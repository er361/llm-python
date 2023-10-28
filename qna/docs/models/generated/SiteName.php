<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "sitename".
 *
 * @property int $id
 * @property string|null $url
 * @property string $updated_at
 *
 * @property UserSiteV3[] $userSiteV3Site1
 * @property UserSiteV3[] $userSiteV3Site2
 * @property UserSiteV3[] $userSiteV3Site3
 */
class SiteName extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sitename';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at'], 'safe'],
            [['url'], 'string', 'max' => 32],
            [['url'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[UserSiteV3Site1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSiteV3Site1()
    {
        return $this->hasMany(UserSiteV3::class, ['site1' => 'id']);
    }

    /**
     * Gets query for [[UserSiteV3Site2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSiteV3Site2()
    {
        return $this->hasMany(UserSiteV3::class, ['site2' => 'id']);
    }

    /**
     * Gets query for [[UserSiteV3Site3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSiteV3Site3()
    {
        return $this->hasMany(UserSiteV3::class, ['site3' => 'id']);
    }
}
