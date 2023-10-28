<?php

namespace common\models;

use common\models\queries\UserTimezoneHistoryQuery;
use common\models\generated\Timezone;
use yii\db\ActiveQuery;

/**
 * Class UserTimezoneHistory
 * @package common\models
 *
 * @property-read Timezone $timezone
 */
class UserTimezoneHistory extends \common\models\generated\UserTimezoneHistory
{
    public static function find(): UserTimezoneHistoryQuery
    {
        return new UserTimezoneHistoryQuery(get_called_class());
    }

    public function getTimezone(): ActiveQuery
    {
        return $this->hasOne(Timezone::class, ['id' => 'timezone_id']);
    }
}