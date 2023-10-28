<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_activity_bit".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_date
 * @property int $duration
 * @property int $activity
 * @property resource $works
 * @property resource $actvs
 */
class UserActivityBit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_activity_bit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'utc_date', 'duration', 'activity', 'works', 'actvs'], 'required'],
            [['user_id', 'duration', 'activity'], 'integer'],
            [['utc_date'], 'safe'],
            [['works', 'actvs'], 'string'],
            [['user_id', 'utc_date'], 'unique', 'targetAttribute' => ['user_id', 'utc_date']],
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
            'utc_date' => 'Utc Date',
            'duration' => 'Duration',
            'activity' => 'Activity',
            'works' => 'Works',
            'actvs' => 'Actvs',
        ];
    }
}
