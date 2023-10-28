<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "adm_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $auth_token
 * @property string $token_ip
 * @property string $last_login_ip
 * @property string $last_logged_at
 * @property string $created_at
 * @property string $valid_before
 *
 * @property AdmUser $user
 */
class AdmToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'adm_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'auth_token', 'token_ip', 'last_login_ip', 'last_logged_at', 'created_at', 'valid_before'], 'required'],
            [['user_id'], 'integer'],
            [['last_logged_at', 'created_at', 'valid_before'], 'safe'],
            [['auth_token'], 'string', 'max' => 255],
            [['token_ip', 'last_login_ip'], 'string', 'max' => 32],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdmUser::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'last_logged_at' => 'Last Logged At',
            'created_at' => 'Created At',
            'valid_before' => 'Valid Before',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(AdmUser::className(), ['id' => 'user_id']);
    }
}
