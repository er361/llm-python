<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_site_v3".
 *
 * @property int $id
 * @property int $user_id
 * @property string $utc_datetime_15m
 * @property int $updated_at
 * @property int|null $site1
 * @property int|null $duration1
 * @property int|null $site2
 * @property int|null $duration2
 * @property int|null $site3
 * @property int|null $duration3
 *
 * @property Sitename $siteName1
 * @property Sitename $siteName2
 * @property Sitename $siteName3
 * @property User $user
 */
class UserSiteV3 extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_site_v3';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'utc_datetime_15m', 'updated_at'], 'required'],
            [['user_id', 'updated_at', 'site1', 'duration1', 'site2', 'duration2', 'site3', 'duration3'], 'integer'],
            [['utc_datetime_15m'], 'safe'],
            [['user_id', 'utc_datetime_15m'], 'unique', 'targetAttribute' => ['user_id', 'utc_datetime_15m']],
            [['site1'], 'exist', 'skipOnError' => true, 'targetClass' => Sitename::class, 'targetAttribute' => ['site1' => 'id']],
            [['site2'], 'exist', 'skipOnError' => true, 'targetClass' => Sitename::class, 'targetAttribute' => ['site2' => 'id']],
            [['site3'], 'exist', 'skipOnError' => true, 'targetClass' => Sitename::class, 'targetAttribute' => ['site3' => 'id']],
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
            'site1' => 'Site1',
            'duration1' => 'Duration1',
            'site2' => 'Site2',
            'duration2' => 'Duration2',
            'site3' => 'Site3',
            'duration3' => 'Duration3',
        ];
    }

    /**
     * Gets query for [[SiteName1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiteName1()
    {
        return $this->hasOne(Sitename::class, ['id' => 'site1']);
    }

    /**
     * Gets query for [[SiteName2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiteName2()
    {
        return $this->hasOne(Sitename::class, ['id' => 'site2']);
    }

    /**
     * Gets query for [[SiteName3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiteName3()
    {
        return $this->hasOne(Sitename::class, ['id' => 'site3']);
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
