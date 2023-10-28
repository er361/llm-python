<?php

namespace common\models\generated;

use common\models\SettingsChangedSign;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $name
 * @property string $email
 * @property string|null $password
 * @property string $role
 * @property int $status
 * @property int $company_id
 * @property string|null $created_at
 * @property string|null $deleted_at
 * @property string|null $date_format
 * @property string $time_format
 * @property string $timezone
 * @property string|null $auth_key
 * @property resource|null $registration_ip
 * @property string|null $last_login_at
 * @property string|null $unconfirmed_email
 * @property float|null $rate_value
 * @property string|null $rate_interval
 *
 * @property Note[] $notes
 * @property ReportQueue[] $reportQueues
 * @property TimeAdjustment[] $timeAdjustments
 * @property Company $company
 * @property UserAccess[] $userAccesses
 * @property Group[] $groups
 * @property UserAccount[] $userAccounts
 * @property UserActivityCache[] $userActivityCaches
 * @property UserActivityNew[] $userActivityNews
 * @property UserActivityOverlimit[] $userActivityOverlimits
 * @property UserApiToken[] $userApiTokens
 * @property UserAppCache[] $userAppCaches
 * @property UserAppNew[] $userAppNews
 * @property UserAppToken[] $userAppTokens
 * @property UserGroup[] $userGroups
 * @property Group[] $groups0
 * @property UserInvoicePreference[] $userInvoicePreferences
 * @property UserManualActivityBlock[] $userManualActivityBlocks
 * @property UserManualActivityNew[] $userManualActivityNews
 * @property UserNotificationOption[] $userNotificationOptions
 * @property NotificationOption[] $notificationOptions
 * @property UserToken[] $userTokens
 * @property UserVoip[] $userVoips
 *
 * @property-read UserRateHistory[] $rateHistory
 * @property-read SettingsChangedSign $settingsChangedSign
 * @property-read UserApiKey $userApiKey
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'company_id'], 'required'],
            [['role'], 'string'],
            [['status', 'company_id'], 'integer'],
            [['created_at', 'deleted_at', 'last_login_at'], 'safe'],
            [['rate_value'], 'number'],
            [['name', 'password', 'timezone'], 'string', 'max' => 64],
            [['email'], 'string', 'max' => 128],
            [['date_format'], 'string', 'max' => 12],
            [['time_format'], 'string', 'max' => 3],
            [['auth_key'], 'string', 'max' => 32],
            [['registration_ip', 'rate_interval'], 'string', 'max' => 16],
            [['unconfirmed_email'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
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
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
            'status' => 'Status',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'date_format' => 'Date Format',
            'time_format' => 'Time Format',
            'timezone' => 'Timezone',
            'auth_key' => 'Auth Key',
            'registration_ip' => 'Registration Ip',
            'last_login_at' => 'Last Login At',
            'unconfirmed_email' => 'Unconfirmed Email',
            'rate_value' => 'Rate Value',
            'rate_interval' => 'Rate Interval',
        ];
    }

    /**
     * Gets query for [[Notes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ReportQueues]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReportQueues()
    {
        return $this->hasMany(ReportQueue::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[TimeAdjustments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTimeAdjustments()
    {
        return $this->hasMany(TimeAdjustment::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[UserAccesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccesses()
    {
        return $this->hasMany(UserAccess::className(), ['owner_id' => 'id']);
    }

    /**
     * Gets query for [[Groups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Group::className(), ['id' => 'group_id'])->viaTable('user_access', ['owner_id' => 'id']);
    }

    /**
     * Gets query for [[UserAccounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccounts()
    {
        return $this->hasMany(UserAccount::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserActivityCaches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserActivityCaches()
    {
        return $this->hasMany(UserActivityCache::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserActivityNews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserActivityNews()
    {
        return $this->hasMany(UserActivityNew::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserActivityOverlimits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserActivityOverlimits()
    {
        return $this->hasMany(UserActivityOverlimit::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserApiTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserApiTokens()
    {
        return $this->hasMany(UserApiToken::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAppCaches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppCaches()
    {
        return $this->hasMany(UserAppCache::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAppNews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppNews()
    {
        return $this->hasMany(UserAppNew::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAppTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppTokens()
    {
        return $this->hasMany(UserAppToken::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroup::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Groups0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroups0()
    {
        return $this->hasMany(Group::className(), ['id' => 'group_id'])->viaTable('user_group', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserInvoicePreferences]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserInvoicePreferences()
    {
        return $this->hasMany(UserInvoicePreference::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserManualActivityBlocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserManualActivityBlocks()
    {
        return $this->hasMany(UserManualActivityBlock::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserManualActivityNews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserManualActivityNews()
    {
        return $this->hasMany(UserManualActivityNew::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserNotificationOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserNotificationOptions()
    {
        return $this->hasMany(UserNotificationOption::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[NotificationOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationOptions()
    {
        return $this->hasMany(NotificationOption::className(), ['id' => 'notification_option_id'])->viaTable('user_notification_option', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserTokens()
    {
        return $this->hasMany(UserToken::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserVoips]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserVoips()
    {
        return $this->hasMany(UserVoip::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserRateHistory]].
     *
     * @return ActiveQuery
     */
    public function getRateHistory(): ActiveQuery
    {
        return $this->hasMany(UserRateHistory::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[SettingsChangedSign]].
     *
     * @return ActiveQuery
     */
    public function getSettingsChangedSign(): ActiveQuery
    {
        return $this->hasOne(SettingsChangedSign::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserApiKey]].
     *
     * @return ActiveQuery
     */
    public function getUserApiKey(): ActiveQuery
    {
        return $this->hasOne(UserApiKey::class, ['user_id' => 'id']);
    }
}
