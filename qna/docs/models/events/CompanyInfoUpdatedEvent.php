<?php

namespace common\models\events;

use common\models\Company;
use common\models\User;
use yii\base\Event;
use yii\base\InvalidArgumentException;

/**
 * Class CompanyInfoUpdatedEvent
 * @package api3\models\events
 */
class CompanyInfoUpdatedEvent extends Event
{
    /** @var User */
    private $user_;

    /** @var Company */
    private $company_;

    /** @var Company */
    private $oldCompany_;

    /**
     * Разрешенные параметры для обновления
     *
     * @var array
     */
    private $changeFields_ = [];

    /**
     * @throws InvalidArgumentException
     */
    public function init()
    {
        if (!$this->user_) {
            throw new InvalidArgumentException('Param user is required');
        }

        if (!$this->company_) {
            throw new InvalidArgumentException('Param company is required');
        }

        if (!$this->oldCompany_) {
            throw new InvalidArgumentException('Param oldCompany is required');
        }
    }

    /**
     * @return void
     */
    public function updateCompanyIngoChangedSign(): void
    {
        if ($this->isFieldsChanged()) {
            $settingsChangedSign = $this->user_->settingsChangedSign;
            if ($settingsChangedSign) {
                $settingsChangedSign->company_info += 1;
                $settingsChangedSign->save();
            }
        }
    }
    
    /**
     * @return bool
     */
    private function isFieldsChanged(): bool
    {
        // Извлекаем только те поля, которые разрешены для обновления
        $oldData = array_intersect_key($this->oldCompany_->attributes, $this->changeFields_);
        $newData = $this->company_->attributes;
        foreach ($oldData as $attribute => $value) {
            if ($newData[$attribute] != $value ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user_ = $user;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company): void
    {
        $this->company_ = $company;
    }

    /**
     * @param Company $oldCompany
     */
    public function setOldCompany(Company $oldCompany): void
    {
        $this->oldCompany_ = $oldCompany;
    }

    /**
     * @param array $changeFields
     */
    public function setChangeFields(array $changeFields): void
    {
        $this->changeFields_ = $changeFields;
    }
}
