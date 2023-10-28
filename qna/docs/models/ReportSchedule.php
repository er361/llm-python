<?php

namespace common\models;

use common\models\queries\ReportScheduleQuery;

/**
 * Class ReportSchedule
 * @package common\models
 */
class ReportSchedule extends \common\models\generated\ReportSchedule {

    const REPORT_TYPE = [
        'activity' => 'activity',
        'app' => 'app',
        'amount' => 'amount',
        'time-adjustment' => 'time-adjustment',
        'deleted' => 'deleted',
        'idle' => 'idle',
        'weekly' => 'weekly'
    ];

    const BY = [
        'date' => 'date',
        'people' => 'people',
        'group' => 'group'
    ];

    const OUTPUT = [
        'csv' => 'csv',
        'pdf' => 'pdf'
    ];

    const PERIOD = [
        'today' => 'today',
        'yesterday' => 'yesterday',
        'this_week' => 'this_week',
        'last_week' => 'last_week',
        'this_month' => 'this_month',
        'last_month' => 'last_month',
    ];

    const FREQUENCY = [
        'daily' => 'daily',
        'weekly' => 'weekly',
        'monthly' => 'monthly'
    ];

    const DAILY = [
        'mon' => 'mon',
        'tue' => 'tue',
        'wed' => 'wed',
        'thu' => 'thu',
        'fri' => 'fri',
        'sat' => 'sat',
        'sun' => 'sun'
    ];

    const WEEKLY = [
        'mon' => 'mon',
        'tue' => 'tue',
        'wed' => 'wed',
        'thu' => 'thu',
        'fri' => 'fri',
        'sat' => 'sat',
        'sun' => 'sun'
    ];

    const MONTHLY = [
        'first' => 'first',
        '15th' => '15th',
        'last' => 'last',
    ];

    public const MONTHLY_ARRAY = [
        '1st_week_end' => '1st_week_end',
        '2nd_week_end' => '2nd_week_end',
        '3rd_week_end' => '3rd_week_end',
        'last_week_end' => 'last_week_end'
    ];

    public const ALLOWED_VIEWS = [
        'time-adjustment' => [
            'people',
            'date',
            'group'
        ],
        'activity' => [
            'people',
            'date',
            'group'
        ],
        'app' => [
            'date',
            'people',
            'group'
        ],
        'amount' => [
            'people',
            'date',
            'group'
        ],
        'idle' => [
            'people',
            'date',
            'group'
        ],
        'deleted' => [
            'people',
            'date',
            'group'
        ],
        'weekly' => [
            'date',
        ]
    ];

    public static function find(): ReportScheduleQuery
    {
        return new ReportScheduleQuery(get_called_class());
    }

    /**
     * @param string $report
     * @return string[]
     */
    public static function getAllowedViews(string $report): array
    {
        if (in_array($report, self::REPORT_TYPE)) {
            return self::ALLOWED_VIEWS[$report];
        }

        return [];
    }

    /**
     * @param int $userId
     * @return ReportScheduleQuery
     */
    public static function findByUser(int $userId): ReportScheduleQuery
    {
        return self::find()->byUser($userId);
    }

    /**
     * @param array $ids
     * @return ReportScheduleQuery
     */
    public static function findByIds(array $ids = []): ReportScheduleQuery
    {
        return self::find()->byIds($ids);
    }
}
