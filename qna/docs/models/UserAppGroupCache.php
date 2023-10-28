<?php

/**
 * @author Andrey A. Kirichenko <andrey.kirichenko@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\components\time\Time;
use Exception;

class UserAppGroupCache extends \common\models\generated\UserAppGroupCache
{

	public $date;
	public $time;
	public $name;
	public $url;

	public static function aggregate() {

	}

	public static function batchInsertApp($data) {
        if (count($data) == 0) {
            return true;
        }

		foreach(array_chunk($data, 1000) as $chunkedData) {
            $command = \Yii::$app->getDb()->createCommand()->batchInsert(
                'user_app_group_cache',
                ['group_id', 'user_date', 'duration', 'app', 'app_name'],
                $chunkedData,
            );
    
            $command->sql .= 
            ' ON DUPLICATE KEY UPDATE
                `duration`=duration + VALUES(duration)
            ';
    
            try {
				return $command->execute(); die();
			} catch(Exception $E) {
				return false;
			}
        }
	}
}
