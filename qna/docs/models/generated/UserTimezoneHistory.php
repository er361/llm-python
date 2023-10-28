<?php

namespace common\models\generated;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_timezone_history".
 *
 * @property int $user_id
 * @property int $timezone_id
 * @property string $utc_datetime
 */
class UserTimezoneHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_timezone_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'timezone_id'], 'integer'],
            [['user_id', 'timezone_id', 'utc_datetime'], 'required'],
            [['utc_datetime'], 'safe'],
            [
                ['user_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'id',
            ],
            [
                ['timezone_id'],
                'exist',
                'targetClass' => Timezone::class,
                'targetAttribute' => 'id',
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'timezone_id' => 'Timezone ID',
        ];
    }
}