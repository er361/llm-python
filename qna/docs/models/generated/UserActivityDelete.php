<?php

namespace common\models\generated;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_activity_delete".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_time_15m
 * @property int $tz_offset
 * @property int $start_offset
 * @property int $duration
 * @property int $activity
 * @property int $block_id
 */
class UserActivityDelete extends ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string {
		return 'user_activity_delete';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array {
		return [
			[['user_id', 'utc_time_15m', 'duration'], 'required'],
			[
				[
					'user_id',
					'tz_offset',
					'start_offset',
					'duration',
					'activity',
					'block_id',
					'type',
				],
				'integer'
			],
			[['utc_time_15m'], 'safe'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array {
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'utc_time_15m' => 'Utc Time 15m',
			'tz_offset' => 'Tz Offset',
			'start_offset' => 'Start Offset',
			'duration' => 'Duration',
			'activity' => 'Activity',
			'block_id' => 'Block ID',
			'type' => 'Type Activity',
		];
	}

}