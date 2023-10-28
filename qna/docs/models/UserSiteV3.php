<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Yii;
use yii\web\ServerErrorHttpException;
use common\components\time\Time;
use api3\models\AppSite;

class UserSiteV3 extends \common\models\generated\UserSiteV3 
{
    /**
     * Максимальное количество сайтов которое мы сохраняем в одной строке
     * @var integer 
     */
    const MAX_SITES_COUNT = 3;

    /**
     * Сохранение сайтов в таблицу user_site_v3
     * @param  AppSite[] $models
     * @param  array $siteIdMap
     * @return void
     * @throws ServerErrorHttpException
     */
    public static function saveAllUserSites($models, $siteIdMap): void
    {
        $userId = Yii::$app->user->id;

        $batchData = [];
        foreach ($models as $model) {
            $data = [
                'user_id' => $userId,
                'utc_datetime_15m' => date(Time::FORMAT_MYSQL, strtotime($model->datetime)),
                'updated_at' => intval($model->updated_at),
            ];

            // формируем ячейки site1, duration1, site2, duration2 и т.д.
            // если данных по ячейке нет, то подставляем null
            for ($i=1; $i <= self::MAX_SITES_COUNT; $i++) { 
                $currentSite = $model->sites_block[$i-1] ?? [];
                $data["site{$i}"] = isset($currentSite['url']) ? $siteIdMap[$currentSite['url']] : null;
                $data["duration{$i}"] = $currentSite['duration'] ?? null;
            }

            $batchData[] = $data;
        }

        // создаём список полей для insert и строки для ON DUPLICATE KEY UPDATE согласно количеству MAX_SITES_COUNT
        $columnList = ['user_id', 'utc_datetime_15m', 'updated_at'];
        $onDuplicateColumns = '';
        for ($i=1; $i <= self::MAX_SITES_COUNT; $i++) { 
            array_push($columnList, "site{$i}", "duration{$i}");
            $onDuplicateColumns .= "
                site{$i}     = IF (updated_at < VALUES(updated_at), VALUES(site{$i}), site{$i}),
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
                    UserSiteV3::tableName(),
                    $columnList,
                    $chunkedData,
                );
                
                $command->sql .= $onDuplicate;
                $command->execute();
            }
            return;
        } catch (\Throwable $th) {
            throw new ServerErrorHttpException('Failed save user sites.');
        }
    }
}