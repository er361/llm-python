<?php
/**
 * @author Andrey A. Nechaev <andrey.nechaev@auslogics.com>
 * @copyright Copyright (c) 2019 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use api2\jobs\MediaDeleteByUser;
use api2\jobs\NotificationUserArchiveJob;
use api2\jobs\NotificationUserRestoreJob;
use api2\models\queries\UserQuery;
use api3\models\Token;
use common\components\helpers\TokenHelper;
use common\models\generated\Timezone;
use common\models\queries\UserTimezoneHistoryQuery;
use common\dictionaries\InterfaceNotification;
use common\dictionaries\NotificationDictionary;
use common\models\generated\NotificationOption;
use common\models\generated\User as GeneratedUser;
use common\models\generated\UserInvoicePreference;
use DateTime;
use DateTimeZone;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\StaleObjectException;
use yii\web\Application as WebApplication;

/**
 * Class User
 * @package common\models
 * @property Group[] $accessGroups
 * @property Company $company
 * @property Note[] $notes
 * @property array $notificationSettings настройки уведомлений
 * - ключ (string): кодовое имя уведомления
 * @see NotificationDictionary со списком допустимых значений
 * - значение: JSON-декодированное значение [user_notification_option->value]
 * @property array $userNotificationOptions
 * @property array $notificationOptions
 * @property UserToken $confirmationToken
 * @property SettingsChangedSign $settingsChangedSign
 * @property UserSettings $settings
 *
 * @property-read UserTimezoneHistory[] $timezoneHistory
 * @property-read UserToken $loginToken
 * @property-read UserToken $inviteToken
 * @property-read Timezone $userTimezone
 */
class User extends GeneratedUser {
	const SCENARIO_SIGNUP = 'signup';
	const SCENARIO_LOGIN = 'login';
	const SCENARIO_CONNECT = 'connect';

	const ROLE_OWNER = 'owner';
	const ROLE_ADMIN = 'admin';
	const ROLE_MANAGER = 'manager';
	const ROLE_USER = 'user';

	const STATUS_ACTIVE = 2;
	const STATUS_DELETED = 0;
	const STATUS_INACTIVE = 1;
	const STATUS_JOB_DEL_FROM_DB = -1;

	const STATUS_ACTIVE_LABEL = 'active';
	const STATUS_DELETED_LABEL = 'deleted';
	const STATUS_INACTIVE_LABEL = 'inactive';

	const STATUS = [
		self::STATUS_ACTIVE_LABEL => self::STATUS_ACTIVE,
		self::STATUS_DELETED_LABEL => self::STATUS_DELETED,
		self::STATUS_INACTIVE_LABEL => self::STATUS_INACTIVE
	];

	const PERMISSION_AUTO_APPROVE = 'time_approve';
	const PERMISSION_AUTO_APPROVE_OVERLIMIT = 'overlimit_approve';

	const ROLES = ['owner', 'admin', 'manager', 'user'];

    /** @var array настройки уведомлений */
	protected $_notificationSettings = null;

    public static function find(): UserQuery
    {
        return new UserQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'duration',
            'activity',
            'app_name',
            'online',
            'group_id',
            'amount_earned'
        ]);
    }

	/**
	 * @param bool $insert
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function beforeSave($insert) {
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->auth_key = Yii::$app->security->generateRandomString();
				$this->created_at = new Expression('NOW()');
				if (Yii::$app instanceof WebApplication) {
					$this->registration_ip = Yii::$app->request->userIP;
				}
			}
			return true;
		}
		return false;
	}

	public function afterDelete() {
		Yii::$app->queue->push(new MediaDeleteByUser(['user_id' => $this->id]));
		parent::afterDelete();
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes) {
		parent::afterSave($insert, $changedAttributes);
		if (!$insert && isset($changedAttributes['status'])) {
			// Сохранение события user_archive/user_activate + отправка уведомлений
			if (
				$this->status == self::STATUS_DELETED
				&& $changedAttributes['status'] == self::STATUS_ACTIVE
			) {
				Yii::$app->stat->user([
					'user_id' => $this->id,
					'event' => \common\models\Stat::EVENT_USER_ARCHIVE,
					'data' => UserStat::extractStatData($this),
				]);

				$initiatorId = Yii::$app->user->id;
				Yii::$app->queue->push(new NotificationUserArchiveJob([
					'user_id' => $this->id,
					'method' => $initiatorId == $this->id ? 'DeleteByUser' : 'DeleteByAdmin',
					'initiator' => $initiatorId
				]));
			} elseif (
				$this->status == self::STATUS_ACTIVE
				&& $changedAttributes['status'] == self::STATUS_DELETED
			) {
				Yii::$app->stat->user([
					'user_id' => $this->id,
					'event' => \common\models\Stat::EVENT_USER_ACTIVATE,
					'data' => UserStat::extractStatData($this),
				]);

				Yii::$app->queue->push(new NotificationUserRestoreJob([
					'user_id' => $this->id,
					'method' => 'RestoreByAdmin',
					'initiator' => Yii::$app->user->id
				]));
			}
		}

        if ($insert) {
            $userApiKey = new UserApiKey();
            $userApiKey->createApiKey($this->id);
        }
	}

	/**
	 * @return bool
	 */
	public function beforeDelete(): bool {
		if (parent::beforeDelete()) {
			Yii::$app->queue->push(new MediaDeleteByUser(['user_id' => $this->id]));
			Yii::$app->stat->user([
				'user_id' => $this->id,
				'event' => \common\models\Stat::EVENT_USER_DELETE,
				'data' => UserStat::extractStatData($this),
			]);
			return true;
		}
		return false;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 *
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword(string $password): bool
    {
        if (!$this->password) {
            return false;
        }
		return Yii::$app->getSecurity()->validatePassword($password, $this->password);
	}

	/**
	 * Gets query for [[Groups]].
	 *
	 * @return ActiveQuery
	 *
	 * @throws InvalidConfigException
	 */
	public function getGroups() {
		return $this
			->hasMany(Group::class, ['id' => 'group_id'])
			->viaTable('user_group', ['user_id' => 'id']);
	}

	/**
	 * Gets query for [[Groups]].
	 *
	 * @return ActiveQuery|null
	 *
	 * @throws InvalidConfigException
	 */
	public function getAccessGroups() {
		return $this
			->hasMany(Group::class, ['id' => 'group_id'])
			->viaTable('user_access', ['owner_id' => 'id']);
	}

	/**
	 * @return UserInvoicePreference|array|null
	 */
	public function getInvoicePreference() {
		$invoicePreference = UserInvoicePreference::find()->where(['user_id' => $this->id])->one();
		if (!$invoicePreference) {
			$invoicePreference = new UserInvoicePreference(['user_id' => $this->id]);
		}
		return $invoicePreference;
	}

	/**
	 * @return bool
	 */
	public function isAdmin(): bool {
		return ($this->role == self::ROLE_ADMIN);
	}

	/**
	 * @return bool
	 */
	public function isManager(): bool {
		return ($this->role == self::ROLE_MANAGER);
	}

	/**
	 * @return bool
	 */
	public function isOwner(): bool {
		return ($this->role == self::ROLE_OWNER);
	}

	/**
	 * @return bool
	 */
	public function isUser(): bool {
		return ($this->role == self::ROLE_USER);
	}

	/**
	 * Gets query for [[UserSettings]].
	 *
	 * @return ActiveQuery
	 */
	public function getSettings() {
		return $this->hasOne(UserSettings::class, ['user_id' => 'id']);
	}

	/**
	 * Gets query for [[Company]].
	 *
	 * @return ActiveQuery
	 */
	public function getCompany() {
		return $this->hasOne(Company::class, ['id' => 'company_id']);
	}

    public function getJoinToken(): ActiveQuery {
        return $this
            ->hasOne(UserToken::class, ['user_id' => 'id'])
            ->where(['type' => UserToken::TYPE_JOIN]);
    }

	/**
	 * @return ActiveQuery
	 */
	public function getExportToken() {
		return $this
			->hasOne(UserToken::class, ['user_id' => 'id'])
			->where(['type' => UserToken::TYPE_EXPORT]);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getInviteToken() {
		return $this
			->hasOne(UserToken::class, ['user_id' => 'id'])
			->where(['type' => UserToken::TYPE_INVITE]);
	}

    public function getEmailToken() {
        return $this
            ->hasOne(UserToken::class, ['user_id' => 'id'])
            ->where(['type' => UserToken::TYPE_EMAIL]);
    }


    /**
	 * @return ActiveQuery
	 */
	public function getLoginToken() {
		return $this
			->hasOne(UserToken::class, ['user_id' => 'id'])
			->where(['type' => UserToken::TYPE_LOGIN]);
	}

	public function createLoginToken() {
		$token = new UserToken([
			'user_id' => $this->id,
			'type' => UserToken::TYPE_LOGIN,
		]);
		$this->link('loginToken', $token);
		$token->save();
		$token->refresh();
	}

    public function getConfirmationToken(): ActiveQuery {
        return $this
            ->hasOne(UserToken::class, ['user_id' => 'id'])
            ->where(['type' => UserToken::TYPE_CONFIRMATION]);
    }

	/**
	 * @return ActiveQuery
	 */
	public function getNotes() {
		return $this->hasMany(Note::class, ['user_id' => 'id']);
	}

	public function createExportToken() {
		$token = new UserToken([
			'user_id' => $this->id,
			'type' => UserToken::TYPE_EXPORT,
		]);
		$this->link('exportToken', $token);
		$token->save();
		$token->refresh();
	}

	/**
	 * @return bool
	 */
	public function isCompanyPlanExpired(): bool {
		return !is_null($this->company) && $this->company->isPlanExpired();
	}

	/**
	 * @return bool|void
	 */
	public function isAutoApprove() {
		return $this->hasPermissions(self::PERMISSION_AUTO_APPROVE);
	}

    /**
     * @return bool|void
     */
    public function isAutoApproveOverlimit() {
        return $this->hasPermissions(self::PERMISSION_AUTO_APPROVE_OVERLIMIT);
    }

	/**
	 * @param $permission
	 *
	 * @return bool|void
	 */
	public function hasPermissions($permission) {
		switch ($permission) {
		case self::PERMISSION_AUTO_APPROVE:
			return in_array(
				$this->role,
				array_slice(User::ROLES, 0, $this->company->permissions->time_approve)
			);
		case self::PERMISSION_AUTO_APPROVE_OVERLIMIT:
			return in_array(
				$this->role,
				array_slice(User::ROLES, 0, $this->company->permissions->overlimit_approve)
			);
		}
	}

	/**
	 * @param $permission
	 * @param $role
	 *
	 * @return bool|void
	 */
	public function hasPermissionsByRole($permission, $role) {
		switch ($permission) {
		case self::PERMISSION_AUTO_APPROVE:
			return in_array(
				$role,
				array_slice(User::ROLES, 0, $this->company->permissions->time_approve)
			);
		case self::PERMISSION_AUTO_APPROVE_OVERLIMIT:
			return in_array(
				$role,
				array_slice(User::ROLES, 0, $this->company->permissions->overlimit_approve)
			);
		}
	}

	/**
	 * Текущая дата в таймзоне пользователя
	 * @param string|null $format
	 *
	 * @return DateTime|string
	 *
	 * @throws Exception
	 */
	public function getLocalCurrentDateTime( ? string $format = null) {
		$dt = new DateTime('now', new DateTimeZone($this->timezone));

		return is_null($format)
		? $dt
		: $dt->format($format);
	}

	/**
	 * @param array|null $andWhere
	 * @param mixed $additional
	 *
	 * @return array
	 *
	 * @throws \yii\db\Exception
	 */
	public function getTeam($andWhere = null, $additional = null) {
		$query = $this->company->getUsers();
		$query->andWhere(
			[
				'IN',
				'user.role',
				array_slice(self::ROLES, array_keys(self::ROLES, $this->role)[0] + 1),
			]
		);

		if ($this->isManager()) {
			$s = "
				SELECT DISTINCT ug.user_id FROM user_group AS ug
				LEFT JOIN user_access AS ua ON ua.group_id=ug.group_id
				WHERE ua.owner_id={$this->id}
			";
			$usersInManagerGroups = Yii::$app->db->createCommand($s)->queryColumn();
			if (empty($usersInManagerGroups)) {
				$usersInManagerGroups = [$this->id];
			}
			$query->andWhere(['IN', 'user.id', $usersInManagerGroups]);
		}

		if (!empty($andWhere)) {
			$query->andWhere($andWhere);
		}

		$query->orWhere(['=', 'user.id', $this->id]); // include self

		if (is_callable($additional)) {
			$additional($query);
		}
		return $query->all();
	}

	/**
	 * @return ActiveQuery
	 */
	public function getUserNotificationOptions() {
		return $this->hasMany(UserNotificationOption::class, ['user_id' => 'id']);
	}

	/**
	 * @return ActiveQuery
	 */
	public function getNotificationOptions() {
		return $this->hasMany(NotificationOption::class, ['id' => 'notification_option_id'])
			->via('userNotificationOptions');
	}

	/**
	 * Настройки уведомлений
	 * @param bool $all FALSE - настройки только конкретного пользователя, TRUE - все настройки включая дефолтные
	 * @param bool $forceRefill принудительное заполнение из БД
	 *
	 * @return array
	 */
	public function getNotificationSettings(bool $all = false, bool $forceRefill = false) : array{
		if ($forceRefill || is_null($this->_notificationSettings)) {
			// выборка значений всех параметров (если не существует для данного юзера, то берётся дефолтное значение)
			$query = NotificationOption::find()
				->alias($aliasMain = 'no')
				->andWhere([
					"$aliasMain.name" => NotificationDictionary::catalog(),
				]);

			if ($all) {
				// все настройки (конкретного юзера + унаследованные по умолчанию)
				$query
					->leftJoin(
						UserNotificationOption::tableName() . ' ' . ($aliasAux = 'uno'),
						"$aliasAux.notification_option_id = $aliasMain.id AND $aliasAux.user_id = :userId",
						[':userId' => $this->id]
					)
					->select([
						'value' => new Expression("COALESCE($aliasAux.value, $aliasMain.default_value)"),
					]);
			} else {
				// только те настройки, что явно заданы в БД для данного пользователя
				$query
					->innerJoinWith(
						[
							'userNotificationOptions ' . ($aliasAux = 'uno') => function (ActiveQuery $query) use ($aliasAux) {
								$query->andWhere(["$aliasAux.user_id" => $this->id]);
							},
						],
						false
					)
					->select([
						'value' => "$aliasAux.value",
					]);
			}

			$settings = $query
				->addSelect([
					'name' => "$aliasMain.name",
				])
				->indexBy('name')
				->column();

			// декодирование значений из JSON
			$this->_notificationSettings = array_map(
				function ($value) {
					return json_decode($value, true);
				},
				$settings
			);
		}
		return $this->_notificationSettings;
	}

	/**
	 * Значение указанной настройки уведомлений юзера
	 * @param string $optionName название настройки
	 *
	 * @return mixed|null
	 */
	public function getNotificationSettingsValue(string $optionName) {
		return $this->notificationSettings[$optionName] ?? null;
	}

	/**
	 * Значение настройки "Email me when my requests to add time are approved or declined" уведомлений юзера
	 * @return bool
	 */
	public function wantsTimeRequestsProcessedNotification(): bool {
		return false !== $this
			->getNotificationSettingsValue(
				InterfaceNotification::NOTIFICATION_TIME_REQUESTS_PROCESSED
			);
	}

	public static function getDateFormatByCountry($code = 'EU') {
		$standarts = [
			'AU' => 'Y-m-d',
			'CA' => 'Y-m-d',
			'US' => 'm/d/Y',
			'EU' => 'd.m.Y',
		];
		return (array_key_exists($code, $standarts) ? $standarts[$code] : $standarts['EU']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserStats() {
		return $this->hasMany(UserStat::class, ['user_id' => 'id']);
	}

    public function getGroupsFlat() {
        $groups = $this->getGroups()->orderBy('name ASC')->indexBy('id')->all();
        $groupsArr = [];
        foreach ($groups as $k => $v) {
            $groupsArr[$k] = $v->name;
        }
        return $groupsArr;
    }

    /**
     * Возвращает активного пользователя по id
     *
     * @param int $id
     * @return array|ActiveRecord|null
     */
    public static function findActiveById(int $id)
    {
        return self::find()
            ->activeById($id)
            ->one();
    }

    public function getTimezoneHistory(): UserTimezoneHistoryQuery
    {
        return $this->hasMany(UserTimezoneHistory::class, ['user_id' => 'id']);
    }

    /**
     * Найти юзера среди участников группы, относящейся к компании
     *
     * @param int $userId
     * @param int $ownerId
     * @param int $companyId
     * @return array|ActiveRecord|null
     */
    public static function getCompanyGroupMemberById(int $userId, int $ownerId, int $companyId)
    {
        return self::find()
            ->getGroups('INNER JOIN')
            ->innerJoin(
                UserAccess::tableName(),
                UserAccess::tableName() . '.group_id = ' . Group::tableName() . '.id'
            )
            ->activeById($userId)
            ->andWhere([Group::tableName() . '.company_id' => $companyId])
            ->andWhere(['owner_id' => $ownerId])
            ->one();
    }

    /**
     * @param string $email
     * @return array|ActiveRecord|null
     */
    public static function findUserByEmail(string $email)
    {
        return self::find()
            ->where(['email' => $email])
            ->one();
    }

    /**
     * @param string $token
     * @return User|null
     */
    public static function findUserByAccessToken(string $token): ?User
    {
        /** @var UserApiToken $tokenModel */
        $tokenModel = UserApiToken::find()
            ->byTokenJoinUser($token)
            ->one();

        if (!$tokenModel) {
            return null;
        }

        return static::findOne($tokenModel->user_id);
    }

    /**
     * Возвращает текущий токен
     *
     * @param string $accessToken - двухчастный токен
     * @return array|ActiveRecord|null
     */
    public function getCurrentToken(string $accessToken)
    {
        $authToken = TokenHelper::getAccessToken($accessToken);
        return Token::find()
            ->where([
                'and',
                ['user_id' => $this->id],
                ['auth_token' => $authToken]
            ])
            ->one();
    }

    /**
     * @param string $token
     * @return bool
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function logoutByAccessToken(string $token): bool
    {
        $token = $this->getCurrentToken($token);

        if (!$token) {
            return false;
        }

        return $token->delete() >= 0;
    }

    /**
     * @return ActiveQuery
     */
    public function getUserTimezone(): ActiveQuery
    {
        //TODO: предлагаю везде заменить на timezone_id
        return $this->hasOne(Timezone::class, ['timezone' => 'timezone']);
    }
}
