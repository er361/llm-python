<?php

namespace common\models\generated;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_notification_option".
 *
 * @property int $user_id
 * @property int $notification_option_id
 * @property string $value JSON-encoded scalar or object {value:"",...options}
 *
 * @property NotificationOption $notificationOption
 * @property User $user
 */
class UserNotificationOption extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_notification_option';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'notification_option_id', 'value'], 'required'],
            [['user_id', 'notification_option_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
            [['user_id', 'notification_option_id'], 'unique', 'targetAttribute' => ['user_id', 'notification_option_id']],
            [['notification_option_id'], 'exist', 'skipOnError' => true, 'targetClass' => NotificationOption::class, 'targetAttribute' => ['notification_option_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'notification_option_id' => Yii::t('app', 'Notification Option ID'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationOption()
    {
        return $this->hasOne(NotificationOption::class, ['id' => 'notification_option_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
