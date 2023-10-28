<?php

namespace common\models;

use common\models\generated\UserManualActivityBlock as GeneratedUserManualActivityBlock;
use Exception;

class UserManualActivityBlock extends GeneratedUserManualActivityBlock
{
    public function aggregate() {
        $ta = TimeAdjustment::findOne([
                'user_id' => $this->user_id,
                'utc_time' => date("Y-m-d H:i:s", $this->utc_time),
                'duration' => $this->duration
            ]);

        if ($ta === NULL) {
            return false;
        }

        $data = [
            'user_id' => $this->user_id, 
            'time_adjustment_id' => $ta->id,
            'tz_offset' => $this->tz_offset, 
            'duration' => $this->duration, 
            'block_timestamp' => $this->block_timestamp
        ];

        return $this->batchInsert([$data]);

    }

    protected function batchInsert($data)
    {
        if (count($data) == 0) {
            return true;
        }

        $command = \Yii::$app->getDb()->createCommand()->batchInsert(
            'user_manual_activity_block',
            ['user_id', 'time_adjustment_id',  'tz_offset', 'duration', 'block_timestamp'],
            $data,
        );

        $command->sql .=
				' ON DUPLICATE KEY UPDATE
        `duration` = duration + VALUES(duration)
        ';

        try {
            return $command->execute();
        } catch(Exception $E) {
            return false;
        }
    }
}
