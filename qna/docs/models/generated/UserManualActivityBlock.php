<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_manual_activity_block".
 *
 * @property int $id
 * @property int $user_id
 * @property int $time_adjustment_id
 * @property int $tz_offset
 * @property int $duration
 * @property int $block_timestamp
 *
 * @property TimeAdjustment $timeAdjustment
 * @property User $user
 */
class UserManualActivityBlock extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_manual_activity_block';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'time_adjustment_id', 'duration', 'block_timestamp'], 'required'],
            [['user_id', 'time_adjustment_id', 'tz_offset', 'duration', 'block_timestamp'], 'integer'],
            [['user_id', 'time_adjustment_id', 'tz_offset', 'block_timestamp'], 'unique', 'targetAttribute' => ['user_id', 'time_adjustment_id', 'tz_offset', 'block_timestamp']],
            [['time_adjustment_id'], 'exist', 'skipOnError' => true, 'targetClass' => TimeAdjustment::className(), 'targetAttribute' => ['time_adjustment_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'tz_offset' => 'Tz Offset',
            'duration' => 'Duration',
            'block_timestamp' => 'Block Timestamp',
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

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
