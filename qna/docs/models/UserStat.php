<?php
/**
 * @author Dmitriy Blagov <dmitriy.blagov@auslogics.com>
 * @copyright Copyright (c) 2022 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\components\time\Time;

/**
 * Class UserStat
 *
 * @package common\models
 */
class UserStat extends \common\models\generated\UserStat {
	const STAT_ATTRIBUTES = ['id', 'company_id', 'name', 'email', 'role', 'status', 'created_at', 'deleted_at', 'timezone', 'last_login_at'];

	public function rules() {
		return array_merge(parent::rules(), [
			['created_at', 'default', 'value' => date('Y-m-d H:i:s')],
		]);
	}

	public static function extractStatData($attr) {
		$data = [];
		foreach (self::STAT_ATTRIBUTES as $attribute) {
			$data[$attribute] = $attr[$attribute];
		}

		return json_encode($data);
	}

	public static function isActiveUsersNew($id) {
		return self::isRegistered($id, \common\models\Stat::EVENT_ACTIVE_USERS_NEW);
	}

	public static function isUserActive($id) {
		return self::isRegistered($id, \common\models\Stat::EVENT_USER_ACTIVE, date(Time::FORMAT_DATE));
	}

	/**
	 * Метод, который определяет, регистрировалось ли событие в указанную дату
	 *
	 * @param \common\models\User $userId
	 * @param string $event
	 * @param mixed $date
	 * @return boolean
	 */
	public static function isRegistered($userId, $event, $date = null) {
		$eventCount = self::find()
			->where([
				'user_id' => $userId,
				'event' => $event,
			])
			->andFilterWhere(['DATE(created_at)' => $date])
			->count();

		if ($eventCount > 0) {
			return true;
		}

		return false;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}
}