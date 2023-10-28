<?php

namespace common\models\generated;

use common\components\time\Time;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_api_key".
 *
 * @property int $user_id
 * @property string $api_key
 * @property string $valid_till
 *
 * @property-read User $user
 */
class UserApiKey extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_api_key';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id'], 'integer'],
            [['user_id', 'api_key', 'valid_till'], 'required'],
            [
                ['user_id'],
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'id',
            ],

            [['api_key'], 'string', 'max' => 64],
            [['api_key'], 'unique'],

            ['valid_till', 'date', 'format' => 'php:' . Time::FORMAT_MYSQL]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => 'User ID',
            'api_key' => 'API key',
            'valid_till' => 'Valid until date',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
