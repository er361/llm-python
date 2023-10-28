<?php

/**
 * @author Andrey A. Nechaev <andrey.nechaev@auslogics.com>
 * @copyright Copyright (c) 2019 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Yii;
use yii\db\Expression;
use common\helpers\DateTimeHelper;

/**
 * This is the model class for table "user_token".
 *
 * @property int $user_id
 * @property int $type
 * @property string $code
 * @property string $created_at
 *
 * @property bool $isExpired
 */
class UserToken extends \common\models\generated\UserToken {

	const TYPE_CONFIRMATION = 0;
	const TYPE_RECOVERY = 1;
	const TYPE_EMAIL = 2;
	const TYPE_INVITE = 3;
	const TYPE_LOGIN = 4;
	const TYPE_EXPORT = 5;
	const TYPE_JOIN = 6;

	public $confirmWithin = 86400; // 24 hours
	public $recoverWithin = 21600; // 6 hours

	const DEFAULT_EXPIRATION_TIME = 43200; // в два раза меньше, чем в app, чтобы задержка от софта не мешала логиниться

	/** @inheritdoc */
	public function beforeSave($insert) {
		if ($insert) {
			static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);
			$this->setAttribute('created_at', new Expression('NOW()'));
			if (!$this->code) {
				$this->setAttribute('code', Yii::$app->security->generateRandomString());
                if ($this->type == self::TYPE_LOGIN) {
                    $this->created_at = date('Y-m-d H:i:s', time() + $this->confirmWithin);
                }
			}
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		switch ($this->type) {
			case self::TYPE_CONFIRMATION:
				$route = '/user/registration/confirm';
				break;
			case self::TYPE_RECOVERY:
				$route = '/user/recovery/reset';
				break;
			case self::TYPE_EMAIL:
				$route = '/user/confirm/email';
				break;
			case self::TYPE_INVITE:
				$route = '/user/invite/confirm';
				break;
			default:
				throw new \RuntimeException();
		}
		$webapp = Yii::$app->params['webapp'];
		return "{$webapp}{$route}?id={$this->user_id}&code={$this->code}";
	}

	/**
	 * @return string Expiration time.
	 */
	public function getExpiredIn() {
		switch ($this->type) {
			case self::TYPE_CONFIRMATION:
				$expirationTime = $this->confirmWithin;
				break;
			case self::TYPE_RECOVERY:
				$expirationTime = $this->recoverWithin;
				break;
			case self::TYPE_EMAIL:
				$expirationTime = $this->confirmWithin;
				break;
			default:
				$expirationTime = self::DEFAULT_EXPIRATION_TIME;
		}

		$time = strtotime($this->created_at) + $expirationTime;
		return (new \DateTime())->setTimestamp($time)->format(DateTimeHelper::DATETIME_DB_FORMAT);
	}

	/**
	 * @return bool Whether token has expired.
	 */
	public function getIsExpired() {
		return strtotime($this->getExpiredIn()) < time();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}
}
