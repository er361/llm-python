<?php
/**
 * @author Aleksey I. Chelnokov <aleksey.chelnokov@auslogics.com>
 * @copyright Copyright (c) 2021 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

class UserAppToken extends \common\models\generated\UserAppToken {

	/**
	 * Проверяет нужно ли обновить токен
	 *
	 * @return bool
	 */
	public function toRenewToken() {
		$datetime = new \DateTime($this->expires_in);
		$now = new \DateTime();
		return ($now->diff($datetime)->format('%a') <= 6) ? true : false;
	}

	/**
	 * Возвращает форматированную дату в формате Traqq ISO 8601 (YYYY-MM-DDThh:mm:ss±hh:mm)
	 *
	 * @return string
	 */
	public function getFormattedDate() {
		$datetime = new \DateTime($this->expires_in);
		return $datetime->format('Y-m-dTH:i:sP');
	}

	/**
	 * @return bool
	 */
	public function isExpired() {
		return $this->expires_in < date('Y-m-d H:i:s');
	}

	/**
	 * Оставшееся время жизни в секундах
	 * @return int
	 */
	public function timeToLive() {
		$expiryDateTs = (new \DateTime($this->expires_in))->getTimestamp();
		$nowTs = (new \DateTime())->getTimestamp();

		return $expiryDateTs - $nowTs;
	}

	public function getUserAgentReadable() {
		return (is_null($this->user_agent)) ? 'Unknown' : $this->user_agent;
	}

	/**
	 * Форматирует дату в формате datetime (YYYY-MM-DD hh:mm:ss)
	 *
	 * @return bool
	 */

	public function beforeSave($insert) {
		if (parent::beforeSave($insert)) {
			$this->expires_in = date("Y-m-d H:i:s", strtotime($this->expires_in));
			return true;
		}
		return false;
	}

}
