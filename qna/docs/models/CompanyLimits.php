<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_limits".
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property string $limit
 * @property string $timezone
 * @property string $days
 * @property string $created_at
 * @property string $updated_at
 * @property string $roles
 */
class CompanyLimits extends \common\models\generated\CompanyLimits
{
    const ROLES = [
		'owner',
		'admin',
		'manager',
		'user',
	];

    const DAYS = [
        'Sun',
        'Mon',
        'Tue',
        'Wed',
        'Thu',
        'Fri',
        'Sat'
    ];

    const TIMEZONE_USER = 'user';
    const TIMEZONE_COMPANY = 'company';

    const TIMEZONE = [
        self::TIMEZONE_USER,
        self::TIMEZONE_COMPANY
    ];

    CONST DEFAULT_VALUES = [
        'days' => '',
		'from' => '00:00:00',
		'to' => '23:59:59',
		'limit' => 'PT24H',
		'timezone' => 'company',
		'roles' => 'owner,admin,manager,user'
    ];

    public function rules() {
        return array_merge(parent::rules(),[
            ['created_at','default','value' => date('Y-m-d H:i:s')]
        ]);
    }
}
