<?php

namespace common\models\events;

use common\models\User;
use yii\base\Event;
use yii\base\InvalidArgumentException;

/**
 * Class UserInfoUpdateEvent
 * @package api3\models\events
 */
class UserInfoUpdatedEvent extends Event
{
    /** @var User */
    private $user_;

    /** @var User */
    private $oldUser_;

    /**
     * Разрешенные параметры для обновления
     *
     * @var array
     */
    private $changeFields_;

    /**
     * @throws InvalidArgumentException
     */
    public function init()
    {
        if (!$this->user_) {
            throw new InvalidArgumentException('Param user is required');
        }

        if (!$this->oldUser_) {
            throw new InvalidArgumentException('Param oldUser is required');
        }

        if (!$this->changeFields_) {
            throw new InvalidArgumentException('Param changeFields is required');
        }
    }

    /**
     * @return void
     */
    public function updateUserIngoChangedSign(): void
    {
        if ($this->isFieldsChanged()) {
            $settingsChangedSign = $this->user_->settingsChangedSign;
            if ($settingsChangedSign) {
                $settingsChangedSign->user_info += 1;
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
        $oldData = array_intersect_key($this->oldUser_->getAttributes(), $this->changeFields_);
        $newData = $this->user_->attributes;
        foreach ($oldData as $attribute => $value) {
            if ($newData[$attribute] != $value ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $oldUser
     */
    public function setOldUser(User $oldUser): void
    {
        $this->oldUser_ = $oldUser;
    }

    /**
     * @param array $changeFields
     */
    public function setChangeFields(array $changeFields): void
    {
        $this->changeFields_ = $changeFields;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user_ = $user;
    }
}
