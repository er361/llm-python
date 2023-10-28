<?php

/**
 * @author Roman O. Malkin <roman.malkin@auslogics.com>
 * @copyright Copyright (c) 2023 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models\redis;

use Yii;

/**
 * Feedback код который генерируется после удаления компании/пользователя
 * @package common\models\redis
 */
class FeedbackCode extends \yii\redis\ActiveRecord
{
    const TYPE_DELETE_COMPANY = 'company';
    const TYPE_DELETE_USER = 'user';

    /**
     * {@inheritDoc}
     */
    public function attributes()
    {
        return ['id', 'type', 'user_id', 'code'];
    }

    /**
     * @return self
     */
    public function createCode(): self
    {
        $code = Yii::$app->security->generateRandomString();
        $this->code = $code;
        $this->save();
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCode(): string
    {
        if (!$code = self::findOne(['user_id' => $this->user_id, 'type' => $this->type])) {
            $code = $this->createCode();
        }
        return $code->code;
    }

    /**
     * @param  string $code
     * @param  string $type
     * @return self
     */
    public static function findByCode($code, $type): ?self
    {
        return self::find()->where(['code' => $code, 'type' => $type])->one();
    }
}