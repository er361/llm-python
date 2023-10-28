<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_stat".
 *
 * @property int $id
 * @property int $user_id
 * @property string $created_at
 * @property string $event
 * @property string $data
 *
 * @property User $user
 */
class UserStat extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'user_stat';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['user_id'], 'required'],
			[['user_id'], 'integer'],
			[['created_at'], 'safe'],
			[['data'], 'string'],
			[['event'], 'string', 'max' => 32],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'created_at' => 'Created At',
			'event' => 'Event',
			'data' => 'Data',
		];
	}
}
