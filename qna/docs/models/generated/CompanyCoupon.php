<?php

namespace common\models\generated;

use common\models\Company;
use Yii;

/**
 * This is the model class for table "company_coupon".
 *
 * @property int $id
 * @property int $company_id
 * @property string $type
 * @property string $code
 * @property double $discount_amount
 * @property int $allowed_uses
 * @property string $description
 * @property string $expires
 * @property int $recurring
 * @property string $created_at
 * @property string $sent_at
 * @property string $used_at
 *
 * @property Company $company
 * @property CompanyCouponPaymentPlan[] $companyCouponPaymentPlans
 */
class CompanyCoupon extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_coupon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'type', 'code'], 'required'],
            [['company_id', 'absolute', 'allowed_uses', 'recurring'], 'integer'],
            [['discount_amount', 'absolute_discount_amount'], 'number'],
            [['expires', 'created_at', 'sent_at', 'used_at'], 'safe'],
            [['type'], 'string', 'max' => 31],
            [['code'], 'string', 'max' => 511],
            [['description'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'type' => Yii::t('app', 'Type'),
            'code' => Yii::t('app', 'Code'),
            'discount_amount' => Yii::t('app', 'Discount Amount'),
            'absolute' => Yii::t('app', 'Absolute'),
            'absolute_discount_amount' => Yii::t('app', 'Absolute Discount Amount'),
            'allowed_uses' => Yii::t('app', 'Allowed Uses'),
            'description' => Yii::t('app', 'Description'),
            'expires' => Yii::t('app', 'Expires'),
            'recurring' => Yii::t('app', 'Recurring'),
            'created_at' => Yii::t('app', 'Created At'),
            'sent_at' => Yii::t('app', 'Sent At'),
            'used_at' => Yii::t('app', 'Used At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCouponPaymentPlans()
    {
        return $this->hasMany(CompanyCouponPaymentPlan::className(), ['company_coupon_id' => 'id']);
    }
}

