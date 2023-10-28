<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property string $street
 * @property string $city
 * @property string $state
 * @property string $postcode
 * @property string $country_code
 * @property int $start_week
 * @property string $timezone
 * @property int $limits
 * @property int $screenshots
 * @property int $blur
 * @property int $apps
 * @property int $invoicing
 * @property int $video
 * @property string $currency
 * @property string $restore_token
 * @property string $delete_at
 * @property string $signup_at
 * @property int $company_permissions_id
 * @property int $company_limits_id
 *
 * @property CompanyLimits $companyLimits
 * @property CompanyPermissions $companyPermissions
 * @property Country $countryCode
 * @property CompanyCoupon[] $companyCoupons
 * @property CompanyMeta[] $companyMetas
 * @property CompanyPlan[] $companyPlans
 * @property Group[] $groups
 * @property Payment[] $payments
 * @property PaymentPlan[] $paymentPlans
 * @property User[] $users
 */
class Company extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'company';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['name'], 'required'],
			[['start_week', 'limits', 'screenshots', 'blur', 'apps', 'invoicing', 'video', 'company_permissions_id', 'company_limits_id'], 'integer'],
			[['delete_at', 'signup_at'], 'safe'],
			[['name', 'timezone'], 'string', 'max' => 64],
			[['street'], 'string', 'max' => 255],
			[['city', 'state', 'restore_token'], 'string', 'max' => 128],
			[['postcode'], 'string', 'max' => 12],
			[['country_code'], 'string', 'max' => 2],
			[['currency'], 'string', 'max' => 3],
			[['company_limits_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyLimits::className(), 'targetAttribute' => ['company_limits_id' => 'id']],
			[['company_permissions_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyPermissions::className(), 'targetAttribute' => ['company_permissions_id' => 'id']],
			[['country_code'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_code' => 'code']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'name' => 'Name',
			'street' => 'Street',
			'city' => 'City',
			'state' => 'State',
			'postcode' => 'Postcode',
			'country_code' => 'Country Code',
			'start_week' => 'Start Week',
			'timezone' => 'Timezone',
			'limits' => 'Limits',
			'screenshots' => 'Screenshots',
			'blur' => 'Blur',
			'apps' => 'Apps',
			'invoicing' => 'Invoicing',
			'video' => 'Video',
			'currency' => 'Currency',
			'restore_token' => 'Restore Token',
			'delete_at' => 'Delete At',
			'signup_at' => 'Signup At',
			'company_permissions_id' => 'Company Permissions ID',
			'company_limits_id' => 'Company Limits ID',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyLimits() {
		return $this->hasOne(CompanyLimits::className(), ['id' => 'company_limits_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyPermissions() {
		return $this->hasOne(CompanyPermissions::className(), ['id' => 'company_permissions_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCountryCode() {
		return $this->hasOne(Country::className(), ['code' => 'country_code']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyCoupons() {
		return $this->hasMany(CompanyCoupon::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyMetas() {
		return $this->hasMany(CompanyMeta::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCompanyPlans() {
		return $this->hasMany(CompanyPlan::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getGroups() {
		return $this->hasMany(Group::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayments() {
		return $this->hasMany(Payment::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPaymentPlans() {
		return $this->hasMany(PaymentPlan::className(), ['company_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUsers() {
		return $this->hasMany(User::className(), ['company_id' => 'id']);
	}

}
