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

class SiteName extends \common\models\generated\SiteName 
{
    /**
     * Сохранение сайтов в таблицу sitename
     * @param AppSite[] $models 
     * @return array [name => id] всех сохранённых\обновленных sitename
     * @throws ServerErrorHttpException
     */
    public static function saveAllSites($models): array
    {
        $allSites = [];
        $allSitesBlocks = ArrayHelper::getColumn($models, 'sites_block');
        foreach ($allSitesBlocks as $siteBlock) {
            array_push($allSites, ...array_column($siteBlock, 'url'));
        }

        try {
            $batchData = array_map(function($item) {
                return ['url' => $item];
            }, $allSites);

            foreach (array_chunk($batchData, 1000) as $chunkedData) {
                $command = Yii::$app->getDb()->createCommand()->batchInsert(
                    SiteName::tableName(),
                    ['url'],
                    $chunkedData 
                );
                $command->sql .= ' ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP ';
                $command->execute();    
            }

            return SiteName::find()->select('id')->where(['url' => $allSites])->indexBy('url')->column();
        } catch (\Throwable $th) {
            throw new ServerErrorHttpException('Failed save sites urls.');
        }
    }
}