<?php

namespace common\models;

/**
 * Class SettingsChangedSign
 * @package common\models
 */
class SettingsChangedSign extends generated\SettingsChangedSign
{
    public function beforeValidate(): bool
    {
        $this->company %= self::MAX_VALUE;
        $this->user %= self::MAX_VALUE;
        $this->server %= self::MAX_VALUE;
        $this->company_info %= self::MAX_VALUE;
        $this->user_info %= self::MAX_VALUE;

        return parent::beforeValidate();
    }
}