<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_plan".
 *
 * @property int $id
 * @property int $company_id
 * @property int $team_size
 * @property string $period
 * @property double $price_monthly
 * @property double $price_annual
 * @property int $trial_period длительность триала в днях
 * @property int $trial_used
 * @property int $subscription_id
 * @property int $payment_plan_id
 * @property string $valid_till
 * @property string $vendor_update_url
 * @property string $vendor_cancel_url
 * @property string $updated_at
 *
 * @property Company $company
 */
class CompanyPlan extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'company_plan';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['company_id'], 'required'],
			[['company_id', 'team_size', 'trial_period', 'trial_used', 'subscription_id', 'payment_plan_id'], 'integer'],
			[['period'], 'string'],
			[['price_monthly', 'price_annual'], 'number'],
			[['valid_till', 'updated_at'], 'safe'],
			[['vendor_update_url', 'vendor_cancel_url'], 'string', 'max' => 255],
			[['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'company_id' => 'Company ID',
			'team_size' => 'Team Size',
			'period' => 'Period',
			'price_monthly' => 'Price Monthly',
			'price_annual' => 'Price Annual',
			'trial_period' => 'Trial Period',
			'trial_used' => 'Trial Used',
			'subscription_id' => 'Subscription ID',
			'payment_plan_id' => 'Payment Plan ID',
			'valid_till' => 'Valid Till',
			'vendor_update_url' => 'Vendor Update Url',
			'vendor_cancel_url' => 'Vendor Cancel Url',
			'updated_at' => 'Updated At',
		];
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
	public function getCompanyPlanModifiers() {
		return $this->hasMany(CompanyPlanModifier::className(), ['company_plan_id' => 'id']);
	}
}
