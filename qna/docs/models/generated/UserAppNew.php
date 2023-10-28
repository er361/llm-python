<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_new".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_time_15m
 * @property int $tz_offset
 * @property int $duration
 * @property string|null $app
 * @property int $block_id
 */
class UserAppNew extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_app_new';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'utc_time_15m'], 'required'],
            [['user_id', 'tz_offset', 'duration', 'block_id'], 'integer'],
            [['utc_time_15m'], 'safe'],
            [['app'], 'string', 'max' => 32],
            [['user_id', 'utc_time_15m', 'app'], 'unique', 'targetAttribute' => ['user_id', 'utc_time_15m', 'app']],
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
            'duration' => 'Duration',
            'app' => 'App',
            'block_id' => 'Block ID',
        ];
    }
}
