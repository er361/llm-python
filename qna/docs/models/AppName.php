<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;
use api3\models\AppSite;

class AppName extends \common\models\generated\AppName 
{
    /**
     * Сохранение приложений в таблицу appname
     * @param AppSite[] $models 
     * @return array [name => id] всех сохранённых\обновленных appname
     * @throws ServerErrorHttpException
     */
    public static function saveAllApps($models): array
    {
        $allAppsNames = [];
        $allAppsBlocks = ArrayHelper::getColumn($models, 'apps_block');
        foreach ($allAppsBlocks as $appBlock) {
            array_push($allAppsNames, ...array_column($appBlock, 'name'));
        }

        try {
            $batchData = array_map(function($item) {
                return ['name' => $item];
            }, $allAppsNames);

            foreach (array_chunk($batchData, 1000) as $chunkedData) {
                $command = Yii::$app->getDb()->createCommand()->batchInsert(
                    AppName::tableName(),
                    ['name'],
                    $chunkedData 
                );
                $command->sql .= ' ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP ';
                $command->execute();    
            }

            return AppName::find()->select('id')->where(['name' => $allAppsNames])->indexBy('name')->column();
        } catch (\Throwable $th) {
            throw new ServerErrorHttpException('Failed save apps names.');
        }
    }
}