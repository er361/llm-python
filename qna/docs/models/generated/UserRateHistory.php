<?php

namespace common\models\generated;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_rate_history".
 *
 * @property int $user_id
 * @property string $rate
 * @property int|null $rate_interval
 * @property string $rate_start_datetime
 * @property string $utc_datetime
 */
class UserRateHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_rate_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'rate_start_datetime', 'utc_datetime'], 'required'],
            [['user_id'], 'integer'],
            [
                ['user_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'id',
            ],
            [['rate'], 'number'],
            [['rate_interval'], 'integer', 'max' => 9],
            [
                ['rate_start_datetime', 'utc_datetime'],
                'datetime',
                'format' => 'php:Y-m-d H:i:s'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => 'User ID',
            'rate' => 'User\'s rate',
            'rate_interval' => 'Rate interval',
            'rate_start_datetime' => 'Rate start datetime',
            'utc_datetime' => 'UTC datetime',
        ];
    }
}