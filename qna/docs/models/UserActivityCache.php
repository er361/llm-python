<?php
/**
 * @author Andrey N. Loshkarev <andrey.loshkarev@auslogics.com>
 * @copyright Copyright (c) 2022 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\components\time\Time;
use common\models\generated\UserActivityCache as GeneratedUserActivityCache;
use Yii;
use yii\db\Expression;

/**
 * Class UserActivityCache
 * @package common\models
 */
class UserActivityCache extends GeneratedUserActivityCache {
	/**
	 * @param array $activity
	 *
	 * @return bool
	 */
	public static function aggregate($params): bool{
		$userDate = "CONVERT_TZ(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"), TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i')))";
		$processedParams = self::checkForNull($params, $userDate);
		$activities = self::findUserActivities($processedParams, $userDate);
		if (is_array($activities) && $activities && $activities[0]) {
			return self::batchInsert($activities);
		}
		return false;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public static function checkForNull($params, $userDate) {
		// default value
		$users = 'WHERE';
		$defaultDate = (new \DateTime('now'))->format(Time::FORMAT_DATE);
		$start = "(({$userDate}) BETWEEN '{$defaultDate} 00:00:00'";
		$finish = "AND '{$defaultDate} 23:59:59')";

		foreach($params as $param => $value) {
			if ($param === 'userIds' && $value) {
				$ids = implode(',', $value);
				$users = "WHERE `user_id` IN ({$ids}) AND";
			}
			if ($param === 'start' && $value) {
				$date = (new \DateTime($value))->format(Time::FORMAT_DATE);
				$start = "(({$userDate}) BETWEEN  '{$date} 00:00:00'";
			}
			if ($param === 'finish' && $value) {
				$date = (new \DateTime($value))->format(Time::FORMAT_DATE);
				$finish = "AND '{$date} 23:59:59')";
			}
		}
		return ['userIds' => $users, 'start' => $start, 'finish' => $finish];
	}

	/**
	 * @param array $activity
	 *
	 * @return array
	 */
	public static function findUserActivities(array $params, $userDate) {
		$formatedUserDate = "(DATE_FORMAT({$userDate}, '%Y-%m-%d'))";
		$activityCalc = "ROUND(SUM(IF(activity IS NULL,0,duration)*activity)/SUM(IF(activity IS NULL,0,duration)))";
		$approved = UserActivityOverlimit::STATUS_APPROVED;
		$overlimitsApproved = "`status` = {$approved}";
		$userActivities = Yii::$app
			->getDb()
			->createCommand(
				"SELECT `user_id`, `user_date`, SUM(`duration`) as duration, "
				. "{$activityCalc} as `activity` "
				. "FROM ("
					. "SELECT `user_id`, {$formatedUserDate} as user_date, sum(`duration`) as duration, "
					. "{$activityCalc} as `activity` "
					. "FROM `user_activity_new` as uan "
					. "{$params['userIds']} "
					. "{$params['start']} "
					. "{$params['finish']} "
					. "GROUP BY `user_id`, `user_date` "

					. "UNION SELECT `user_id`, {$formatedUserDate} as user_date, sum(`duration`) as duration, 50 as `activity` "
					. "FROM `user_manual_activity_new` as uman "
					. "{$params['userIds']} "
					. "{$params['start']} "
					. "{$params['finish']} "
					. "GROUP BY `user_id`, `user_date` "

					. "UNION SELECT `user_id`, {$formatedUserDate} as user_date, sum(`duration`) as duration, "
					. "{$activityCalc} as `activity` "
					. "FROM `user_activity_overlimit` as uao "
					. "{$params['userIds']} "
					. "{$overlimitsApproved} "
					. "AND "
					. "{$params['start']} "
					. "{$params['finish']} "
					. "GROUP BY `user_id`, `user_date`"
				.") as act "
				. "GROUP BY `user_id`, `user_date`"
			)
			->queryAll();
		return $userActivities;
	}

	/**
	 * @param array $activity
	 *
	 * @return bool
	 */
	public static function batchInsert(array $activities): bool{
		foreach(array_chunk($activities, 1000) as $chunkedActivity) {
			$command = Yii::$app->getDb()->createCommand()->batchInsert(
				'user_activity_cache',
				['user_id', 'user_date', 'duration', 'activity'],
				$chunkedActivity,
			);
			$command->sql .= 
			' ON DUPLICATE KEY UPDATE
				`duration`= VALUES(duration),
				`activity`= VALUES(activity)
			';
			$command->execute();
		}
		return true;
	}

	public static function getDayActivityDuration($userId, $date)
    {
        $allDayActivities = self::find()
                ->select(['total' => new Expression("SUM(`duration`)")])
                ->where(['user_id' => $userId])
                ->andWhere(['user_date' => $date])
                ->asArray()->one();
        return $allDayActivities;
    }

	/**
	 * @param array $activity
	 *
	 * @return bool
	 */
	public static function realAggregate(array $activities): bool{
		foreach(array_chunk($activities, 1000) as $chunkedActivity) {
			$command = Yii::$app->getDb()->createCommand()->batchInsert(
				'user_activity_cache',
				['user_id', 'user_date', 'duration', 'activity'],
				$chunkedActivity,
			);
			$command->sql .= 
			' ON DUPLICATE KEY UPDATE
				`activity` =
					IF (
						VALUES(activity) IS NULL
						,
						activity
						,
						IF (
							(
								(duration + VALUES(duration)) > 0
							)
							,
							(
								(
									(duration * activity) +
									(VALUES(duration) * VALUES(activity))
								) / (duration + VALUES(duration))
							)
							,
							activity
						)
					),
				`duration` = duration + VALUES(duration)
			';
			$command->execute();
		}
		return true;
	}

}
