<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

class CompanySettings extends \common\models\generated\CompanySettings 
{
    /**
     * Событие изменения настроек
     */
    const EVENT_SETTINGS_CHANGE = 'settings_change';
}
