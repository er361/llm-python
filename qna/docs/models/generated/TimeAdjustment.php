<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "time_adjustment".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $status
 * @property string|null $reason
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $processed_user_id
 * @property string|null $processed_reason
 * @property int|null $duration
 * @property string $user_time
 * @property string|null $utc_time
 *
 * @property User $user
 * @property UserManualActivityNew[] $userManualActivityNews
 */
class TimeAdjustment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'time_adjustment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'company_id', 'status', 'user_time'], 'required'],
            [['user_id', 'company_id', 'status', 'processed_user_id', 'duration'], 'integer'],
            [['created_at', 'updated_at', 'user_time', 'utc_time'], 'safe'],
            [['reason', 'processed_reason'], 'string', 'max' => 255],
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
            'company_id' => 'Company ID',
            'status' => 'Status',
            'reason' => 'Reason',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'processed_user_id' => 'Processed User ID',
            'processed_reason' => 'Processed Reason',
            'duration' => 'Duration',
            'user_time' => 'User Time',
            'utc_time' => 'Utc Time',
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

    /**
     * Gets query for [[UserManualActivityNews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserManualActivityNews()
    {
        return $this->hasMany(UserManualActivityNew::className(), ['time_adjustment_id' => 'id']);
    }
}
