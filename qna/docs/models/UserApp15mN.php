<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use Exception;

class UserApp15mN extends \common\models\generated\UserApp15mN {

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find() {
        $result = parent::find();
        $result->where(['url'=>null]);
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findWithSites() {
        $result = parent::find();
        return $result;
    }

    public static function batchInsertAppN($data) {
        if (count($data) == 0) {
            //echo "TEST"; die();
            return true;
        }

        foreach(array_chunk($data, 1000) as $chunkedData) {
            $command = \Yii::$app->getDb()->createCommand()->batchInsert(
                'user_app_15m_n',
                ['hash', 'name', 'url'],
                $chunkedData,
            );
            $command->sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $command->sql);

            try {
				$command->execute();
                //var_dump($r);
                return true;
			} catch(Exception $E) {
				return false;
			}
        }
	}

    public static function getAppName($hash) {
        $appN = self::findWithSites()
            ->where(['hash' => $hash])
            ->one();
        if(!$appN) return "";
        return $appN->url ?? $appN->name;
    }
}