<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_plan_modifier".
 *
 * @property int $id
 * @property int $company_plan_id
 * @property int $subscription_id
 * @property int $modifier_id
 * @property int $modifier_recurring
 * @property string $modifier_amount
 * @property string $modifier_description
 * @property int $uses_total
 * @property int $uses_left
 * @property string $created_at
 *
 * @property CompanyPlan $companyPlan
 */
class CompanyPlanModifier extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'company_plan_modifier';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['company_plan_id', 'subscription_id', 'modifier_id', 'modifier_recurring', 'modifier_amount', 'created_at'], 'required'],
			[['company_plan_id', 'subscription_id', 'modifier_id', 'modifier_recurring', 'uses_total', 'uses_left'], 'integer'],
			[['modifier_amount'], 'number'],
			[['created_at'], 'safe'],
			[['modifier_description'], 'string', 'max' => 255],
			[['company_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyPlan::className(), 'targetAttribute' => ['company_plan_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'company_plan_id' => 'Company Plan ID',
			'subscription_id' => 'Subscription ID',
			'modifier_id' => 'Modifier ID',
			'modifier_recurring' => 'Modifier Recurring',
			'modifier_amount' => 'Modifier Amount',
			'modifier_description' => 'Modifier Description',
			'uses_total' => 'Uses Total',
			'uses_left' => 'Uses Left',
			'created_at' => 'Created At',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyPlan() {
		return $this->hasOne(CompanyPlan::className(), ['id' => 'company_plan_id']);
	}
}
