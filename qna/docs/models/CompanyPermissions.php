<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_permissions".
 *
 * @property int $id
 * @property int $company_id
 * @property int $rates
 * @property int $time_adjustment
 * @property int $time_approve
 * @property int $overlimit_manage
 * @property int $overlimit_approve
 */
class CompanyPermissions extends \common\models\generated\CompanyPermissions {
	const SCENARIO_PERMISSIONS = 'permissions';

	const ROLES = [
		1 => 'owner',
		2 => 'admin',
		3 => 'manager',
		4 => 'user',
	];

	public $company_id;

	public function attributeLabels() {
		return [
			'id' => 'ID',
			'rates' => Yii::t('app', 'Who can view/manage pay rates and amounts earned by team members'),
			'time_adjustment' => 'Time Adjustment',
			'time_approve' => 'Time Approve',
			'overlimit_manage' => 'Overlimit Manage',
			'overlimit_approve' => 'Overlimit Approve',
		];
	}

	public function rules() {
		return [
			[['rates', 'time_adjustment', 'time_approve', 'overlimit_manage', 'overlimit_approve'], 'integer'],
			[['rates', 'time_adjustment', 'time_approve', 'overlimit_manage', 'overlimit_approve'], 'required'],
		];
	}
}
