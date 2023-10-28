<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_api_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $auth_token
 * @property resource|null $token_ip
 * @property resource|null $last_login_ip
 * @property string|null $last_logged_in_at
 * @property string|null $created_at
 * @property string|null $valid_before
 * @property string|null $device_data
 * @property string|null $device_name
 * @property string $device_id
 * @property string $refresh_token
 *
 * @property User $user
 */
class UserApiToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_api_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'auth_token', 'refresh_token'], 'required'],
            [['user_id'], 'integer'],
            [['last_logged_in_at', 'created_at', 'valid_before'], 'safe'],
            [['device_data'], 'string'],
            [['auth_token', 'refresh_token'], 'string', 'max' => 32],
            [['token_ip', 'last_login_ip'], 'string', 'max' => 16],
            [['device_name'], 'string', 'max' => 128],
            [['device_id'], 'string', 'max' => 64],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['auth_token'], 'unique'],
            [['refresh_token'], 'unique'],
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
            'auth_token' => 'Auth Token',
            'token_ip' => 'Token Ip',
            'last_login_ip' => 'Last Login Ip',
            'last_logged_in_at' => 'Last Logged In At',
            'created_at' => 'Created At',
            'valid_before' => 'Valid Before',
            'device_data' => 'Device Data',
            'device_name' => 'Device Name',
            'device_id' => 'Device ID',
            'refresh_token' => 'Refresh Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return UserApiTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserApiTokenQuery(get_called_class());
    }
}
