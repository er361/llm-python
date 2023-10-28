<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_settings".
 *
 * @property int $user_id
 * @property string $timezone
 * @property string $date_format
 * @property string $time_format
 *
 */
class UserSettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['timezone'], 'string', 'max' => 64],
            [['date_format'], 'string', 'max' => 12],
            [['time_format'], 'string', 'max' => 3],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'timezone' => 'Timezone',
            'date_format' => 'Date Format',
            'time_format' => 'Time Format',
        ];
    }
}
