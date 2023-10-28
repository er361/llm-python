<?php

namespace common\models;

use api2\components\AggregateActivity;
use api2\components\RealtimesBlocks;
use api2\forms\ActivityAddTime;
use api2\forms\UserActivityBitConverter;
use api2\models\ActivityBlock;
use common\components\time\DateInterval;
use common\components\time\Time;
use Exception;
use Throwable;
use yii\db\Expression;

/**
 * Class UserActivityNew
 * @package common\models
 */
class UserActivityNew extends generated\UserActivityNew
{
    const STATUS_LOW = 39;
    const STATUS_NORMAL = 59;
    
    public static function aggregate($activitys)
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
                } elseif ( !$activityAgg->updateBlock($block) ) {
                    $block = $activityAgg->createBlock();
                    $blocks->append($block);
                }
            }

            // $activity = AggregateActivityNew::createFrom($activity, $this->timezone);
            // if($activity['block_id'] != $blockId) {
            //     $block = $activity->createBlock();
            //     $blocks->append($block);
            //     $blockId = $activity['block_id'];
            // } else {
            //     $activity->updateBlock($block);
            // }

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
                    'block_id' => $activity['block_id']
                ];

                if($activity_s['duration'] > 0) $newActivities[] = $activity_s;
            }
        }

        //print_r($newActivities);

        return self::batchInsertTime($newActivities);
    }

    public static function batchInsertTime($data)
    {
        if (count($data) == 0) {
            return true;
        }

        $command = \Yii::$app->getDb()->createCommand()->batchInsert(
            'user_activity_new',
            ['user_id', 'utc_time_15m', 'tz_offset', 'start_offset', 'duration', 'activity', 'block_id'],
            $data,
        );

        /*
         * Если необходимо сделать апдейт нескольких полей ON DUPLICATE KEY UPDATE, нужно следить, чтобы поля,
         * зависимые от других, вычислялись после тех, которые зависят от них.
         * Например, duration обязательно должен обновляться ТОЛЬКО после вычисления новой активности.
         * Иначе, в качестве duration в ней уже будет использовано обновленное значение.
         */
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
            return $command->execute() && (new UserActivityBitConverter(['data' => $data]))->append();
        } catch(Exception $E) {
            return false;
        }
    }

    public static function getDayActivityDuration($userId, $date)
    {
        $utcTimeConvert = new Expression("convert_tz(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"), TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i')))");
        $allDayActivities = self::find()
                ->select(['total' => new Expression("SUM(`duration`)")])
                ->where(['user_id' => $userId])
                ->andWhere(['BETWEEN', $utcTimeConvert, $date . ' 00:00:00', $date . ' 23:59:59'])
                ->asArray()->one();
        return $allDayActivities;
    }

    public static function addTimeAdjustmentActivity($timeAdjustment, $isError)
    {
        if (!TimeAdjustment::checkActivityIntersect($timeAdjustment, $isError)) {
            return TimeAdjustment::ERROR_CODE_INTERSECT;
        }
        
        $addParams = [
            'class' => ActivityAddTime::class,
            'ta_id' => $timeAdjustment->id,
            'user_id' => $timeAdjustment->user_id,
            'reason' => $timeAdjustment->reason,
            'activity' => null,
            'block_id' => strtotime($timeAdjustment->utc_time),
            'duration' => Time::createByFields($timeAdjustment->user_time, $timeAdjustment->utc_time, $timeAdjustment->duration),
        ];
        
        $addModel = \Yii::createObject($addParams);
        
        
        
        try{
            $addModel->setScenario(ActivityAddTime::SCENARIO_ADD_SINGLE);
            $addModel->validate();
            $addModel->save();
        } catch(Exception $E) {
            echo $E->getMessage();
            die();
        }

        return TimeAdjustment::ERROR_CODE_OK;
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

        // echo $exist->createCommand()->rawSql; die();

        // AND DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND) <= '2022-11-03 12:20:00' 
        // AND DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE) > '2022-11-03 12:20:00' 
        // AND DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), INTERVAL `duration` SECOND) > '2022-11-03 12:20:00' 

        if($exist->count() > 0) return true;
        
        return false;
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        $data = $this->toArray();
        $data['type'] = UserActivityDelete::TYPE_AUTO;
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

    public static function createRandomActivityBlock($userId, $time)
    {
        $interval = new DateInterval('PT'.rand(10,10).'M');

        $block = new ActivityBlock();
        $block->user_id = $userId;
        $block->activity = 50;//$this->activity;
        $block->duration = $interval->getSeconds();
        $block->user_time = $time->format(Time::FORMAT_MYSQL);
        $block->utc_time = $time->toUtcTime()->format(Time::FORMAT_MYSQL);
        $block->block_id = strtotime($time->toUtcTime()->format(Time::FORMAT_MYSQL));

        // $params = [
        //     'class' => ActivityAddTime::class,
        //     'user_id' => $user->id,
        //     'activity' => RandomHelper::randomInt(50),
        //     'duration' => $this->randomDuration($time),
        //     //'block_id' => strtotime($time->toUtcTime()->format(Time::FORMAT_MYSQL))
        // ];

        return $block;
    }

    /**
     * @param DateTime $time
     * @return UserActivityNew
     */
    public function createRandom($time,$avgActivity=50)
    {
        $interval = new DateInterval('PT'.rand(10,10).'M');
        $this->addSpace($time);
        $this->onlyWorktime($time);

        $this->activity = $this->random($avgActivity);
        $this->user_time = $time->format(Time::FORMAT_MYSQL);
        $this->utc_time_15m = $time->toUtcTime()->format(Time::FORMAT_MYSQL);
        $this->duration = $interval->getSeconds();

        return $this;
    }

    /**
     * @param DateTime $time
     * @return boolean True, if space added
     */
    protected function addSpace($time)
    {
        $space = rand(0,100) > 95;
        if ( $space ) {
            $time->add(new DateInterval('PT'.rand(10,70).'M'));
            return true;
        }
        return false;
    }

    /**
     * @param DateTime $time
     */
    protected function onlyWorktime($time)
    {
        $interval = new DateInterval('PT10M');
        while ( $time->format('H') < 8 || $time->format('H')>18 ) {
            $time->add($interval);
        }
    }

    public static function getUserActivityStatus($activity = 0)
    {
        if (is_null($activity) || $activity === '' || $activity === '-') {
            return 'empty';
        }
        $activity = round($activity);
        if (intval($activity) === 0) {
            return 'idle';
        }
        if ($activity <= self::STATUS_LOW) {
            return 'low';
        }
        if ($activity > self::STATUS_LOW && $activity <= self::STATUS_NORMAL) {
            return 'normal';
        }
        return 'high';
    }

    /**
     * * Полное удаление активностей с их зависимостями 
     * @param array $activities
     * @return bool
     */
    public static function totalDelete($activities)
    {

    }
}
