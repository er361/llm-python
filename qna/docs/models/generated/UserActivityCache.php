<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_activity_cache".
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_date
 * @property int $duration
 * @property int $activity
 *
 * @property User $user
 */
class UserActivityCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_activity_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_date', 'duration', 'activity'], 'required'],
            [['user_id', 'duration', 'activity'], 'integer'],
            [['user_date'], 'string', 'max' => 10],
            [['user_id', 'user_date'], 'unique', 'targetAttribute' => ['user_id', 'user_date']],
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
            'user_date' => 'User Date',
            'duration' => 'Duration',
            'activity' => 'Activity',
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
