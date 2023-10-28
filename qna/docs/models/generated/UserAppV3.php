<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_v3".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_datetime_15m
 * @property int $updated_at
 * @property int|null $app1
 * @property int|null $duration1
 * @property int|null $app2
 * @property int|null $duration2
 * @property int|null $app3
 * @property int|null $duration3
 *
 * @property Appname $appName1
 * @property Appname $appName2
 * @property Appname $appName3
 * @property User $user
 */
class UserAppV3 extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_app_v3';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'utc_datetime_15m', 'updated_at'], 'required'],
            [['user_id', 'updated_at', 'app1', 'duration1', 'app2', 'duration2', 'app3', 'duration3'], 'integer'],
            [['utc_datetime_15m'], 'safe'],
            [['user_id', 'utc_datetime_15m'], 'unique', 'targetAttribute' => ['user_id', 'utc_datetime_15m']],
            [['app1'], 'exist', 'skipOnError' => true, 'targetClass' => Appname::class, 'targetAttribute' => ['app1' => 'id']],
            [['app2'], 'exist', 'skipOnError' => true, 'targetClass' => Appname::class, 'targetAttribute' => ['app2' => 'id']],
            [['app3'], 'exist', 'skipOnError' => true, 'targetClass' => Appname::class, 'targetAttribute' => ['app3' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'utc_datetime_15m' => 'Utc Datetime 15m',
            'updated_at' => 'Updated At',
            'app1' => 'App1',
            'duration1' => 'Duration1',
            'app2' => 'App2',
            'duration2' => 'Duration2',
            'app3' => 'App3',
            'duration3' => 'Duration3',
        ];
    }

    /**
     * Gets query for [[AppName1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppName1()
    {
        return $this->hasOne(Appname::class, ['id' => 'app1']);
    }

    /**
     * Gets query for [[AppName2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppName2()
    {
        return $this->hasOne(Appname::class, ['id' => 'app2']);
    }

    /**
     * Gets query for [[AppName3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppName3()
    {
        return $this->hasOne(Appname::class, ['id' => 'app3']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
