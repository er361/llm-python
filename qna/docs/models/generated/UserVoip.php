<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_voip".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $user_time
 * @property string $utc_time
 * @property int $duration
 *
 * @property User $user
 */
class UserVoip extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_voip';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'user_time', 'utc_time', 'duration'], 'required'],
            [['user_id', 'duration'], 'integer'],

            [['user_time', 'utc_time'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'user_time' => 'User Time',
            'utc_time' => 'Utc Time',
            'duration' => 'Duration',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
