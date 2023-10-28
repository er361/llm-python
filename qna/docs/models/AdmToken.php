<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;



use common\components\time\Time;

class AdmToken extends \common\models\generated\AdmToken {

    protected static $_tokensSalt = 'YnR1Yahxeihoz5aik3bk';

    public $expireTime = '+1 hour';

    public function createToken($userId) {
        $this->user_id = $userId;
        $this->auth_token = $this->_createToken();
        $this->token_ip = \Yii::$app->request->getUserIP();
        $this->last_login_ip = \Yii::$app->request->getUserIP();
        $this->last_logged_at = date(Time::FORMAT_MYSQL);
        $this->created_at = date(Time::FORMAT_MYSQL);
        $this->valid_before = date(Time::FORMAT_MYSQL,strtotime($this->expireTime));

        $this->deleteOld();

        if ($this->save()) {
            $this->refresh();
            return true;
        }
        return false;
    }

    public static function deleteOld() {
        self::deleteAll(['<','valid_before',date(Time::FORMAT_MYSQL)]);
    }

    public function getToken() {
        return $this->auth_token . '-' . self::getSalt($this->auth_token);
    }

    public static function getSalt($token) {
        return md5($token . self::$_tokensSalt);
    }

    protected function _createToken() {
        return md5(microtime());
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(AdmUser::className(), ['id' => 'user_id']);
    }
}