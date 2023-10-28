<?php

namespace common\models\generated;

use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_manual_activity_delete".
 *
 * @property int $id
 * @property int $user_id
 * @property int $time_adjustment_id
 * @property string $utc_time_15m
 * @property int $tz_offset
 * @property int $start_offset
 * @property int $duration
 * @property int $block_id
 *
 * @property TimeAdjustment $timeAdjustment
 */
class UserManualActivityDelete extends ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'user_manual_activity_delete';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array {
		return [
			[
				['user_id', 'time_adjustment_id', 'utc_time_15m', 'duration'],
				'required'
			],
			[
				[
					'user_id',
					'time_adjustment_id',
					'tz_offset',
					'start_offset',
					'duration',
					'block_id'
				],
				'integer'
			],
			[['utc_time_15m'], 'safe'],
			[
				['time_adjustment_id'],
				'exist',
				'skipOnError' => true,
				'targetClass' => TimeAdjustment::class,
				'targetAttribute' => ['time_adjustment_id' => 'id']
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array {
		return [
			'id' => 'ID',
			'user_id' => 'User ID',
			'time_adjustment_id' => 'Time Adjustment ID',
			'utc_time_15m' => 'Utc Time 15m',
			'tz_offset' => 'Tz Offset',
			'start_offset' => 'Start Offset',
			'duration' => 'Duration',
			'block_id' => 'Block ID',
		];
	}

	/**
	 * Gets query for [[TimeAdjustment]].
	 *
	 * @return ActiveQueryInterface
	 */
	public function getTimeAdjustment(): ActiveQueryInterface {
		return $this->hasOne(TimeAdjustment::class, ['id' => 'time_adjustment_id']);
	}

}