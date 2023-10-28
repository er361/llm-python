<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use api2\models\User;
use yii\db\ActiveQuery;
use api2\models\events\UserSettingsTimezoneUpdatedEvent;
use common\models\generated\Timezone;
/**
 * Class UserSettings
 * @package common\models
 * @property-read Timezone $userTimezone
 * @property-read User $user
 */
class UserSettings extends \common\models\generated\UserSettings 
{
    /**
     * Событие обновления таймзоны
     */
    const EVENT_TIMEZONE_UPDATE = 'timezone_update';

    /**
     * Событие изменения настроек
     */
    const EVENT_SETTINGS_CHANGE = 'settings_change';
    
    /**
     * @return ActiveQuery
     */
    public function getUserTimezone(): ActiveQuery
    {
        return $this->hasOne(Timezone::class, ['timezone' => 'timezone']);
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['timezone'])) {
            $this->trigger(self::EVENT_TIMEZONE_UPDATE, new UserSettingsTimezoneUpdatedEvent([
                'userSettings' => $this
            ]));
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Возвращает список полей, которые можно менять в настройках пользователя
     *
     * @return string[]
     */
    public static function getAvailableForChangeFields(): array
    {
        return [
            'timezone',
            'date_format',
            'time_format',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
