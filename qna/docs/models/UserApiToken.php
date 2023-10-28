<?php

namespace common\models;

use common\models\queries\UserApiTokenQuery;
use yii\db\ActiveQuery;

/**
 * Class UserApiToken
 * @package common\models
 *
 * @property User $user
 */
class UserApiToken extends \common\models\generated\UserApiToken
{
    /**
     * {@inheritdoc}
     * @return UserApiTokenQuery the active query used by this AR class.
     */
    public static function find(): UserApiTokenQuery
    {
        return new UserApiTokenQuery(get_called_class());
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
