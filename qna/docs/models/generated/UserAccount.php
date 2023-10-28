<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_account".
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $client_id
 * @property string $data
 * @property string $code
 * @property string $created_at
 * @property string $email
 * @property string $username
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['provider', 'client_id'], 'required'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['provider', 'client_id', 'email', 'username'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
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
            'provider' => 'Provider',
            'client_id' => 'Client ID',
            'data' => 'Data',
            'code' => 'Code',
            'created_at' => 'Created At',
            'email' => 'Email',
            'username' => 'Username',
        ];
    }
}
