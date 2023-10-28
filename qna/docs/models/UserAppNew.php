<?php

/**
 * @author Andrey A. Kirichenko <andrey.kirichenko@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;


use api2\components\behaviors\TimeIntervalDelete;
use api2\components\behaviors\TimeIntervalFind;
use api2\components\validators\TimeIntersectValidator;
use common\components\time\Time;
use Exception;

class UserAppNew extends \common\models\generated\UserAppNew
{

	public $date;
	public $time;
	public $name;
	public $url;

	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'deleteInterval' => [
				'class' => TimeIntervalDelete::class,
			],
			'interval' => [
				'class' => TimeIntervalFind::class,
			],
		]);
	}

	public static function aggregate($apps, $return = FALSE)
	{
		$tb = [];
		$tbg = [];
		
		foreach ($apps as $app) {
			$app = $app->toArray();

			if ($app['duration'] < 0) {
				continue;
			}
			
			$app['user_time'] = strtotime($app['user_time']);
			$app['utc_time'] = strtotime($app['utc_time']);

			$tzOffset = round((($app['user_time'] - $app['utc_time']) / 60) / 15);

			$stime = $app['utc_time'];
			$sblock15 = floor($stime / 900) * 900;

			$etime = $stime + $app['duration'];

            //Проверка на интерсект, возможно позже пригодится
            /*$intersected = self::checkIntersect(
                $app['user_id'],
                date(Time::FORMAT_MYSQL, $stime),
                date(Time::FORMAT_MYSQL, $etime)
            );*/

			$eblock15 = floor($etime / 900) * 900;

			$segments15 = (($eblock15 - $sblock15) / 900) + 1;

			for ($j = 1; $j <= $segments15; $j++) {
				if ($j == 1) {
					$utc_time = $app['utc_time'];
					$utc_time_15m = floor($app['utc_time'] / 900) * 900;
					//$startOffset = $utc_time - $utc_time_15m;
				} else {
					$utc_time = floor($app['utc_time'] / 900) * 900 + (900 * ($j - 1));
					$utc_time_15m = floor($app['utc_time'] / 900) * 900 + (900 * ($j - 1));
					//$startOffset = 0;
				}

				$uts_e_time = $j == $segments15 ?
					$app['utc_time'] + $app['duration'] :
					floor($app['utc_time'] / 900) * 900 + (900 * $j);
				$app_s = [
					'user_id' => $app['user_id'],
					'utc_time_15m' => date(Time::FORMAT_MYSQL, $utc_time_15m),
					'tz_offset' => $tzOffset,
					'duration' => $uts_e_time - $utc_time,
					'name' => $app['name'],
					'url' => $app['url'] ?? NULL,
				];
				

				$hash = hash('md5', $app_s['name'] . $app_s['url']);

				$tl = [
					'user_id' => $app_s['user_id'],
					'utc_time_15m' => date(Time::FORMAT_MYSQL, $utc_time_15m),
					'tz_offset' => $tzOffset,
					'duration' => $app_s['duration'],
					'app' => $hash,
					'block_id' => $app['block_id']
				];

				$tlg = [
					'hash' => $hash,
					'name' => $app_s['name'],
					'url' => $app_s['url'],
				];

				$tb[] = $tl;
				$tbg[] = $tlg;

				unset($app_s);
			}
			$app = NULL;
		}

		if($return) return $tb;
		if(UserApp15mN::batchInsertAppN($tbg)) {
			if(self::batchInsertApp($tb)) {
				// print_r($tb); die();
				return UserAppCache::aggregate($tb, false, true);
			}
		}

		return false;
	}

	public static function batchInsertApp($data) {
        if (count($data) == 0) {
            return true;
        }

		foreach(array_chunk($data, 1000) as $chunkedData) {
            $command = \Yii::$app->getDb()->createCommand()->batchInsert(
                'user_app_new',
                ['user_id', 'utc_time_15m', 'tz_offset', 'duration', 'app', 'block_id'],
                $chunkedData,
            );
    
            $command->sql .= 
            ' ON DUPLICATE KEY UPDATE
                `duration`=duration + VALUES(duration)
            ';
    
            try {
				return $command->execute();
			} catch(Exception $E) {
				return false;
			}
        }
	}

	public static function checkIntersect($userId, $timeStart, $timeFinish) {
        $exist = self::find()
		->where(['OR', 
			['AND', 
				['<=', 'utc_time_15m', $timeStart], 
				['>', 'DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE)', $timeStart], 
				['>', 'DATE_ADD(`utc_time_15m`, INTERVAL `duration` SECOND)', $timeStart]
			], 
			['AND', 
				['<', 'utc_time_15m', $timeFinish],
				['<', 'utc_time_15m', $timeFinish],
				['>=', 'DATE_ADD(`utc_time_15m`, INTERVAL `duration` SECOND)', $timeFinish]
			]
		])
		->andWhere(['user_id' => $userId]);

        if($exist->count() > 0) return true;
        
        return false;
    }
}
