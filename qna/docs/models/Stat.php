<?php

namespace common\models;

/**
 * This is the model class for table "stat".
 *
 * @property int $id
 * @property string $event
 * @property string $date
 * @property int $count
 * @property double $amount
 */

class Stat extends \common\models\generated\Stat {

	const EVENT_COMPANY_NEW = 'company_new';
	const EVENT_COMPANY_DEACTIVATE = 'company_deactivate';
	const EVENT_COMPANY_DELETE = 'company_delete';
	const EVENT_COMPANY_RESTORE = 'company_restore';
	const EVENT_SUBSCRIPTION_NEW = 'subscription_new';
	const EVENT_SUBSCRIPTION_RENEWAL = 'subscription_renewal';
	const EVENT_SUBSCRIPTION_UPGRADE = 'subscription_upgrade';
	const EVENT_SUBSCRIPTION_REACTIVATION = 'subscription_reactivation';
	const EVENT_SUBSCRIPTION_DOWNGRADE = 'subscription_downgrade';
	const EVENT_SUBSCRIPTION_EXPIRED = 'subscription_expired';
	const EVENT_SUBSCRIPTION_PAUSE = 'subscription_pause';
	const EVENT_SUBSCRIPTION_CANCEL = 'subscription_cancel';
	const EVENT_PAYMENT_FAILED = 'payment_failed';
	const EVENT_PAYMENT_SUCCESSED = 'payment_successed';
	const EVENT_ACTIVE_COMPANIES_NEW = 'active_companies_new';
	const EVENT_ACTIVE_USERS_NEW = 'active_users_new';
	const EVENT_REG_COMP_2_USAGE = 'reg_comp_2_usage';
	const EVENT_REG_COMP_2_SUBSCRIPTION = 'reg_comp_2_subscription';
	const EVENT_USER_NEW = 'user_new';
	const EVENT_USER_ACTIVE = 'user_active';
	const EVENT_USER_ACTIVATE = 'user_activate';
	const EVENT_USER_ARCHIVE = 'user_archive';
	const EVENT_USER_DELETE = 'user_delete';
	const EVENT_USER_NOTE_CREATE = 'user_note_create';
	const EVENT_USER_NOTE_UPDATE = 'user_note_update';
	const EVENT_USER_NOTE_DELETE = 'user_note_delete';

	public static function getEventList() {
		return [
			self::EVENT_COMPANY_NEW,
			self::EVENT_COMPANY_DEACTIVATE,
			self::EVENT_COMPANY_DELETE,
			self::EVENT_COMPANY_RESTORE,
			self::EVENT_SUBSCRIPTION_NEW,
			self::EVENT_SUBSCRIPTION_RENEWAL,
			self::EVENT_SUBSCRIPTION_UPGRADE,
			self::EVENT_SUBSCRIPTION_REACTIVATION,
			self::EVENT_SUBSCRIPTION_DOWNGRADE,
			self::EVENT_SUBSCRIPTION_EXPIRED,
			self::EVENT_SUBSCRIPTION_PAUSE,
			self::EVENT_SUBSCRIPTION_CANCEL,
			self::EVENT_PAYMENT_FAILED,
			self::EVENT_PAYMENT_SUCCESSED,
			self::EVENT_ACTIVE_COMPANIES_NEW,
			self::EVENT_ACTIVE_USERS_NEW,
			self::EVENT_REG_COMP_2_USAGE,
			self::EVENT_REG_COMP_2_SUBSCRIPTION,
			self::EVENT_USER_NEW,
			self::EVENT_USER_ACTIVE,
			self::EVENT_USER_ACTIVATE,
			self::EVENT_USER_ARCHIVE,
			self::EVENT_USER_DELETE,
			self::EVENT_USER_NOTE_CREATE,
			self::EVENT_USER_NOTE_UPDATE,
			self::EVENT_USER_NOTE_DELETE,
		];
	}

}
