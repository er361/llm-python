<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Yii;
use yii\web\ServerErrorHttpException;
use common\components\time\Time;
use api3\models\Apps;

class UserAppV3 extends \common\models\generated\UserAppV3 
{
    /**
     * Максимальное количество приложений которое мы сохраняем в одной строке
     * @var integer 
     */
    const MAX_APPS_COUNT = 3;

    /**
     * Сохранение приложений в таблицу user_app_v3
     * @param  Apps[] $models
     * @param  array $appNameIdMap
     * @return void
     * @throws ServerErrorHttpException
     */
    public static function saveAllUserApps($models, $appNameIdMap): void
    {
        $userId = Yii::$app->user->id;

        $batchData = [];
        foreach ($models as $model) {
            $data = [
                'user_id' => $userId,
                'utc_datetime_15m' => date(Time::FORMAT_MYSQL, strtotime($model->datetime)),
                'updated_at' => intval($model->updated_at),
            ];

            // формируем ячейки app1, duration1, app2, duration2 и т.д.
            // если данных по ячейке нет, то подставляем null
            for ($i=1; $i <= self::MAX_APPS_COUNT; $i++) { 
                $currentApp = $model->apps_block[$i-1] ?? [];
                $data["app{$i}"] = isset($currentApp['name']) ? $appNameIdMap[$currentApp['name']] : null;
                $data["duration{$i}"] = $currentApp['duration'] ?? null;
            }

            $batchData[] = $data;
        }

        // создаём список полей для insert и строки для ON DUPLICATE KEY UPDATE согласно количеству MAX_APPS_COUNT
        $columnList = ['user_id', 'utc_datetime_15m', 'updated_at'];
        $onDuplicateColumns = '';
        for ($i=1; $i <= self::MAX_APPS_COUNT; $i++) { 
            array_push($columnList, "app{$i}", "duration{$i}");
            $onDuplicateColumns .= "
                app{$i}      = IF (updated_at < VALUES(updated_at), VALUES(app{$i}), app{$i}),
                duration{$i} = IF (updated_at < VALUES(updated_at), VALUES(duration{$i}), duration{$i}),
            ";
        }

        // строка updated_at должна быть в конце, или поля после неё не обновятся
        $onDuplicate = 
            ' ON DUPLICATE KEY UPDATE ' . 
            $onDuplicateColumns . 
            'updated_at = IF (updated_at < VALUES(updated_at), VALUES(updated_at), updated_at)';

        try {
            foreach (array_chunk($batchData, 1000) as $chunkedData) {
                $command = Yii::$app->getDb()->createCommand()->batchInsert(
                    UserAppV3::tableName(),
                    $columnList,
                    $chunkedData,
                );
                
                $command->sql .= $onDuplicate;
                $command->execute();
            }
            return;
        } catch (\Throwable $th) {
            throw new ServerErrorHttpException('Failed save user apps.');
        }
    }
}