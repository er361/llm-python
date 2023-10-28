<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_group_cache".
 *
 * @property int $id
 * @property int $group_id
 * @property string $user_date
 * @property int $duration
 * @property string $app
 * @property string $app_name
 *
 * @property Group $group
 */
class UserAppGroupCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_app_group_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'user_date', 'duration', 'app', 'app_name'], 'required'],
            [['group_id', 'duration'], 'integer'],
            [['user_date'], 'safe'],
            [['app'], 'string', 'max' => 32],
            [['app_name'], 'string', 'max' => 255],
            [['group_id', 'user_date', 'app'], 'unique', 'targetAttribute' => ['group_id', 'user_date', 'app']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'user_date' => 'User Date',
            'duration' => 'Duration',
            'app' => 'App',
            'app_name' => 'App Name',
        ];
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }
}
