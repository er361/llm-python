<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_video".
 *
 * @property int $id
 * @property int $user_id
 * @property string $src
 * @property string $driver
 * @property string $user_time
 * @property string $utc_time
 * @property int $duration
 * @property int $block_id
 */
class UserVideo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_video';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'src', 'driver', 'duration'], 'required'],
            [['user_id', 'duration', 'block_id'], 'integer'],
            [['user_time', 'utc_time'], 'safe'],
            [['src'], 'string', 'max' => 255],
            [['driver'], 'string', 'max' => 32],
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
            'src' => 'Src',
            'driver' => 'Driver',
            'user_time' => 'User Time',
            'utc_time' => 'Utc Time',
            'duration' => 'Duration',
            'block_id' => 'Block ID',
        ];
    }
}
