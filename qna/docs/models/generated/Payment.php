<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "payment".
 *
 * @property int $id
 * @property int $company_id
 * @property string $order_id
 * @property int $subscription_id
 * @property int $payment_plan_id
 * @property double $amount
 * @property string $currency
 * @property double $amount_usd
 * @property double $earned
 * @property string $created_at
 * @property string $updated_at
 * @property string $status
 * @property string $details
 * @property string $data
 * @property int $team_size
 * @property string $period
 *
 * @property Company $company
 */
class Payment extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'payment';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['company_id'], 'required'],
			[['company_id', 'subscription_id', 'payment_plan_id', 'team_size'], 'integer'],
			[['amount', 'amount_usd', 'earned'], 'number'],
			[['created_at', 'updated_at'], 'safe'],
			[['status', 'data', 'period'], 'string'],
            [['order_id'], 'string', 'max' => 128],
			[['currency'], 'string', 'max' => 3],
			[['details'], 'string', 'max' => 255],
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
            'order_id' => 'Order ID',
			'subscription_id' => 'Subscription ID',
			'payment_plan_id' => 'Payment Plan ID',
			'amount' => 'Amount',
			'currency' => 'Currency',
			'amount_usd' => 'Amount Usd',
			'earned' => 'Earned',
			'created_at' => 'Created At',
			'updated_at' => 'Updated At',
			'status' => 'Status',
			'details' => 'Details',
			'data' => 'Data',
			'team_size' => 'Team Size',
			'period' => 'Period',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::className(), ['id' => 'company_id']);
	}
}
