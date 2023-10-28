<?php

namespace common\models\generated;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification_option".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string $default_value JSON-encoded scalar or object {value:"",...options}
 *
 * @property UserNotificationOption[] $userNotificationOptions
 * @property User[] $users
 */
class NotificationOption extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'default_value'], 'required'],
            [['type'], 'string'],
            [['name'], 'string', 'max' => 31],
            [['description', 'default_value'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'type' => Yii::t('app', 'Type'),
            'default_value' => Yii::t('app', 'Default Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserNotificationOptions()
    {
        return $this->hasMany(UserNotificationOption::class, ['notification_option_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('userNotificationOptions');
    }
}
