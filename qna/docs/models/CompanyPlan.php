<?php
/**
 * @author Aleksey I. Chelnokov <aleksey.chelnokov@auslogics.com>
 * @copyright Copyright (c) 2021 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use adm\forms\CompanyStatForm;
use common\components\payment\PaymentApiException;
use yii\web\HttpException;
use yii\base\ErrorException;
use Yii;
use common\jobs\FacebookEventsJob;
use common\components\time\Time;
use common\helpers\DateTimeHelper;

class CompanyPlan extends \common\models\generated\CompanyPlan {

	const PAYMENT_FREE_TEAM_SIZE = 3;
	const PAYMENT_FREE_TRIAL = 21; // дней триала
	const PAYMENT_MAX_TEAM_SIZE = 100;

	/**
	 * @var int старый триал в днях
	 * @see https://pm.auslogics.com/projects/67/tasks/14322
	 * "Реализовать 60 дней триала вместо 14ти для новых пользователей" (c)
	 */
	const PAYMENT_LEGACY_FREE_TRIAL_0 = 14;

	/**
	 * @var int следующий старый триал в днях
	 * @see https://pm.auslogics.com/projects/67/tasks/15653
	 * "заменить все на 21 день триала с 60" (c)
	 */
	const PAYMENT_LEGACY_FREE_TRIAL_1 = 60;

	const PLAN_NAME_FREE = 'Premium Starter';
	const PLAN_NAME_PREMIUM = 'Premium Teams';

    const LABEL_PAYMENT_FAILED = 'Automatic subscription renewal payment failed. Please double-check or update your payment info.';

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['company_id'], 'required'],
			[['period'], 'safe'],
			[['company_id', 'subscription_id'], 'integer'],
			[['team_size'], 'integer', 'min' => self::PAYMENT_FREE_TEAM_SIZE, 'max' => self::PAYMENT_MAX_TEAM_SIZE],
			[['valid_till', 'updated_at'], 'safe'],
			[['vendor_cancel_url'], 'string', 'max' => 255],
			[['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
			[['!trial_period'], 'default', 'value' => self::PAYMENT_FREE_TRIAL],
		];
	}

	public function getName() {
		return \Yii::t('app', ($this->team_size > self::PAYMENT_FREE_TEAM_SIZE) ? self::PLAN_NAME_PREMIUM : self::PLAN_NAME_FREE);
	}

	/**
	 * Количество дней, оставшееся до окончания подписки
	 * @return \DateInterval|false
	 * @throws \Exception
	 */
	public function getDaysLeft() {
		if ($this->team_size == self::PAYMENT_FREE_TEAM_SIZE) {
			return false;
		}

		if (!is_null($timezone = $this->company->timezone)) {
			// сдвиг по таймзоне компании
			$format = 'Y-m-d H:i:s';
			$adjustedDt = (new \DateTime('now', new \DateTimeZone($timezone)))->format($format);
			$companyDt = \DateTime::createFromFormat($format, $adjustedDt);
		} else {
			// по умолчанию - серверное время
			$companyDt = new \DateTime();
		}

		// возможность доработать до конца текущего календарного дня
		$ownerDt = $this->lastValidTill();

		return date_diff($companyDt, $ownerDt, false);
	}

	public function isExpired() {
		if ($daysLeft = $this->getDaysLeft()) {
			return ($daysLeft->invert) ? true : false;
		}
		return false;
	}

	/**
	 * Крайняя дата активности плана, дальше которой план считается истёкшим
	 * @param string|null $format форматирование даты в строку
	 * @return \DateTime|string дать доработать до 23:59:59 текущего календарного дня
	 * @throws \Exception
	 */
	public function lastValidTill($format = null) {
		// если "valid_till" не указан (NULL), то максимально отдалить крайний срок
		$validTill = is_null($this->valid_till)
		? '9999-12-31 23:59:59'
		: $this->valid_till;

		$dt = (new \DateTime($validTill))->setTime(23, 59, 59);

		return is_null($format) || !is_string($format)
		? $dt
		: $dt->format($format);
	}

	/**
	 * Проверка, находится ли указанная дата в пределах активного плана
	 * @param Time|\DateTime|string $value
	 * @return bool FALSE - для указанной даты план истёк, TRUE - активен
	 * @throws \Exception
	 */
	public function isDateTimeValid($value) {
		switch (true) {
		case $value instanceof \DateTime:
			$dt = $value;
			break;
		case $value instanceof Time:
			$dt = $value->timeFinish;
			break;
		case is_string($value):
			$dt = Time::createByString($value)->timeFinish;
			break;
		default:
			return false;
		}

		/**
		 * @var \DateTime $userDt datetime с точки зрения пользователя (может отличаться от серверного GMT)
		 *
		 * $dt содержит серверное представление пользовательского datetime,
		 * т.е. для "2021-06-21 01:02:03+03:00" на часах пользователя $dt будет содержать "2021-06-20 22:02:03".
		 *
		 * Поэтому нужно привести серверный datetime к пользовательскому представлению,
		 * чтобы с датой окончания подписки сравнивать datetime, который видит пользователь на своих часах.
		 */
		$userDt = (clone $dt)->modify($dt->getOffset() . ' seconds');

		return $userDt <= $this->lastValidTill();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments() {
		return $this->hasMany(Payment::className(), ['company_id' => 'company_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::className(), ['id' => 'company_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPaymentPlan() {
		return $this->hasOne(PaymentPlan::className(), ['id' => 'payment_plan_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPrivatePlans() {
		return $this->hasMany(PaymentPlan::class, [
			'company_id' => 'company_id',
			'period' => 'period',
			'team_size' => 'team_size',
		]);
	}

	/**
	 * Метод возвращает оплаты, которые были самыми первыми в цепочках оплат тарифных планов в рамках отдельных подписок
	 * @return \common\models\Payment
	 */
	public function getInitialPayments() {
		return $this->getPayments()
			->andWhere(['=', 'status', 'successed'])
			->andWhere(['=', 'details', 'regular'])
			->andWhere(['like', 'data', 'initial_payment'])
			->orderBy('created_at ASC')
			->all();
	}

    public function pauseWithPaymentData($paymentData = [])
    {
        if ($lastPayment = $this->getLastPayment('pending')) {
            $lastPayment->team_size = self::PAYMENT_FREE_TEAM_SIZE;
            $lastPayment->json = $paymentData;
            $lastPayment->setPaused();
        }
        return true;
    }

    public function pause(): bool
    {
        try {
            \Yii::$app->payment->run('/2.0/subscription/users/update', [
                'subscription_id' => $this->subscription_id,
                'pause' => true,
            ]);
            $companyStatForm = new CompanyStatForm([
                'company_id' => $this->company_id,
                'event' => 'subscription_pause',
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'data' => $this->getDataForStat(),
            ]);
            $companyStatForm->scenario = CompanyStatForm::SCENARIO_REGISTER;

            if ($companyStatForm->register()) {
                return true;
            }
            return false;
        } catch (HttpException | PaymentApiException $e) {
            return false;
        }
    }

	/**
	 * If don't have any success payment (never pay before)
	 * @return boolean
	 */
	public function isInitial() {
		$initialPayments = $this->getInitialPayments();
		return ($this->getPayments()->andWhere(['=', 'status', 'successed'])->count() > 0) ? false : true;
	}

	public function isTrial() {
		if (!$this->isInitial()) {
			return false;
		} else {
			return $this->team_size > self::PAYMENT_FREE_TEAM_SIZE;
		}
	}

	public function isPaused() {
		$lastRegularPayment = $this->getPayments()->andWhere(['=', 'details', 'regular'])->orderBy('id DESC')->one();
		if (!$lastRegularPayment) {
			return false;
		}
		return $lastRegularPayment->isPaused() && $this->valid_till != null;
	}

	public function isFree() {
		return ($this->team_size == self::PAYMENT_FREE_TEAM_SIZE) ? true : false;
	}

	/**
	 * Возвращает последнюю платежную транзакцию или NULL если план FREE
	 *
	 * @param string|array $status pending|cancelled|paused|successed
	 * @param string|array $details regular|downgrade|upgrade
	 * @return \common\models\Payment
	 */
	public function getLastPayment($status = 'successed', $details = ['regular', 'downgrade', 'upgrade']) {
		return $this->getPayments()
			->andWhere([(is_array($status) ? 'IN' : '='), 'status', $status])
			->andWhere([(is_array($details) ? 'IN' : '='), 'details', $details])
			->orderBy('created_at DESC')
			->limit(1)
			->one();
	}

	/**
	 * Метод возращает структуру данных для размещения в поле 'data' при регистрации событий
	 * @return array
	 */
	public function getDataForStat() {
		$data = [
			'company' => $this->company->getAttributes([
				'id',
				'name',
				'country_code',
				'timezone',
				'signup_at',
			]),
			'plan' => $this->getAttributes([
				'team_size',
				'period',
				'trial_used',
				'subscription_id',
				'payment_plan_id',
				'valid_till',
				'updated_at',
			]),
		];

		return json_encode($data);
	}

	public function isCancelled() {
		$lastRegularPayment = $this->getPayments()->andWhere(['=', 'details', 'regular'])->orderBy('id DESC')->one();
		if (!$lastRegularPayment) {
			return false;
		}
		return $lastRegularPayment->isCancelled() && $this->valid_till != null;
	}

	/**
	 * Создает запись о платеже для апгрейда плана или регулярном продлении
	 *
	 * @param array $data Attributes of Payment object:
	 * [R] team_size
	 * [R] period
	 * [O] details
	 * [O] amount
	 * @return \common\models\Payment
	 */
	public function createPayment($data = []) {
		if (empty($data['team_size']) || empty($data['period'])) {
			return false;
		}

		$payment = new Payment([
			'team_size' => $data['team_size'],
			'period' => $data['period'],
			'company_id' => $this->company->id,
			'payment_plan_id' => (!empty($data['payment_plan_id'])) ? $data['payment_plan_id'] : PaymentPlan::getPaymentPlanByData($data['period'], $data['team_size'], $this->company_id)->id,
			'currency' => 'USD',
			'earned' => 0.0,
			'status' => 'pending',
			'details' => ((!empty($data['details']) && $data['details']) ? $data['details'] : 'regular'),
		]);
		if (!empty($this->subscription_id)) {
			$payment->subscription_id = $this->subscription_id;
		}
		$payment->created_at = $payment->updated_at = (new \DateTime())->format('Y-m-d h:i:s');
		$payment->amount = ((!empty($data['amount'])) ? $data['amount'] : 0.00);
		$payment->amount_usd = $payment->amount;
		$payment->save(false);

		return $payment;
	}

	/**
	 * Вычисление количества календарных дней, остающихся
	 * от текущей даты компании (с учётом её тайм-зоны)
	 * до срока истечения подписки.
	 * @return int
	 * @throws \Exception
	 */
	public function calcDaysToExpiration() {
		$companyDt = DateTimeHelper::companyDatetimeForServer($this->company);
		$validTill = new \DateTime($this->valid_till);

		foreach ([$companyDt, $validTill] as $dt) {
			/** @var \DateTime $dt приведение дат к формату "YYYY-MM-DD 00:00:00" */
			$dt->setTime(0, 0);
		}

		// разница в днях = разница в секундах / 86400
		$diffInSeconds = $validTill->getTimestamp() - $companyDt->getTimestamp();

		return $diffInSeconds / 86400;
	}

	/**
	 * Количество дней, оставшееся до окончания подписки
	 * @return \DateInterval|false
	 * @throws \Exception
	 */
	public function getDaysLeftInterval() {
		if (intval($this->team_size) === self::PAYMENT_FREE_TEAM_SIZE) {
			return false;
		}

		$companyDt = DateTimeHelper::companyDatetimeForServer($this->company);

		// возможность доработать до конца текущего календарного дня
		$ownerDt = $this->lastValidTill();

		return date_diff($companyDt, $ownerDt, false);
	}

    /**
     * Метод возвращает данные о подписке по номеру плана
     * @param integer $planId Идентификатор вендора
     * @param integer $companyId Идентификатор компании, необходим для получения списка приватных планов
     * @return array             ['period','team_size']
     */
    public static function getOptionsByPlanId($planId, $companyId = null)
    {
        $data = [];
        $plans = Yii::$app->payment->getPlans($companyId);

        foreach ($plans as $period => $vendorPlans) {
            foreach ($vendorPlans as $team_size => $plan) {
                if ($plan == $planId) {
                    $data = [
                        'period' => $period,
                        'team_size' => (int)$team_size,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Выставляет все значения как по умолчанию для бесплатного плана
     * @return bool
     */
    public function reset(): bool
    {
        $this->team_size = self::PAYMENT_FREE_TEAM_SIZE;
        $this->subscription_id = null;
        $this->payment_plan_id = null;
        $this->vendor_update_url = '';
        $this->vendor_cancel_url = '';
        $this->valid_till = null;
        return $this->save(false);
    }

    
    /**
     * @return bool
     */
    public function trialStart($data)
    {
        if (!$this->trial_used) {
            $freeTrialDays = CompanyPlan::PAYMENT_FREE_TRIAL;
            $this->team_size = $data['team_size'];
			$this->period = $data['period'];
			$this->trial_used = true;
			$this->valid_till = (new \DateTime("+{$freeTrialDays} day"))->format('Y-m-d H:i:s');
            if ($this->save()) {
                \Yii::$app->queue->push(new FacebookEventsJob([
                    'event' => 'trialStart',
                    'sourceData' => $data
                ]));
                return true;
            }
        } else {
            throw new ErrorException('Trial already used');
        }
        return false;
    }
}
