<?php

/**
 * @author Andrey A. Kirichenko <andrey.kirichenko@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\components\time\Time;
use Exception;
use yii\db\Expression;

class UserAppCache extends \common\models\generated\UserAppCache
{

	public $date;
	public $time;
	public $name;
	public $url;
	
	public static function aggregate($apps, $return = FALSE, $isArray = FALSE)
	{
		$result = [];

		foreach($apps as $app) {
			if(!$isArray) {
				$app = $app->toArray();
			}

			$appName = UserApp15mN::getAppName($app['app']);
			
			$userTime = strtotime($app['utc_time_15m']) + ($app['tz_offset'] * 900);

			$result['user'][] = [
				'user_id' => $app['user_id'],
				'user_date' => date(Time::FORMAT_DATE, $userTime),
				'duration' => $app['duration'],
				'app' => $app['app'],
				'app_name' => $appName
			];

			$userGoups = UserGroup::findAll(['user_id' => $app['user_id']]);
			foreach($userGoups as $group) {
				$userTimeGroup = strtotime($app['utc_time_15m']) + ($app['tz_offset'] * 900);

				$result['group'][] = [
					'group_id' => $group['group_id'],
					'user_date' => date(Time::FORMAT_DATE, $userTimeGroup),
					'duration' => $app['duration'],
					'app' => $app['app'],
					'app_name' => $appName
				];
			}

		}

		if($return) return $result;

		if(!empty($result['user'])) {
			if(!self::batchInsertApp($result['user'])) return false;
		}

		if(!empty($result['group'])) {
			if(!UserAppGroupCache::batchInsertApp($result['group'])) return false;
		}

		return true;
	}

	public static function batchInsertApp($data) {
        if (count($data) == 0) {
            return true;
        }

		foreach(array_chunk($data, 1000) as $chunkedData) {
            $command = \Yii::$app->getDb()->createCommand()->batchInsert(
                'user_app_cache',
                ['user_id', 'user_date', 'duration', 'app', 'app_name'],
                $chunkedData,
            );
    
            $command->sql .= 
            ' ON DUPLICATE KEY UPDATE
                `duration`=duration + VALUES(duration)
            ';
    
            try {
				return $command->execute();
                die();
			} catch(Exception $E) {
				return false;
			}
        }
	}

    public function queryTopApps($userId, $from, $to) {
        $query = (new \yii\db\Query())
            ->select([
                'user_id',
                'SUM(duration) AS d',
                'app_name as `name`',
            ])
            ->from('user_app_cache')
            ->where(['user_id' => $userId])
            ->andWhere(['BETWEEN', 'user_date', $from, $to])
            ->groupBy(['user_id', 'app_name'])
            ->orderBy(['user_id' => SORT_ASC, 'SUM(duration)' => SORT_DESC]);

        $topApps = (new \yii\db\Query())
            ->select(new Expression('`user_id`,`d`,`name`,@rank:=IF(@c=`user_id`,@rank+1,1) as `rank`,@c:=`user_id`'))
            ->from(['ranked' => $query])
            ->having(['<', 'rank', 2]);
        \Yii::$app->db->createCommand('SET @rank:=NULL;SET @c:=NULL;')->execute();

        return $topApps;
    }
}
