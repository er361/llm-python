<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_coupon_payment_plan".
 *
 * @property int $company_coupon_id
 * @property int $payment_plan_id
 *
 * @property CompanyCoupon $companyCoupon
 * @property PaymentPlan $paymentPlan
 */
class CompanyCouponPaymentPlan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_coupon_payment_plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_coupon_id', 'payment_plan_id'], 'required'],
            [['company_coupon_id', 'payment_plan_id'], 'integer'],
            [['company_coupon_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyCoupon::className(), 'targetAttribute' => ['company_coupon_id' => 'id']],
            [['payment_plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentPlan::className(), 'targetAttribute' => ['payment_plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'company_coupon_id' => Yii::t('app', 'Company Coupon ID'),
            'payment_plan_id' => Yii::t('app', 'Payment Plan ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCoupon()
    {
        return $this->hasOne(CompanyCoupon::className(), ['id' => 'company_coupon_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentPlan()
    {
        return $this->hasOne(PaymentPlan::className(), ['id' => 'payment_plan_id']);
    }
}

