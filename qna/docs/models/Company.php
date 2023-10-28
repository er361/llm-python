<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use api2\jobs\CompanyDelete;
use api2\models\Token;
use common\components\CompanyStatEvents;
use common\components\payment\vendors\paddle\PaddleException;
use common\components\time\Time;
use common\exceptions\ValidateException;
use common\models\generated\CompanyMeta;
use common\models\generated\Timezone;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class Company
 *
 * @inheritdoc
 *
 * @property boolean $isActive
 * @property boolean $isDeleted
 * @property int $rates
 * @property string $restoreLink @readonly
 *
 * @property CompanyPlan $plan
 * @property CompanyCoupon[] $coupons
 * @property User[] $users
 * @property User $firstOwner
 * @property PaymentPlan[] $privatePlans
 * @property Payment[] $payments
 * @property CompanyUtm[] $utms
 * @property CompanyUtm[] $lastUtms
 * @property CompanySettings $settings
 * @property CompanyEntrypoint $entrypoint
 * @property Timezone $companyTimezone
 * @property CompanyPermissions $permissions
 *
 * @property int $currentCompanyWeekDay номер текущего дня недели компании
 * @property bool $isCurrentlyStartOfWeek сейчас в компании начало недели?
 *
 * @package common\models
 */
class Company extends \common\models\generated\Company
{

    const SAVE_TIME = '+7 days';

    const EVENT_AFTER_FAKEDELETE = 'afterFakeDelete';
    const EVENT_AFTER_RESTORE = 'afterRestore';

    /**
     * Событие изменения настроек
     */
    const EVENT_SETTINGS_CHANGE = 'settings_change';

    /**
     * Событие изменения информации о компании
     */
    public const EVENT_COMPANY_INFO_UPDATED = 'company_info_updated';

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_FAKEDELETE, function ($e) {
            \Yii::info($this->getNotificationData(), __CLASS__ . '::fakeDelete');
        });
        $this->on(self::EVENT_AFTER_RESTORE, function ($e) {
            \Yii::info($this->getNotificationData(), __CLASS__ . '::restore');
        });
        $this->on(self::EVENT_BEFORE_DELETE, function ($e) {
            \Yii::info($this->getNotificationData(), __CLASS__ . '::realDelete');
        });

        $statEvents = new CompanyStatEvents([
            'company' => $this,
            'events' => [
                self::EVENT_AFTER_FAKEDELETE,
                self::EVENT_AFTER_RESTORE,
                self::EVENT_BEFORE_DELETE,
                self::EVENT_AFTER_INSERT
            ],
        ]);

        $this->on(self::EVENT_BEFORE_DELETE, function ($e) {
            //запись в статистику компании
            \Yii::$app->stat->company([
                'company_id' => $this->id,
                'event' => \common\models\Stat::EVENT_COMPANY_DELETE,
                'data' => CompanyStat::extractStatData($this),
            ]);
            foreach ($this->users as $user) {
                $user->delete();
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [
                [
                    'start_week',
                    'limits',
                    'screenshots',
                    'blur',
                    'apps',
                    'invoicing',
                    'video',
                    'rates',
                    'company_permissions_id',
                    'company_limits_id'
                ],
                'integer'
            ],
            ['name',
                'match',
                'pattern' => '/^[A-Za-z\s\d_\-\&\!\?\.,\(\)]*$/i',
                'message' => 'The \'{attribute}\' may only contain English letters, numbers and punctuation marks.'
            ],
            [['delete_at', 'signup_at'], 'safe'],
            [['name', 'timezone'], 'string', 'max' => 64],
            [['street'], 'string', 'max' => 255],
            [['city', 'state', 'restore_token'], 'string', 'max' => 128],
            [['postcode'], 'string', 'max' => 12],
            [['country_code'], 'string', 'max' => 2],
            [['currency'], 'string', 'max' => 3],
            [
                ['company_permissions_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CompanyPermissions::className(),
                'targetAttribute' => ['company_permissions_id' => 'id']
            ],
            [
                ['country_code'],
                'exist',
                'skipOnError' => true,
                'targetClass' => \common\models\generated\Country::className(),
                'targetAttribute' => ['country_code' => 'code']
            ],
            [
                ['company_limits_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CompanyLimits::className(),
                'targetAttribute' => ['company_limits_id' => 'id']
            ],
        ];
    }

    private function getNotificationData()
    {
        /** @var User $owner */
        $owner = $this->getUsers()->where(['role' => 'owner'])->one();

        return [
            'Company ID' => $this->id,
            'Company name' => $this->name,
            'Company GEO' => $this->country_code,
            'Users in traqq' => $this->getUsers()->count(),
            'owner email and name' => $owner ? "{$owner->email} {$owner->name}" : 'NULL',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveUsers() {
        return $this->hasMany(User::className(), ['company_id' => 'id'])->where(['user.status' => User::STATUS_ACTIVE]);
    }

    public function getOwners()
    {
        return $this->hasMany(User::className(), ['company_id' => 'id'])->where(['role' => 'owner', 'status' => 2]);
    }

    public function getFirstOwner()
    {
        return $this->getOwners()
            ->orderBy(['id' => SORT_ASC])
            ->limit(1)
            ->one();
    }

    /**
     * Компания активна, когда не помечена к удалению
     * @return bool
     */
    public function getIsActive()
    {
        return is_null($this->delete_at) && is_null($this->restore_token);
    }

    /**
     * Возвращает true, если есть `restore_token`
     * @return bool
     */
    public function getIsDeleted()
    {
        return boolval($this->restore_token);
    }

    /**
     * Возвращает true, если есть `restore_token` и прошёл её save-срок
     * @return bool
     */
    public function canRealDelete()
    {
        return boolval($this->restore_token) && (new \DateTime() >= new \DateTime($this->delete_at));
    }

    public function getRestoreLink()
    {
        return \Yii::$app->params['webapp'] . '/company/restore/?code=' . $this->restore_token;
    }

    /**
     * Отмечает компанию к удалению, и ставит задачу на реальное удаление в очередь
     *
     * @param string|\DateTime $time Время, в которое нужно удалить
     * @param int|null $deleterId
     */
    public function deleteAt($time = self::SAVE_TIME, int $deleterId = null)
    {
        $time = is_string($time) ? new \DateTime($time) : $time;
        $deleteLag = 20; // откладываем реальное удаление на 20 секунд
        $delayTime = intval($time->format('U') - (new \DateTime())->format('U'));
        $delayTime = $delayTime > 0 ? ($delayTime + $deleteLag) : 0;

        $this->delete_at = $time->format(Time::FORMAT_MYSQL);
        $this->restore_token = $this->createRestoreToken();

        // удаляем API-токены пользователей
        $this->deleteUserTokens();

        \Yii::$app->queue->delay($delayTime)->push(new CompanyDelete([
            'id' => $this->id,
        ]));
        $this->save();
        \Yii::$app->stat->company([
            'company_id' => $this->id,
            'event' => \common\models\Stat::EVENT_COMPANY_DEACTIVATE,
            'data' => CompanyStat::extractStatData($this),
        ]);

        // удаляем временную статистику
        $users = UserStat::find()
            ->select('user_id')
            ->where(new Expression("data LIKE '%company_id\":" . $this->id . '%\''))
            ->andWhere(new Expression('user_id IS NOT NULL'))
            ->distinct()
            ->asArray()
            ->column();

        CompanyStat::deleteAll(['company_id' => $this->id]);
        UserStat::deleteAll(['user_id' => $users]);

        $this->trigger(self::EVENT_AFTER_FAKEDELETE);
    }

    /**
     * Мгновенное удаление компании со всеми токенами её пользователей
     * @return bool успешность удаления
     */
    public function deleteNow()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // удаление токенов пользователей
            $this->deleteUserTokens();

            // удаление компании
            if (!$this->delete()) {
                throw new ValidateException($this->getErrors());
            }

            if ($this->plan && $this->plan->subscription_id && !\common\components\payment\Payment::appPaddle()
                    ->run('2.0/subscription/users_cancel', [
                        'subscription_id' => $this->plan->subscription_id
                    ])) {
                throw new PaddleException("Error cancel subscription {$this->plan->subscription_id}");
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function restore()
    {
        $this->delete_at = null;
        $this->restore_token = null;
        $this->save();
        \Yii::$app->stat->company([
            'company_id' => $this->id,
            'event' => \common\models\Stat::EVENT_COMPANY_RESTORE,
            'data' => CompanyStat::extractStatData($this),
        ]);
        $this->trigger(self::EVENT_AFTER_RESTORE);
    }

    private function createRestoreToken()
    {
        return md5(microtime());
    }

    /**
     * Удаление API-токенов пользователей
     */
    private function deleteUserTokens()
    {
        $userIds = $this->getUsers()->select('id')->column();
        Token::deleteAll(['user_id' => $userIds]);
        UserAppToken::deleteAll(['user_id' => $userIds]);
        UserToken::deleteAll(['user_id' => $userIds]);
    }

    public function getPlan()
    {
        return $this->hasOne(CompanyPlan::className(), ['company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::className(), ['company_id' => 'id']);
    }

    public function isPlanExpired()
    {
        return !is_null($this->plan) && $this->plan->isExpired();
    }

    /**
     * @inheritDoc
     */
    public function getCoupons()
    {
        return $this->hasMany(CompanyCoupon::class, ['company_id' => 'id']);
    }

    /**
     * @inheritDoc
     */
    public function getUtms()
    {
        return $this->hasMany(CompanyUtm::class, ['company_id' => 'id']);
    }

    public function getLastUtms()
    {
        return $this->hasMany(CompanyUtm::class, ['company_id' => 'id'])
            ->where(['utm_created_at' => new \yii\db\Expression('(SELECT MAX(u2.`utm_created_at`) FROM ' . CompanyUtm::tableName() . ' u2 WHERE u2.`company_id` = ' . CompanyUtm::tableName() . '.`company_id`)')])->orderBy([CompanyUtm::tableName() . '.id' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrivatePlans()
    {
        return $this->hasMany(PaymentPlan::class, ['company_id' => 'id']);
    }
    public function getEntrypoint() {
        return $this->hasOne(CompanyEntrypoint::class, ['company_id' => 'id']);
    }

    public function getPermissions()
    {
        return $this->hasOne(CompanyPermissions::class, ['id' => 'company_permissions_id']);
    }

	public function getSettings()
	{
		return $this->hasOne(CompanySettings::class, ['company_id' => 'id']);
	}

	public function getRates() {
		return $this->permissions->rates;
	}

    public function getLimits()
    {
        return $this->hasOne(CompanyLimits::class, ['id' => 'company_limits_id']);
    }

    public function getTime_Adjustment()
    {
        return $this->permissions->time_adjustment;
    }

    public function getTime_Approve()
    {
        return $this->permissions->time_approve;
    }

    public function setRates($value = 1)
    {
        $permissions = CompanyPermissions::findOne($this->company_permissions_id);
        $permissions->rates = $value;
        $permissions->save();
    }

    public function setTime_Adjustment($value = 1)
    {
        $permissions = CompanyPermissions::findOne($this->company_permissions_id);
        $permissions->time_adjustment = $value;
        $permissions->save();
    }

    public function setTime_Approve($value = 1)
    {
        $permissions = CompanyPermissions::findOne($this->company_permissions_id);
        $permissions->time_approve = $value;
        $permissions->save();
    }

    /**
     * Текущая дата компании с учётов её таймзоны
     * @param string|null $format
     * @return \DateTime|string
     * @throws \Exception
     */
    public function getCurrentCompanyDate($format = null)
    {
        $date = new \DateTime('now', new \DateTimeZone($this->timezone ?? 'UTC'));

        return is_null($format)
            ? $date
            : $date->format($format);
    }

    /**
     * Номер текущего дня недели компании
     * @return int от 0 (воскресенье) до 6 (суббота)
     * @throws \Exception
     */
    public function getCurrentCompanyWeekDay()
    {
        return intval($this->getCurrentCompanyDate('w'));
    }

    /**
     * Сейчас в компании начало недели?
     * @return bool
     */
    public function getIsCurrentlyStartOfWeek()
    {
        return (intval($this->start_week) % 7) === $this->currentCompanyWeekDay;
    }

    /**
     * Проверяет, является ли переданная дата первым днем в неделе по таймзоне компании
     * @param \DateTime $date
     * @return bool
     */
    public function isStartOfWeek($date = false)
    {
        if ($date === false) {
            $date = new \DateTime("now", new \DateTimeZone($this->timezone ?? 'UTC'));
        }
        return (intval($this->start_week) % 7) === intval($date->format('w'));
    }

    /**
     * Устанавливает лимиты по умолчанию
     *
     * @param integer $id
     * @return void
     */
    public static function setDefaultLimits($id)
    {
        $limits = new CompanyLimits();
        $limits->setAttributes(CompanyLimits::DEFAULT_VALUES);
        $company = self::findOne($id);

        //если лимиты уже есть, не трогаем их
        if ($company->limits) {
            return false;
        }

        if (!$limits->save()) {
            return false;
        } else {
            $company->company_limits_id = $limits->id;
            if (!$company->save()) {
                return false;
            }
        }

        return $limits;
    }

    public static function getCurrencySymbols()
    {
        return [
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CNH' => '¥',
            'CZK' => 'Kč',
            'EUR' => '€',
            'JMD' => 'J$',
            'JPY' => '¥',
            'INR' => '₹',
            'IDR' => 'Rp',
            'NZD' => 'NZ$',
            'PHP' => '₱',
            'GBP' => '£',
            'RUB' => '₽',
            'SEK' => 'kr',
            'CHF' => '₣',
            'ZAR' => 'R',
            'USD' => '$',
        ];
    }

    public function setCurrency($currency = 'USD')
    {
        $this->currency = (array_key_exists($currency, self::getCurrencySymbols())) ? $currency : 'USD';
    }

    public function createNewPermissions($rates = 1, $timeApprove = 1, $timeAdjustment = 1)
    {
        $permissions = new CompanyPermissions();
        $permissions->rates = $rates;
        $permissions->time_approve = $timeApprove;
        $permissions->time_adjustment = $timeAdjustment;
        $permissions->save(false);
        $this->company_permissions_id = $permissions->id;

        return $permissions;
    }

    /**
     * Деактивирована ли компания
     * @return bool
     */
    public function isDeactivated()
    {
        return !is_null($this->restore_token) || !is_null($this->delete_at);
    }

    public function getCurrencySymbol()
    {
        $symbols = self::getCurrencySymbols();
        return array_key_exists($this->currency, $symbols) ? $symbols[$this->currency] : '';
    }

    public function createRandom()
    {
        $this->street = '8611 Fieldstone Drive';
        $this->city = 'Woburn';
        $this->state = 'Massachusetts';
        $this->postcode = '01801';
        $this->country_code = 'US';
        $this->start_week = '1';
        $this->timezone = 'America/Winnipeg';
        $this->screenshots = '1';
        $this->blur = '0';
        $this->apps = '1';
    }

    public function isConfirmed(): bool
    {
        return $this->firstOwner->confirmationToken === null;
    }

    public function getMeta(): ActiveQuery
    {
        return $this->hasMany(CompanyMeta::class, ['company_id' => 'id']);
    }

    public function setMeta($key = null, $value = null)
    {
        if (is_null($key) || is_null($value)) {
            return false;
        }

        $meta = $this->getMeta()->where(['like', 'meta_key', $key])->all();

        if (!$meta) {
            $meta = new CompanyMeta();
            $meta->company_id = $this->id;
            $meta->meta_key = $key;
            $meta->meta_value = $value;
            $meta->updated = (new \DateTime())->format('Y-m-d H:i:s');
            $meta->save(false);
        } else {
            foreach ($meta as $m) {
                $m->meta_value = $value;
                $m->save(false);
            }
        }

        return $meta;
    }

    public function getCompanyTimezone(): ActiveQuery
    {
        return $this->hasOne(Timezone::class, ['timezone' => 'timezone']);
    }
}
