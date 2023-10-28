<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class CompanyCoupon
 * @package common\models
 *
 * @property Company $company
 */
class CompanyCoupon extends generated\CompanyCoupon
{
    public const RECURRING_TRUE = 1;
    public const RECURRING_FALSE = 0;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
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
    public function rules()
    {
        $rules = [
            [['type', 'allowed_uses'], 'compare', 'compareValue' => 0, 'operator' => '!=='],
        ];

        return array_merge(parent::rules(), $rules);
    }

    /**
     * @inheritDoc
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Фиксирование применённого компанией купона
     * @param int $companyId ID компании
     * @param string $code код купона
     * @return bool успешность сохранения изменений в БД
     */
    public static function applyCoupon($companyId, $code): bool
    {
        $coupon = self::findOne([
            'company_id' => $companyId,
            'code' => $code,
            'used_at' => null,
        ]);

        if (is_null($coupon)) {
            return false;
        }

        return 1 === $coupon->updateAttributes(['used_at' => new Expression('NOW()')]);
    }
}
