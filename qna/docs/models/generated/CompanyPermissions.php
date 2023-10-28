<?php

namespace common\models\generated;

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
 *
 * @property Company[] $companies
 */
class CompanyPermissions extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'company_permissions';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['rates', 'time_adjustment', 'time_approve', 'overlimit_manage', 'overlimit_approve'], 'integer'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'rates' => 'Rates',
			'time_adjustment' => 'Time Adjustment',
			'time_approve' => 'Time Approve',
			'overlimit_manage' => 'Overlimit Manage',
			'overlimit_approve' => 'Overlimit Approve',
		];
	}

	/**
	 * Gets query for [[Companies]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanies() {
		return $this->hasMany(Company::className(), ['company_permissions_id' => 'id']);
	}
}
