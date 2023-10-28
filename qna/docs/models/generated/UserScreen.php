<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_screen".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $image
 * @property string|null $driver
 * @property string $user_time
 * @property string $utc_time
 * @property int $monitor
 * @property int $block_id
 */
class UserScreen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_screen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'monitor', 'block_id'], 'integer'],
            [['image'], 'required'],
            [['user_time', 'utc_time'], 'safe'],
            [['image'], 'string', 'max' => 255],
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
            'image' => 'Image',
            'driver' => 'Driver',
            'user_time' => 'User Time',
            'utc_time' => 'Utc Time',
            'monitor' => 'Monitor',
            'block_id' => 'Block ID',
        ];
    }
}
