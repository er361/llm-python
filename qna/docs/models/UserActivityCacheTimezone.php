<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use common\helpers\DateTimeHelper;
use common\components\time\Time;

/**
 * Единая модель для таблиц user_activity_cache_*
 * @package common\models
 */
class UserActivityCacheTimezone extends Model
{	
    /**
     * Функция разбивает массив $activities на части и сохраняет его в кэш таблицы таймзон.
     * На одну часть выполняется сохранение во все таблицы одним запросом.
     * @param  array $activities
     * @return bool
     */
    public static function aggregate(array $activities): bool
    {
        $db = Yii::$app->db;
        $allTableNamesByOffset = static::getAllTableNamesByOffset();

        foreach(array_chunk($activities, 200) as $chunkedActivity) {
            $sql = ""; // переменная которая будет содержать все INSERT в виде строки, чтобы выполнить их в одном запросе

            $sql .= "BEGIN;";
            foreach ($allTableNamesByOffset as $offset => $tableName) {
                // создаём строки для VALUES и помещаем их в массив
                $valuesArr = array_map(function($block) use ($offset) {
                    $dateForCurrentTimezone =  date(Time::FORMAT_DATE, strtotime($block['utc_time']) + $offset);
                    return new Expression("({$block['user_id']}, '{$dateForCurrentTimezone}', {$block['duration']}, {$block['activity']})");
                }, $chunkedActivity);

                // не используем batchInsert потому, что вызвав его десятки раз мы потеряем много времени
                $valuesStr = implode(', ', $valuesArr);
                $sql .= <<<SQL
                    INSERT INTO {{%$tableName}} (`user_id`, `user_date`, `duration`, `activity`) VALUES $valuesStr ON DUPLICATE KEY UPDATE
                    `activity` = IF (
                        (
                            (duration + VALUES(duration)) > 0
                        ),
                        (
                            ((duration * activity) + (VALUES(duration) * VALUES(activity))) / (duration + VALUES(duration))
                        ),
                        (
                            IFNULL (activity, 0)
                        )
                    ),
                    `duration` = duration + VALUES(duration);
                SQL;
            }
            $sql .= "COMMIT;";
            
            $db->createCommand($sql)->execute();
        }

        return true;
    }
    
    /**
     * Получаем список таблиц для всех таймзон.
     * @return array [offset_in_second => table_name]
     */
    static function getAllTableNamesByOffset(): array
    {
        $allTableNames = [];
        $allTimezoneOffsets = array_keys(DateTimeHelper::getAllTimezoneOffsetList());
        foreach ($allTimezoneOffsets as $offset) {
            if ($offset == 0) {
                $allTableNames[$offset] = 'user_activity_cache_utc';
            } elseif ($offset < 0) {
                $allTableNames[$offset] = 'user_activity_cache_tzw_' . (abs($offset) / 60) / 15;
            } elseif ($offset > 0) {
                $allTableNames[$offset] = 'user_activity_cache_tze_' . ($offset / 60) / 15;
            }
        }
        ksort($allTableNames, SORT_NATURAL);

        return $allTableNames;
    }
}
