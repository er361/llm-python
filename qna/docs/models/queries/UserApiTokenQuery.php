<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * Class UserApiTokenQuery
 * @package common\models\queries
 */
class UserApiTokenQuery extends ActiveQuery
{
    /**
     * @param string $token
     * @return UserApiTokenQuery
     */
    public function byTokenJoinUser(string $token): UserApiTokenQuery
    {
        return $this->where(['auth_token' => $token])
            ->andWhere('valid_before > NOW()')
            ->innerJoin('user u', 'u.id = user_api_token.user_id');
    }

    /**
     * @param string $token
     * @return UserApiTokenQuery
     */
    public function byRefreshTokenJoinUser(string $token): UserApiTokenQuery
    {
        return $this->where(['refresh_token' => $token])
            ->andWhere('valid_before > NOW()')
            ->innerJoin('user u', 'u.id = user_api_token.user_id');
    }
}