<?php

namespace common\models;

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
class CompanyPlanModifier extends \common\models\generated\CompanyPlanModifier {

}