<?php
/**
 * @author Andrey N. Loshkarev <andrey.loshkarev@auslogics.com>
 * @copyright Copyright (c) 2022 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\components\time\Time;
use common\models\generated\UserAppGroupCache;
use Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class UserAppCacheAggregator
 * @package console\components
 */
class UserAppCacheAggregator extends ActiveRecord {
	/** @var string */
	public $userId;

	/** @var string  */
	public $dateStart;

	/** @var string  */
	public $dateFinish;

	/**
	 * @return array
	 */
	public function rules(): array {
		return [
			[['userId', 'dateStart', 'dateFinish'], 'string'],
			[
				['dateStart', 'dateFinish'],
				'default',
				'value' => date(Time::FORMAT_DATE, time())
			]
		];
	}

	/**
	 * @throws Exception
	 */
	public function run($controller): bool {
		$finish = $this->dateFinish
			? date(Time::FORMAT_DATE, strtotime('+1 day', strtotime($this->dateFinish)))
			: date(Time::FORMAT_DATE, strtotime('+1 day'));
		$date = $this->dateStart
			?: $this->getFirstDate();

		$controller->stdout($date . " - " . $finish . PHP_EOL);
		$controller->stdout("===============" . PHP_EOL);
		$day = strtotime($date);

		while ($date !== $finish) {
			$controller->stdout('date: ' . $date . PHP_EOL);
			$condition = $this->makeConditionForAppNew($date, $finish);
			$userAppDay = $this->findInUserAppNew($condition);
			$controller->stdout('time count: ' . count($userAppDay) . PHP_EOL);
			$controller->stdout("===============" . PHP_EOL);

			if ($userAppDay) {
				$appDate = $this->makeUserTimeOffset($userAppDay);
				$deleteCondition = $this->deleteCondition($appDate);
				$this->deleteInUserAppCache($deleteCondition);
				$this->deleteInUserAppGroupCache($deleteCondition);
				UserAppCache::aggregate($userAppDay);
			}
			$day += 86400;
			$date = date(Time::FORMAT_DATE, $day);
		}
		return true;
	}

	/**
	 * @return false|string
	 */
	private function getFirstDate() {
		$utcTimeConverted = new Expression(
			"CONVERT_TZ(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"),"
			. " TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i'))) as date"
		);
		$firstDateTime = UserAppNew::find()->select($utcTimeConverted)->asArray()->one()['date'];
		return date(Time::FORMAT_DATE, strtotime($firstDateTime));
	}

	/**
	 * @param string $date
	 * @param string $finish
	 * @return array
	 */
	private function makeConditionForAppNew(
		string $date,
		string $finish
	): array {
		$utcTimeConvert = new Expression(
			"CONVERT_TZ(`utc_time_15m`, '+00:00', CONCAT(IF(tz_offset >= 0, \"+\", \"\"),"
			. " TIME_FORMAT(SEC_TO_TIME(tz_offset*15*60), '%H:%i')))"
		);
		if ($date) {
			$compareDate = [
				'BETWEEN',
				$utcTimeConvert,
				$date . " 00:00:00",
				$date . " 23:59:59"
			];
		} else {
			$compareDate = [
				'<=',
				'utc_time_15m',
				$finish
			];
		}
		return [
			'AND',
			$compareDate,
			[
				'<',
				'utc_time_15m',
				date(Time::FORMAT_MYSQL, strtotime('-15 minutes', strtotime('now')))
			]
		];
	}

	/**
	 * @param array $condition
	 *
	 * @return array|null
	 */
	private function findInUserAppNew(array $condition): ?array {
		return UserAppNew::find()
			->where($condition)
			->all();
	}

	/**
	 * @param array $userApps
	 *
	 * @return array
	 */
	private function makeUserTimeOffset(array $userApps): array {
		$appDate = [];
		foreach ($userApps as $userApp) {
			$appDate['dateStart'] = date(
				Time::FORMAT_DATE,
				strtotime($userApp->utc_time_15m)  + $userApp->tz_offset * 900);
			$appDate['dateFinish'] = date(
				Time::FORMAT_DATE,
				strtotime($userApp->utc_time_15m) + $userApp->tz_offset * 900);
		}
		return $appDate;
	}

	/**
	 * @param array $appDate
	 *
	 * @return array
	 */
	private function deleteCondition(array $appDate): array {
		return ['BETWEEN', 'user_date', $appDate['dateStart'], $appDate['dateFinish'] . " 23:59:59"];
	}

	/**
	 * @param array $deleteCondition
	 */
	private function deleteInUserAppCache(array $deleteCondition): void {
		UserAppCache::deleteAll($deleteCondition);
	}

	/**
	 * @param array $deleteCondition
	 */
	private function deleteInUserAppGroupCache(array $deleteCondition): void {
		UserAppGroupCache::deleteAll($deleteCondition);
	}

}