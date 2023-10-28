<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_activity_overlimit".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_time_15m
 * @property int $tz_offset
 * @property int $start_offset
 * @property int $duration
 * @property int $activity
 * @property int $block_id
 * @property int $status
 *
 * @property User $user
 */
class UserActivityOverlimit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_activity_overlimit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'utc_time_15m'], 'required'],
            [['user_id', 'tz_offset', 'start_offset', 'duration', 'activity', 'block_id', 'status'], 'integer'],
            [['utc_time_15m'], 'safe'],
            [['user_id', 'utc_time_15m', 'tz_offset', 'block_id'], 'unique', 'targetAttribute' => ['user_id', 'utc_time_15m', 'tz_offset', 'block_id']],
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
            'utc_time_15m' => 'Utc Time 15m',
            'tz_offset' => 'Tz Offset',
            'start_offset' => 'Start Offset',
            'duration' => 'Duration',
            'activity' => 'Activity',
            'block_id' => 'Block ID',
            'status' => 'Status',
        ];
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
