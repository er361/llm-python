<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "appname".
 *
 * @property int $id
 * @property string|null $name
 * @property string $updated_at
 *
 * @property UserAppV3[] $userAppV3App1
 * @property UserAppV3[] $userAppV3App2
 * @property UserAppV3[] $userAppV3App3
 */
class AppName extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'appname';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[UserAppV3App1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppV3App1()
    {
        return $this->hasMany(UserAppV3::class, ['app1' => 'id']);
    }

    /**
     * Gets query for [[UserAppV3App2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppV3App2()
    {
        return $this->hasMany(UserAppV3::class, ['app2' => 'id']);
    }

    /**
     * Gets query for [[UserAppV3App3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppV3App3()
    {
        return $this->hasMany(UserAppV3::class, ['app3' => 'id']);
    }
}
