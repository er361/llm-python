<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;


use common\components\time\Time;
use yii\db\Expression;
use yii\web\IdentityInterface;

class AdmUser extends \common\models\generated\AdmUser implements IdentityInterface {
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     **/
    public function validatePassword($password) {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->secret);
    }

    public static function findIdentity($id) {
        return self::findOne($id);
    }


    public static function findIdentityByAccessToken($token, $type = null) {
        $token = explode('-', $token);
        if ( ! isset($token[0]) || ! isset($token[1])) {
            return null;
        }

        $salt = AdmToken::getSalt($token[0]);
        if ($salt !== $token[1]) {
            return null;
        }

        /** @var AdmToken $tokenModel */
        $tokenModel = AdmToken::find()
            ->where(['auth_token'=>$token[0]])
            ->andWhere(['>','valid_before',new Expression('NOW()')])
            ->one();

        if ( ! $tokenModel) {
            return null;
        }

        $tokenModel->last_logged_at = date(Time::FORMAT_MYSQL);
        $tokenModel->last_login_ip = \Yii::$app->request->getUserIP();
        $tokenModel->save();

        $userModel = $tokenModel->user;
//        $userModel->token = $tokenModel;
        return $userModel;
    }

    public function getId() {
        return $this->id;
    }

    public function getAuthKey() {
        // TODO: Implement getAuthKey() method.
    }

    public function validateAuthKey($authKey) {
        // TODO: Implement validateAuthKey() method.
    }
}