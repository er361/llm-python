<?php

namespace common\models;

use api2\components\AggregateActivity;
use api2\components\RealtimeBlocks;
use api2\components\RealtimesBlocks;
use common\components\time\Time;
use common\exceptions\ValidateException;
use Exception;
use Throwable;

/**
 * Class UserActivityNew
 * @package common\models
 */
class UserActivityOverlimit extends generated\UserActivityOverlimit
{
    const STATUS = [
        0 => 'declined',
        1 => 'pending',
        2 => 'approved',
    ];

    const STATUS_DECLINED = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;

    public static function aggregate($activitys, $status =  self::STATUS_PENDING)
    {
        $newActivities = [];
        $blocks = new RealtimesBlocks();

        foreach ($activitys as $activity) {
            $activity = $activity->toArray();
            
            $activity['user_time'] = strtotime($activity['user_time']);
            $activity['utc_time'] = strtotime($activity['utc_time']);

            $tzOffset = round((($activity['user_time'] - $activity['utc_time']) / 60) / 15);

            $stime = $activity['utc_time'];
            $sblock15 = floor($stime / 900) * 900;

            $etime = $stime + $activity['duration'];
            $eblock15 = floor($etime / 900) * 900;

            $segments15 = (($eblock15 - $sblock15) / 900) + 1;

            
            $date = date('Y-m-d', $activity['user_time']);
            $user_id = $activity['user_id'];
            $activityAgg = AggregateActivity::createFrom($activity, false);
            $activityAgg->utc_time = date(Time::FORMAT_MYSQL, $activityAgg->utc_time);
            $activityAgg->user_time = date(Time::FORMAT_MYSQL, $activityAgg->user_time);
            $seconds = (!$blocks->end($date, $user_id)) ? false : $activityAgg->getDistance($blocks->end($date, $user_id));
            while ($activityAgg->duration > 0 ) {
                if ($seconds === false) {
                    $block = $activityAgg->createBlock();
                    $blocks->append($block);
                } elseif ($seconds > 1 ) {
                    $block = $activityAgg->createBlock();
                    $blocks->append($block);
                } elseif (!$activityAgg->updateBlock($block)) {
                    $block = $activityAgg->createBlock();
                    $blocks->append($block);
                }
            }

            for ($j = 1; $j <= $segments15; $j++) {
                if ($j == 1) {
                    $utc_time = $activity['utc_time'];
                    $utc_time_15m = floor($activity['utc_time'] / 900) * 900;
                    $startOffset = $utc_time - $utc_time_15m;
                } else {
                    $utc_time = floor($activity['utc_time'] / 900) * 900 + (900 * ($j - 1));
                    $utc_time_15m = floor($activity['utc_time'] / 900) * 900 + (900 * ($j - 1));
                    $startOffset = 0;
                }

                $uts_e_time = $j == $segments15 ? $activity['utc_time'] + $activity['duration'] :
                    floor($activity['utc_time'] / 900) * 900 + (900 * $j);

                $duration = $uts_e_time - $utc_time;

                $activity_s = [
                    'user_id' => $activity['user_id'],
                    'utc_time_15m' => date(Time::FORMAT_MYSQL, $utc_time_15m),
                    'tz_offset' => $tzOffset,
                    'start_offset' => $startOffset,
                    'duration' => $duration,
                    'activity' => $activity['activity'],
                    'block_id' => $activity['block_id'],
                    'status' => $status
                ];

                if($activity_s['duration'] > 0) $newActivities[] = $activity_s;
            }
        }
        
        return self::batchInsertTime($newActivities);
    }

    public static function batchInsertTime($data)
    {
        if (count($data) == 0) {
            return true;
        }

        $command = \Yii::$app->getDb()->createCommand()->batchInsert(
            'user_activity_overlimit',
            ['user_id', 'utc_time_15m', 'tz_offset', 'start_offset', 'duration', 'activity', 'block_id', 'status'],
            $data,
        );

        $command->sql .=
            ' ON DUPLICATE KEY UPDATE
            `activity` =
                IF (
                    (
                        (duration + VALUES(duration)) > 0
                    )
                    ,
                    ROUND(
                        (
                            (duration * activity) +
                            (VALUES(duration) * VALUES(activity))
                        ) / (duration + VALUES(duration))
                    )
                    ,
                    activity
                ),
                `duration` = duration + VALUES(duration),
                `start_offset` = IF(start_offset < VALUES(start_offset), start_offset, VALUES(start_offset))
            ';

        try {
            return $command->execute();
        } catch (Exception $E) {
            echo $E->getMessage();
            return false;
        }
    }

    public static function approve($overlimit)
    {
        $activityArr = [
            'user_id' => $overlimit['user_id'],
            'user_date' => $overlimit['user_date'],
            'duration' => $overlimit['duration'],
            'activity' => $overlimit['activity']
        ];
        UserActivityCache::realAggregate([$activityArr]);

        self::updateAll(['status' => self::STATUS_APPROVED], ['id' => $overlimit['id']]);
    }

    public static function approveAll($user_id)
    {
        $overlimits = self::find()
            ->select([
                'id', 
                'user_id', 
                'duration', 
                'activity',
                "DATE_FORMAT(convert_tz(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"), TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i'))),'%Y-%m-%d') as user_date"
            ])
            ->where(['user_id' => $user_id, 'status' => self::STATUS_PENDING])
            ->asArray()->all();

        foreach ($overlimits as $overlimit) {
            self::approve($overlimit);
        }
    }

    public static function approveAllByCompany($company)
    {
        $rolesArray = array_slice(User::ROLES, 0, $company->permissions->overlimit_approve);
        $users = User::find()
            ->where(['company_id' => $company->id])
            ->andWhere(['IN', 'role', $rolesArray])
            ->all();

        foreach ($users as $user) {
            self::approveAll($user->id);
        }
    }

    public static function checkIntersect($userId, $timeStart, $timeFinish)
    {
        $exist = self::find()
        ->where(['OR', 
            ['AND', 
                ['<=', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeStart], 
                ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE)', $timeStart], 
                ['>', 'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), INTERVAL `duration` SECOND)', $timeStart]
            ], 
            ['AND', 
                ['<', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeFinish],
                ['<', 'utc_time_15m', $timeFinish],
                ['>=', 'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), INTERVAL `duration` SECOND)', $timeFinish]
            ],
            ['AND',
                ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE)', $timeStart],
                ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeStart],
                ['<', 'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), INTERVAL `duration` SECOND)', $timeFinish]
            ]
        ])
        ->andWhere(['user_id' => $userId]);

        //print_r($exist->createCommand()->getRawSql()); die();

        if($exist->count() > 0) return true;
        
        return false;
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        $data = $this->toArray();
        $data['type'] = UserActivityDelete::TYPE_OVERTIME;
        
        try {
            $activity = new UserActivityDelete();
            $activity->load($data, '');
            if (!parent::beforeDelete()) {
                return false;
            }
            $activity->batchInsertTime();
        } catch (Throwable $e) {
            throw new Exception("Activity delete error: " . $e->getMessage());
        }
        return true;
    }
}
