<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models\redis;

use Yii;

/**
 * Метка ставится после отправки feedback чтобы предотвратить спам
 * @package common\models\redis
 */
class FeedbackMark extends yii\base\BaseObject
{
    const REDIS_HASH = 'feedback_mark';
    const INTERVAL_BETWEEN_SEND = 86400; //24 часа
    
    /**
     * @var string
     */
    public $key;
    
    /**
     * @var \yii\redis\Connection
     */
    private $redis_;
        
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        
        $this->redis_ = Yii::$app->redis;
    }
    
    /**
     * Проверяем есть ли метка об отправке за указанный интервал
     * @return bool
     */
    public function isExist(): bool
    {
        $createdAt = $this->redis_->hget(self::REDIS_HASH, $this->key);
        if (!empty($createdAt) && $createdAt > (time() - self::INTERVAL_BETWEEN_SEND)) {
            return true;
        } 
        return false;
    }
    
    /**
     * Сохраняем метку и устанавливаем время жизни на всю таблицу равное интервалу между отправками
     * @return bool
     */
    public function save(): bool
    {
        try {
            $this->redis_->hset(self::REDIS_HASH, $this->key, time());
            $this->redis_->expire(self::REDIS_HASH, self::INTERVAL_BETWEEN_SEND);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}