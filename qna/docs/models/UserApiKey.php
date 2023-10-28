<?php

namespace common\models;

use common\components\helpers\ApiKeyHelper;
use yii\db\ActiveQuery;

/**
 * Class UserApiKey
 * @package common\models
 */
class UserApiKey extends generated\UserApiKey
{
    public function __construct($config = [])
    {
        $this->api_key = ApiKeyHelper::generateKey();
        parent::__construct($config);
    }

    /**
     * Создает ключ api для пользователя
     *
     * @param int $userId
     * @return bool
     */
    public function createApiKey(int $userId): bool
    {
        $this->user_id = $userId;
        $this->valid_till = ApiKeyHelper::getExpireDate();
        return $this->save();
    }

    /**
     * @param int $userId
     * @return ActiveQuery
     */
    public static function findByUserId(int $userId): ActiveQuery
    {
        return UserApiKey::find()->where(['user_id' => $userId]);
    }

    /**
     * Возвращает ключ api по id пользователя
     *
     * @param int $userId
     * @return string|null
     */
    public static function getApiKeyByUserId(int $userId): ?string
    {
        return UserApiKey::findByUserId($userId)->select('api_key')->scalar();
    }

    /**
     * Генерирует новый ключ api для пользователя
     *
     * @param int $userId
     * @return string|null
     */
    public function generateNewApiKey(int $userId): ?string
    {
        /** @var UserApiKey $apiKey */
        $apiKey = UserApiKey::findByUserId($userId)->one();
        if ($apiKey) {
            $apiKey->api_key = ApiKeyHelper::generateKey();
            if ($apiKey->save()) {
                return $apiKey->api_key;
            }
        }
        return null;
    }
}
