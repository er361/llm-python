<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_cache".
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_date
 * @property int $duration
 * @property string $app
 * @property string $app_name
 */
class UserAppCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_app_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_date', 'duration', 'app', 'app_name'], 'required'],
            [['user_id', 'duration'], 'integer'],
            [['user_date'], 'safe'],
            [['app'], 'string', 'max' => 32],
            [['app_name'], 'string', 'max' => 255],
            [['user_id', 'user_date', 'app'], 'unique', 'targetAttribute' => ['user_id', 'user_date', 'app']],
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
            'app' => 'App',
            'app_name' => 'App Name',
        ];
    }
}
