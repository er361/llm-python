<?php

namespace common\models;

use common\components\time\Time;
use Exception;

class UserManualActivityNew extends \common\models\generated\UserManualActivityNew
{
    public static function addManualActivityBlock($activity)
    {
        $ta = TimeAdjustment::findOne(['user_id' => $activity['user_id'], 'utc_time' => date("Y-m-d H:i:s", $activity['utc_time']), 'duration' => $activity['duration']]);
        
        if ($ta === NULL) {
            $user = User::findOne(['id' => $activity['user_id']]);

            $createdDate = new \DateTime();

            $ta = new TimeAdjustment();
            $ta->user_id = $activity['user_id'];
            $ta->company_id = $user->company_id;
            $ta->status = TimeAdjustment::STATUS_APPROVED;
            $ta->reason = $activity['reason'];
            $ta->processed_user_id = $activity['user_id'];
            $ta->duration = $activity['duration'];
            $ta->user_time = date("Y-m-d H:i:s", $activity['user_time']);
            $ta->utc_time = date("Y-m-d H:i:s", $activity['utc_time']);
            $ta->created_at = $createdDate->format('Y-m-d H:i:s');
            $ta->save();
        }

        if ($ta->id === NULL) return;

        $stime = $activity['utc_time'];
        $sblock15 = floor($stime / 900) * 900;

        $etime = $stime + $activity['duration'];
        $eblock15 = floor($etime / 900) * 900;

        $segments = (($eblock15 - $sblock15) / 900) + 1;

        $tzOffset = (($activity['user_time'] - $activity['utc_time']) / 60) / 15;

        for ($j = 1; $j <= $segments; $j++) {
            if ($j == 1) {
                $utc_time = $activity['utc_time'];
                $utc_time_15m = floor($activity['utc_time'] / 900) * 900;
                $startOffset = $utc_time - $utc_time_15m;
            } else {
                $utc_time = floor($activity['utc_time'] / 900) * 900 + (900 * ($j - 1));
                $utc_time_15m = floor($activity['utc_time'] / 900) * 900 + (900 * ($j - 1));
                $startOffset = 0;
            }

            $uts_e_time = $j == $segments ? $activity['utc_time'] + $activity['duration'] :
                floor($activity['utc_time'] / 900) * 900 + (900 * $j);

            $activity_s = [
                'user_id' => $activity['user_id'],
                'time_adjustment_id' => $ta->id,
                'utc_time_15m' => date(Time::FORMAT_MYSQL, $utc_time_15m),
                'tz_offset' => $tzOffset,
                'start_offset' => $startOffset,
                'duration' => $uts_e_time - $utc_time,
                'block_id' => $activity['block_id']
            ];

            if ($activity_s['duration'] == 0) continue;
            $newActivities[] = $activity_s;
        }

        return self::batchInsertManualTime($newActivities);
    }

    private static function batchInsertManualTime($data)
    {
        if (count($data) == 0) {
            return true;
        }

        $command = \Yii::$app->getDb()->createCommand()->batchInsert(
            'user_manual_activity_new',
            ['user_id', 'time_adjustment_id', 'utc_time_15m', 'tz_offset', 'start_offset', 'duration', 'block_id'],
            $data,
        );

        try {
            return $command->execute();
        } catch(Exception $E) {
            return false;
        }
    }

    public static function checkIntersect($userId, $timeStart, $timeFinish, $status = NULL)
    {
        $exist = self::find()
            ->where(['OR', 
                [
                    'AND', 
                    ['<=', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeStart], 
                    ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE)', $timeStart], 
                    [
                        '>',
                        'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), '
                            . 'INTERVAL user_manual_activity_new.`duration` SECOND)',
                        $timeStart
                    ]
                ], 
                [
                    'AND', 
                    ['<', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeFinish],
                    ['<', 'utc_time_15m', $timeFinish],
                    [
                        '>=',
                        'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), '
                            . 'INTERVAL user_manual_activity_new.`duration` SECOND)',
                        $timeFinish
                    ]
                ],
                [
                    'AND',
                    ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND)', $timeStart],
                    ['>', 'DATE_ADD(`utc_time_15m`,INTERVAL 15 MINUTE)', $timeStart],
                    [
                        '<',
                        'DATE_ADD(DATE_ADD(`utc_time_15m`,INTERVAL `start_offset` SECOND), '
                        . 'INTERVAL user_manual_activity_new.`duration` SECOND)',
                        $timeFinish
                    ]
                ]
            ])
            ->andWhere(['user_manual_activity_new.user_id' => $userId])
            ->leftJoin('time_adjustment', ' time_adjustment.id = user_manual_activity_new.time_adjustment_id');

        if($status) {
            $exist->andWhere(['status' => $status]);
        }
        if($exist->count() > 0) return true;

        return false;
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        $data = $this->toArray();
        $activity = new UserManualActivityDelete();
        $activity->load($data, '');
        if (!parent::beforeDelete()) {
            return false;
        }
        $activity->save();
        return true;
    }
}
