<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "payment_plan".
 *
 * @property int $id
 * @property string $vendor_id
 * @property string $period
 * @property int $team_size
 * @property string $vendor_plan_id ID в платёжной системе
 * @property string $name
 * @property string $currency
 * @property double $amount
 * @property string|null $created_at
 * @property int|null $company_id
 * @property int $archived
 * @property string $archived_at
 *
 * @property Company $company
 */
class PaymentPlan extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'payment_plan';
	}

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'period', 'team_size', 'vendor_plan_id', 'name'], 'required'],
            [['period'], 'string'],
            [['team_size', 'company_id', 'archived'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'archived_at'], 'safe'],
            [['vendor_id'], 'string', 'max' => 31],
            [['vendor_plan_id'], 'string', 'max' => 511],
            [['name'], 'string', 'max' => 127],
            [['currency'], 'string', 'max' => 3],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'period' => 'Period',
            'team_size' => 'Team Size',
            'vendor_plan_id' => 'Vendor Plan ID',
            'name' => 'Name',
            'currency' => 'Currency',
            'amount' => 'Amount',
            'created_at' => 'Created At',
            'company_id' => 'Company ID',
            'archived' => 'Archived',
            'archived_at' => 'Archived At',
        ];
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::class, ['id' => 'company_id']);
	}
}
