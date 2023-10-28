<?php
/**
 * @author Andrey A. Kirichenko <andrey.kirichenko@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;


use api2\components\behaviors\TimeIntervalDelete;
use api2\components\behaviors\TimeIntervalFind;
use api2\components\validators\TimeIntersectValidator;
use common\components\time\Time;

class UserSiteNew extends \common\models\generated\UserAppNew {

    public function behaviors() {
        return array_merge(parent::behaviors(),[
            'deleteInterval'=> [
                'class' => TimeIntervalDelete::class,
            ],
            'interval' => [
                'class' => TimeIntervalFind::class,
            ],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function find() {
        $result = parent::find();
        $result->where(['not',['url'=>NULL]]);
        return $result;
    }
    
}