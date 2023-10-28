<?php
/**
 * @author Aleksey I. Chelnokov <aleksey.chelnokov@auslogics.com>
 * @copyright Copyright (c) 2022 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use api2\models\ActivityQuery;
use common\components\time\Time;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class ActivitySummary
 * @package common\models
 */
class ActivitySummary extends Model {

	public $date;
	public $from;
	public $to;
	public $userIds = [];
	public $timeStart = NULL;
	public $timeFinish = NULL;
	public $timezone = NULL;

	public function rules() {
		return [
			['date', 'default', 'value' => date('Y-m-d')],
			['date', 'date', 'format' => 'php:Y-m-d'],
			['from', 'default', 'value' => NULL],
			['from', 'date', 'format' => 'php:Y-m-d'],
			['to', 'default', 'value' => NULL],
			['to', 'date', 'format' => 'php:Y-m-d'],
			['userIds', 'each', 'rule' => ['exist', 'targetClass' => User::class, 'targetAttribute' => 'id'], 'message' => 'Users list is not correct.'],
			['timeStart', 'default', 'value' => function () {
				if ($this->from === NULL) {
					return $this->date;
				} else {
					return $this->from;
				}
			}],
			['timeFinish', 'default', 'value' => function () {
				if ($this->to === NULL) {
					return $this->date . ' 23:59:59';
				} else {
					return $this->to . ' 23:59:59';
				}
			}],
			['timezone', 'default', 'value' => function () {return $this->timezone;}],
		];
	}

	/**
	 * @return ActivityQuery
	 */
	public function getAvgSummary() {
		$activity = UserActivityNew::find(); //return $activity;

		if ($this->timezone == NULL) {
			$utcTimeConvert = new Expression(
				"convert_tz(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"),"
				. " TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i')))"
			);
		} else {
			$utcTimeConvert = new Expression(
				"convert_tz(`utc_time_15m`, '+00:00', '"
				. Time::getTimeZoneOffset($this->timezone) . "')"
			);
		}

		$activity->select([
            'user_id',
			'SUM(duration) as `duration`',
			'ROUND(SUM(IF(activity IS NULL,0,duration)*activity)/SUM(IF(activity IS NULL,0,duration))) as `activity`',
			"DATE_FORMAT($utcTimeConvert,'%Y-%m-%d') as `date`",
		]);

		$activity->where(['between', $utcTimeConvert, $this->timeStart, $this->timeFinish]);
		$activity->andWhere(['IN', 'user_id', $this->userIds]);
		$activity->groupBy(['date', 'user_id']);

		$manualActivity = UserManualActivityNew::find();

		if ($this->timezone == NULL) {
			$utcTimeConvert = new Expression(
				"convert_tz(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"),"
				. " TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i')))"
			);
		} else {
			$utcTimeConvert = new Expression(
				"convert_tz(`utc_time_15m`, '+00:00', '"
				. Time::getTimeZoneOffset($this->timezone) . "')"
			);
		}

		$manualActivity->select([
            'user_id',
			'SUM(duration) as `duration`',
			'NULL as `activity`',
			"DATE_FORMAT($utcTimeConvert,'%Y-%m-%d') as `date`",
		]);

		$manualActivity->where(['between', $utcTimeConvert, $this->timeStart, $this->timeFinish]);
		$manualActivity->andWhere(['IN', 'user_id', $this->userIds]);
		$manualActivity->groupBy(['date', 'user_id']);

		$activity->union($manualActivity);

		$result = new ActivityQuery();
        $result->select([
            'ROUND(SUM(IF(activity IS NULL,0,duration)*activity)/SUM(IF(activity IS NULL,0,duration))) as `activity`',
            'SUM(duration) as `duration`',
            'COUNT(DISTINCT user_id) as `usersWorkedCount`',
            'COUNT(DISTINCT date) as `userActivityDaysCount`'
        ]);
        $result->from($activity);
        $result->groupBy('');

		return $result;
	}

}
