<?php

namespace common\models\queries;

use common\models\UserTimezoneHistory;
use yii\db\ActiveQuery;

class UserTimezoneHistoryQuery extends ActiveQuery
{
    /**
     * Поиск по диапазону дат
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return UserTimezoneHistoryQuery
     */
    public function orBetweenDates(string $dateFrom, string $dateTo): UserTimezoneHistoryQuery
    {
        $condition = $this->getBetweenDatesCondition($dateFrom, $dateTo);
        return $this->orWhere($condition);
    }

    /**
     * Условие получения записей в диапазоне дат
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    private function getBetweenDatesCondition(string $dateFrom, string $dateTo): array
    {
        $dateFrom = date('Y-m-d', strtotime($dateFrom)) . ' 00:00:00';
        $dateTo = date('Y-m-d', strtotime($dateTo)) . ' 23:59:59';

        return [
            'between',
            'utc_datetime',
            $dateFrom,
            $dateTo
        ];
    }

    /**
     * Поиск по предыдущей таймзоне. Если запись не найдена, то возвращается первая запись
     *
     * @param string $date
     * @param int $userId
     * @return UserTimezoneHistoryQuery
     */
    public function orPreviousOrFirstTimezone(string $date, int $userId): UserTimezoneHistoryQuery
    {
        $minDateSubQuery = self::getMinDateQuery($userId);

        $query = UserTimezoneHistory::find()
            ->select('utc_datetime')
            ->where([
                'and',
                ['user_id' => $userId],
                [
                    'or',
                    // Предыдущая запись
                    ['<', 'utc_datetime', $date],
                    [
                        'and',
                        // Первая запись
                        ['>', 'utc_datetime', $date],
                        ['=', 'utc_datetime', $minDateSubQuery]
                    ],
                ]
            ])
            ->orderBy(['utc_datetime' => SORT_DESC])
            ->limit(1);

        return $this->orWhere(['=', 'utc_datetime', $query]);
    }

    /**
     * Запрос на получение минимальной даты для пользователя по истории таймзон
     *
     * @param int $userId
     * @return UserTimezoneHistoryQuery
     */
    private static function getMinDateQuery(int $userId): UserTimezoneHistoryQuery
    {
        return UserTimezoneHistory::find()
            ->select('min(utc_datetime)')
            ->where(['user_id' => $userId])
            ->limit(1);
    }
}