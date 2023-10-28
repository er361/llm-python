<?php

namespace common\models;

use common\components\time\Time;
use common\exceptions\ValidateException;
use common\models\generated\UserLimits as GeneratedUserLimits;
use common\models\User;
use DateTime;

/**
 * This is the model class for table "user_limits".
 *
 * @property int $id
 * @property int $user_id
 * @property string $from
 * @property string $to
 * @property string $limit
 * @property string $timezone
 * @property string $timezone_value
 * @property string $days
 * @property string $created_at
 * @property string $start_date
 */
class UserLimits extends GeneratedUserLimits {
	const DAYS = [
		'Sun',
		'Mon',
		'Tue',
		'Wed',
		'Thu',
		'Fri',
		'Sat'
	];

	const TIMEZONE_USER = 'user';
	const TIMEZONE_COMPANY = 'company';

	const TIMEZONE = [
		self::TIMEZONE_USER,
		self::TIMEZONE_COMPANY
	];

	const DEFAULT_VALUES = [
		'from' => '',
		'to' => '',
		'limit' => 'PT8H',
		'timezone' => 'user',
		'days' => 'Mon,Tue,Wed,Thu,Fri'
	];

	const DEFAULT_DAY_LIMIT = 86400; //секунд в сутках

	public function rules() {
		return array_merge(
			parent::rules(),
			[['created_at', 'default', 'value' => date('Y-m-d H:i:s')]]
		);
	}

	public static function softDelete($userId) {
		return (bool)self::updateAll(
			[
				'from' => '',
				'to' => '',
				'limit' => null
			],
			[
				'AND',
				['user_id' => $userId],
				['IS NOT', 'limit', null]
			]
		);
	}

	/**
	 * @return string
	 */
	public function getLimit() {
		return $this->limit;
	}

	public static function getActualLimits($userId, $startDate) {
        $limits = self::find()
					->where(['user_id' => $userId])
					->andWhere(['<=', 'start_date', $startDate])
					->orderBy('created_at DESC')
					->one();
		if(isset($limits) && $limits->limit != NULL) {
			return $limits;
		}
        return null;
    }

	public static function applyNextDayLimits($userId, $date) { 
		$todayLimits = self::findOne(['user_id' => $userId, 'start_date' => $date]);

		$nextDateLimitsQuery = self::find()
						->where(['user_id' => $userId])
						->andWhere(['>', 'start_date', $date])
						->orderBy('created_at DESC');

		if(isset($nextDateLimitsQuery) && $nextDateLimitsQuery->count() > 0) {
			if(isset($todayLimits)) {
				$todayLimits->delete();
			}

			foreach($nextDateLimitsQuery->all() as $nextDateLimits) {
				$newStartDate = new DateTime($nextDateLimits->start_date);
				$nextDateLimits->start_date = $newStartDate->modify("-1 day")->format(Time::FORMAT_DATE);
				if(!$nextDateLimits->save()) {
					throw new ValidateException($nextDateLimits->getErrors());
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * В параметре timezoneSource передаётся 'user' или 'company'
	 *
	 * @param string $timezone
	 * @param array $userIds
	 * @param string $timezoneSource
	 *
	 * @return void
	 */
	public static function updateTimezone($timezone, $userIds, $timezoneSource) {
		return (bool)UserLimits::updateAll(
			['timezone_value' => $timezone],
			[
				'AND',
				['user_id' => $userIds],
				['timezone' => $timezoneSource],
			]
		);
	}
	

	/**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
	}
}
