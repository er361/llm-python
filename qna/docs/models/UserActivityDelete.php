<?php

namespace common\models;

use Yii;

/**
 * Class UserActivityDelete
 * @package common\models
 */
class UserActivityDelete extends generated\UserActivityDelete {
	const TYPE = [
		0 => 'auto',
		1 => 'manual',
		2 => 'overtime',
	];
	const TYPE_AUTO = 0;
	const TYPE_MANUAL = 1;
	const TYPE_OVERTIME = 2;
	
	public function batchInsertTime() {
		$data = $this->toArray();
		if (count($data) == 0) {
			return true;
		}
		if (isset($data['id'])) {
			unset($data['id']);
		}
		$command = Yii::$app
			->getDb()
			->createCommand()
			->batchInsert(
				self::tableName(),
				[
					'user_id',
					'utc_time_15m',
					'tz_offset',
					'start_offset',
					'duration',
					'activity',
					'block_id',
					'type'
				],
				[$data],
			);
		$command->sql .=
				' ON DUPLICATE KEY UPDATE
				`duration` = duration + VALUES(duration),
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
				`start_offset` = IF(start_offset < VALUES(start_offset), start_offset, VALUES(start_offset))
			';

		//echo $command->sql; die();

		$command->execute();
	}

}
