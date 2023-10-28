<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_manual_activity_new".
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
class UserManualActivityNew extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_manual_activity_new';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'time_adjustment_id', 'utc_time_15m', 'duration'], 'required'],
            [['user_id', 'time_adjustment_id', 'tz_offset', 'start_offset', 'duration', 'block_id'], 'integer'],
            [['utc_time_15m'], 'safe'],
            [['time_adjustment_id'], 'exist', 'skipOnError' => true, 'targetClass' => TimeAdjustment::className(), 'targetAttribute' => ['time_adjustment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
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
     * @return \yii\db\ActiveQuery
     */
    public function getTimeAdjustment()
    {
        return $this->hasOne(TimeAdjustment::className(), ['id' => 'time_adjustment_id']);
    }
}
