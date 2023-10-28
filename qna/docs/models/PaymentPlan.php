<?php

namespace common\models;

use common\components\payment\helpers\ArrayHelper;
use common\dictionaries\PaymentPeriod;
use common\dictionaries\PaymentVendor;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class PaymentPlan
 * @package common\models
 *
 * @property Company $company для приватных планов
 * @property CompanyPlan $companyPlan приватный план компании
 */
class PaymentPlan extends generated\PaymentPlan {
    const FREE_TEAM_SIZE = 3;

	/**
	 * @inheritDoc
	 */
	public function behaviors() {
		return [
			'timestamp' => [
				'class' => TimestampBehavior::class,
				'updatedAtAttribute' => false,
				'value' => new Expression('CURRENT_TIMESTAMP'),
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function rules() {
		return [
			[['vendor_id', 'period', 'team_size', 'vendor_plan_id', 'name'], 'required'],
			[['vendor_id'], 'string', 'max' => 31],
			[['vendor_id'], 'in', 'range' => PaymentVendor::list()],
			[['period'], 'string'],
			[['period'], 'in', 'range' => PaymentPeriod::list()],
			[
				['team_size'],
				'integer',
				'min' => CompanyPlan::PAYMENT_FREE_TEAM_SIZE + 1,
				'max' => CompanyPlan::PAYMENT_MAX_TEAM_SIZE,
			],
			[['vendor_plan_id'], 'filter', 'filter' => 'strval', 'when' => function (self $model) {
				return is_numeric($model->vendor_plan_id);
			}],
			[['vendor_plan_id'], 'string', 'max' => 511],
			[['name'], 'string', 'max' => 127],
			[['currency'], 'string', 'max' => 3],
			[['currency'], 'default', 'value' => 'USD'],
			[['amount'], 'number', 'min' => 0],
			[['amount'], 'default', 'value' => 0],
			[['company_id','archived'], 'integer'],
            [['archived_at'], 'safe'],
			[['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::class, ['id' => 'company_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyPlan() {
		return $this->hasOne(CompanyPlan::class, [
			'company_id' => 'company_id',
			'period' => 'period',
			'team_size' => 'team_size',
		]);
	}

	public static function getPaymentPlanByData($period = 'monthly', $team_size = 3, $company_id = null)
    {
        $query = static::find()
            ->andWhere(['period' => $period])
            ->andWhere(['team_size' => $team_size]);

        if (!is_null($company_id)) {
            $query->andWhere(['OR', ['IS', 'company_id', null], ['=', 'company_id', $company_id]]);
        }

        return $query->orderBy('company_id DESC')->one();
    }

    /**
     * Получение планов из БД в формате конфигурации
     * @param int|null $companyId ID компании, чьи приватные планы нужно включить в выборку публичных планов
     * @param string $vendorId ID платёжной системы
     * @return array [period => [teamSize => vendorPlanId, ...], ...]
     */
    public static function findAsConfig($companyId = null, $vendorId = PaymentVendor::PAYMENT_VENDOR_PADDLE) {
        $query = static::find()
            ->andFilterWhere([
                'vendor_id' => $vendorId,
            ])->addOrderBy([
                'period' => SORT_ASC,
                'team_size' => SORT_ASC,
            ]);

        if (is_null($companyId)) {
            $query->andWhere(['company_id' => null]);
        } else {
            $query
                ->andWhere(['or', ['company_id' => null], ['company_id' => $companyId]])
                ->addOrderBy(['company_id' => SORT_ASC]);
        }

        $models = $query->all();

        return ArrayHelper::map($models, 'team_size', 'vendor_plan_id', 'period');
    }
}
