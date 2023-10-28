<?php

namespace common\models;

use api2\components\behaviors\TimeIntervalDelete;
use api2\components\behaviors\TimeIntervalFind;
use api2\components\validators\ManualTimeIntersectValidator;
use common\components\time\Time;
use common\exceptions\ValidateException;

/**
 * This is the model class for table "time_adjustment".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $status
 * @property string|null $reason
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $processed_user_id
 * @property string|null $processed_reason
 * @property int|null $duration
 * @property string $user_time
 * @property string|null $utc_time
 *
 * @property User $user
 */
class TimeAdjustment extends \common\models\generated\TimeAdjustment {
	const SCENARIO_ADD = 'add';
	const SCENARIO_EDIT = 'edit';
	const SCENARIO_UPDATE = 'update';
	const SCENARIO_DELETE = 'delete';

    const STATUS_DECLINED_NAME = 'declined';
    const STATUS_PENDING_NAME = 'pending';
    const STATUS_APPROVED_NAME = 'approved';

	const STATUS = [
		0 => self::STATUS_DECLINED_NAME,
		1 => self::STATUS_PENDING_NAME,
		2 => self::STATUS_APPROVED_NAME,
	];

	const STATUS_DECLINED = 0;
	const STATUS_PENDING = 1;
	const STATUS_APPROVED = 2;

	const ERROR_CODE_OK = 0;
	const ERROR_CODE_INTERSECT = 1;
	const ERROR_CODE_INCORRECT = 2;

	public function behaviors() {
		return array_merge(parent::behaviors(), [
			'deleteInterval' => [
				'class' => TimeIntervalDelete::class,
			],
			'interval' => [
				'class' => TimeIntervalFind::class,
			],
		]);
	}

	public function rules() {
		return [
			[['processed_reason'], 'string', 'on' => self::SCENARIO_UPDATE],
			[['processed_user_id', 'status'], 'required', 'on' => self::SCENARIO_UPDATE],
			[['reason', 'duration', 'user_time', 'utc_time'], 'safe', 'on' => self::SCENARIO_EDIT],
			[['status'], function ($attr) {
				$this->status = array_keys(self::STATUS, $this->$attr)[0];
			}, 'on' => self::SCENARIO_UPDATE],
			['status', 'in', 'range' => [self::STATUS_APPROVED, self::STATUS_DECLINED], 'on' => self::SCENARIO_UPDATE],
			['status', 'in', 'range' => [self::STATUS_PENDING], 'on' => [self::SCENARIO_EDIT, self::SCENARIO_DELETE], 'message' => \Yii::t('yii', 'Request has been already processed.')],
			[['duration'], function ($attr) {
				$this->user_time = $this->$attr->time->format(Time::FORMAT_MYSQL);
				$this->utc_time = $this->$attr->toUtcTime()->time->format(Time::FORMAT_MYSQL);
				$this->duration = intval($this->$attr->interval->getSeconds());
			}, 'on' => self::SCENARIO_EDIT],
            ['duration', function($attr) {
                $endTime = strtotime($this->utc_time) + $this->$attr;

                if ($endTime > time()) {
                    $this->addError($attr, \Yii::t('app', 'You cannot select intervals in future.'));
                }
            }, 'on' => self::SCENARIO_ADD],
			['utc_time', ManualTimeIntersectValidator::class, 'on' => self::SCENARIO_ADD, 'where' => function ($exists, $model) {
				$exists->andWhere(['<>', 'status', self::STATUS_DECLINED]);
			}],
			['utc_time', ManualTimeIntersectValidator::class, 'on' => self::SCENARIO_EDIT, 'where' => function ($exists, $model) {
				$exists->andWhere(['<>', 'time_adjustment.id', $model->id])->andWhere(['<>', 'status', self::STATUS_DECLINED]);
			}],
		];
	}

	/**
	 * @param bool $insert
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function beforeSave($insert) {
		if (parent::beforeSave($insert)) {
			if (!$this->isNewRecord) {
				$this->updated_at = new \yii\db\Expression('NOW()');
			}
			return true;
		}
		return false;
	}

	public static function approve($timeAdjustment, $isError = true) {
		$resultAdd = UserActivityNew::addTimeAdjustmentActivity($timeAdjustment, $isError);
		
		switch ($resultAdd) {
		case self::ERROR_CODE_INTERSECT:
			$params = [
				'status' => TimeAdjustment::STATUS_DECLINED,
				'processed_user_id' => $timeAdjustment->user_id,
				'processed_reason' => 'Intersect time.',
			];
			break;
		case self::ERROR_CODE_INCORRECT:
			$params = [
				'status' => TimeAdjustment::STATUS_DECLINED,
				'processed_user_id' => $timeAdjustment->user_id,
				'processed_reason' => 'Incorrect duration.',
			];
			break;
		default:
			$params = [
				'status' => TimeAdjustment::STATUS_APPROVED,
				'processed_user_id' => $timeAdjustment->processed_user_id ? $timeAdjustment->processed_user_id : $timeAdjustment->user_id,
                'processed_reason' =>  $timeAdjustment->processed_reason,
			];
		}

		self::updateAll($params, ['id' => $timeAdjustment->id]);
	}

	public static function approveAll($user_id) {
		$timeAdjustments = self::find()->where(['user_id' => $user_id, 'status' => TimeAdjustment::STATUS_PENDING])->all();
		foreach ($timeAdjustments as $timeAdjustment) {
			self::approve($timeAdjustment, false);
		}
	}

	public static function approveAllByCompany($company) {
		$rolesArray = array_slice(User::ROLES, 0, $company->permissions->time_approve);
		$users = User::find()
			->where(['company_id' => $company->id])
			->andWhere(['IN', 'role', $rolesArray])
			->all();

		foreach ($users as $user) {
			self::approveAll($user->id);
		}
	}

	public function findInterval($userId,Time $time) {
        $timeStart = $time->toUtcTime()->time->format(Time::FORMAT_MYSQL);
        $timeFinish = $time->toUtcTime()->timeFinish->format(Time::FORMAT_MYSQL);

        return self::find()
            ->where(['OR',
                [
                    'AND',
                    ['>', 'UNIX_TIMESTAMP(`utc_time`)', strtotime($timeStart)],
                    ['<', 'UNIX_TIMESTAMP(`utc_time`)', strtotime($timeFinish)]

                ],
                [
                    'AND',
                    ['>', 'UNIX_TIMESTAMP(`utc_time`) + duration', strtotime($timeStart)],
                    ['<', 'UNIX_TIMESTAMP(`utc_time`) + duration', strtotime($timeFinish)]
                ],
                //Запрещаем добавление полностью одинаковых интервалов
                [
                    'AND',
                    ['=', 'UNIX_TIMESTAMP(`utc_time`)', strtotime($timeStart)],
                    ['=', 'UNIX_TIMESTAMP(`utc_time`) + duration', strtotime($timeFinish)]
                ]
            ])
            ->andWhere([
                'user_id' => $userId,
            ]);
    }

	public static function checkActivityIntersect($model, $isError) {
        $timeStartTS = strtotime($model->utc_time);
        $timeStart = date(Time::FORMAT_MYSQL, $timeStartTS);
		$timeFinishTS = $timeStartTS + $model->duration;
        $timeFinish = date(Time::FORMAT_MYSQL, $timeFinishTS);

		$intersect = UserActivityNew::checkIntersect($model->user_id, $timeStart, $timeFinish) 
				|| UserActivityOverlimit::checkIntersect($model->user_id, $timeStart, $timeFinish) 
				|| UserManualActivityNew::checkIntersect($model->user_id, $timeStart, $timeFinish, self:: STATUS_APPROVED);

		if ($intersect) {
			if ($isError) {
				$model->addError($model->duration, "Intersect time.");
				throw new ValidateException($model->getErrors());
			}
			return false;
		}

		return true;
    }

    protected static function checkActivityIntersectManual($model, $isError) {
        $timeStartTS = strtotime($model->utc_time);
        $timeStart = date(Time::FORMAT_MYSQL, $timeStartTS);
		//$timeMinTS = $timeStartTS - 3600;
        //$timeMin = date(Time::FORMAT_MYSQL, $timeMinTS);
		$timeFinishTS = $timeStartTS + $model->duration;
        $timeFinish = date(Time::FORMAT_MYSQL, $timeFinishTS);

		$exists = UserManualActivityNew::find()
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
				]
			])
			->andWhere(['user_id' => $model->user_id]);

		if ($exists->count() > 0) {
			if ($isError) {
				$model->addError($model->duration, "Intersect time.");
				throw new ValidateException($model->getErrors());
			}
			return false;
		}

		return true;
    }

}
