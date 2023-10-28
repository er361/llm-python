<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $expires_in
 * @property string $device
 * @property string $user_agent
 * @property string $access_token
 * @property string $refresh_token
 *
 * @property User $user
 */
class UserAppToken extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'user_app_token';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['user_id', 'access_token', 'refresh_token'], 'required'],
			[['user_id'], 'integer'],
			[['expires_in'], 'safe'],
			[['device'], 'string', 'max' => 64],
			[['user_agent'], 'string', 'max' => 255],
			[['access_token', 'refresh_token'], 'string', 'max' => 65],
			[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'expires_in' => 'Expires In',
			'device' => 'Device',
			'user_agent' => 'User Agent',
			'access_token' => 'Access Token',
			'refresh_token' => 'Refresh Token',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

}
