<?php

namespace common\models;

/**
 * Class Payment
 * @package common\models
 *
 * @property Company $company
 */
class Payment extends generated\Payment {

	const STATUS_PENDING = 'pending';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_PAUSED = 'paused';
	const STATUS_SUCCESSED = 'successed';
	const DETAILS_UPGRADE = 'upgrade';
	const DETAILS_DOWNGRADE = 'downgrade';
	const DETAILS_REGULAR = 'regular';

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::class, ['id' => 'company_id']);
	}

	public function getPaymentPlan() {
		return $this->hasOne(PaymentPlan::class, ['id' => 'payment_plan_id']);
	}

	private $json;

	public function setPaused() {
		$this->status = self::STATUS_PAUSED;
		$this->save(false);
	}

	public function setPending() {
		$this->status = self::STATUS_PENDING;
		$this->save(false);
	}

	public function setCancelled() {
		$this->status = self::STATUS_CANCELLED;
		$this->save(false);
	}

	public function setSuccessed() {
		$this->status = self::STATUS_SUCCESSED;
		$this->save(false);
	}

	public function isDowngrade() {
		return ($this->details == self::DETAILS_DOWNGRADE) ? true : false;
	}

	public function isUpgrade() {
		return ($this->details == self::DETAILS_UPGRADE) ? true : false;
	}

	public function isRegular() {
		return ($this->details == self::DETAILS_REGULAR) ? true : false;
	}

	public function isSuccessed() {
		return ($this->status == self::STATUS_SUCCESSED) ? true : false;
	}

	public function isPending() {
		return ($this->status == self::STATUS_PENDING) ? true : false;
	}

	public function isPaused() {
		return ($this->status == self::STATUS_PAUSED) ? true : false;
	}

	public function isCancelled() {
		return ($this->status == self::STATUS_CANCELLED) ? true : false;
	}

	public function isInitial() {
		$json = $this->getJson();
		return (!empty($json['initial_payment']) && $json['initial_payment']) ? true : false;
	}

	/**
	 * Метод возвращает пользователей, которые отмечены в data.members
	 * В противном случае возвращает первые три пользователя
	 *
	 * @return [app\models\User]
	 */
	public function getDataMembers() {
		$query = $this->company->getUsers()->orderBy('id')->limit(3);
		if (!empty($this->getJson()['members'])) {
			if (count($this->getJson()['members']) > 0) {
				$query->andWhere(['IN', 'id', $this->getJson()['members']]);
			}
		}
		return $query->all();
	}

	public function setJson($data) {
		if ($data == 'null' || is_null($data)) {
			$this->data = null;
		} else {
			$this->data = json_encode($data);
		}
	}

	public function getJson() {
		return json_decode($this->data, true);
	}
}
