<?php

namespace common\models;

use yii\helpers\ArrayHelper;
use common\components\filters\AccessFilter;
use common\models\generated\NotificationOption;
use common\models\User;

class UserNotificationOption extends \common\models\generated\UserNotificationOption 
{
    const FIELD_TIME_APPROVAL_REQUESTS = 'time_approval_requests';
    const FIELD_ACTIVITY_SUMMARY = 'activity_summary';
    const FIELD_TEAM_ACTIVITY_SUMMARY = 'team_activity_summary';
    const FIELD_ACTIVITY_NON_STOP = 'activity_non_stop';
    const FIELD_TEAM_ACTIVITY_IDLE = 'team_activity_idle';
    const FIELD_TIME_ACTIVITY_DELETE = 'time_activity_delete';
    const FIELD_TEAMMATE_DELETES_ACCOUNT = 'teammate_deletes_account';
    const FIELD_TIME_REQUESTS_PROCESSED = 'time_requests_processed';
    const FIELD_TRAQQ_NEWSLETTERS = 'traqq_newsletters';

    const ALL_FIELDS = [
        self::FIELD_TIME_APPROVAL_REQUESTS,
        self::FIELD_ACTIVITY_SUMMARY,
        self::FIELD_TEAM_ACTIVITY_SUMMARY,
        self::FIELD_ACTIVITY_NON_STOP,
        self::FIELD_TEAM_ACTIVITY_IDLE,
        self::FIELD_TIME_ACTIVITY_DELETE,
        self::FIELD_TEAMMATE_DELETES_ACCOUNT,
        self::FIELD_TIME_REQUESTS_PROCESSED,
        self::FIELD_TRAQQ_NEWSLETTERS,
    ];

    /**
     * Разрешённые атрибуты для каждой отдельной роли пользователя
     * @param User $user
     * @return array
     */
    public static function getPermittedAttributes($user): array {
        $hasTimeApprovePermission = AccessFilter::checkUserPermissions($user, ['time_adjustment']);

        $attributes = [
            'owner' => self::ALL_FIELDS,
            'admin' => [
                $hasTimeApprovePermission ? self::FIELD_TIME_APPROVAL_REQUESTS : null,
                self::FIELD_ACTIVITY_SUMMARY,
                self::FIELD_TEAM_ACTIVITY_SUMMARY,
                self::FIELD_ACTIVITY_NON_STOP,
                self::FIELD_TEAM_ACTIVITY_IDLE,
                self::FIELD_TIME_ACTIVITY_DELETE,
                self::FIELD_TEAMMATE_DELETES_ACCOUNT,
                self::FIELD_TRAQQ_NEWSLETTERS,
            ],
            'manager' => [
                $hasTimeApprovePermission ? self::FIELD_TIME_APPROVAL_REQUESTS : null,
                self::FIELD_ACTIVITY_SUMMARY,
                self::FIELD_TEAM_ACTIVITY_SUMMARY,
                self::FIELD_ACTIVITY_NON_STOP,
                self::FIELD_TEAM_ACTIVITY_IDLE,
                self::FIELD_TIME_ACTIVITY_DELETE,
                self::FIELD_TRAQQ_NEWSLETTERS,
            ],
            'user' => [
                self::FIELD_ACTIVITY_SUMMARY,
                self::FIELD_TIME_REQUESTS_PROCESSED,
                self::FIELD_TRAQQ_NEWSLETTERS,
            ],
        ];

        return $attributes[$user->role];
    }
    
    /**
     * @param  User $user
     * @return array
     */
    public static function getUserSettingsArray($user): array
    {
        $permittedAttributes = self::getPermittedAttributes($user);

        $userNotificationSettings = NotificationOption::find()
            ->select(['notification_option.name', 'IFNULL(user_notification_option.value, notification_option.default_value) AS value'])
            ->leftJoin(
                self::tableName(), 
                'user_notification_option.notification_option_id = notification_option.id AND user_notification_option.user_id = :userID',
                ['userID' => $user->id]
            )
            ->where(['notification_option.name' => $permittedAttributes])
            ->asArray()
            ->all();

        $notificationSettingsMap = ArrayHelper::map(
            $userNotificationSettings, 
            'name', 
            'value'
        );
        
        ksort($notificationSettingsMap);

        return $notificationSettingsMap;
    }
}
