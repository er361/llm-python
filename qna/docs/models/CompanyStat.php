<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2021 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\Expression;


/**
 * Class CompanyStat
 *
 * @property array $json
 *
 * @package common\models
 */
class CompanyStat extends \common\models\generated\CompanyStat {

    public function rules() {
        return array_merge(parent::rules(),[
            ['created_at','default','value'=>date('Y-m-d H:i:s')]
        ]);
    }

    public function setJson($data) {
        $this->data = json_encode($data);
    }

    public function getJson() {
        return json_decode($this->data,true);
    }

    /**
     * Возвращает стату по компаниям, которые были запрошены к удалению, но не были восстановлены
     * @return ActiveQuery
     */
    public static function findFakeDeleted($where=[]) {
//        $query = "select company_id,`events` as `event`,`created_at`,`data` from (SELECT company_id,GROUP_CONCAT(`event`) as events,created_at,data FROM `company_stat` WHERE 1 and (`created_at` BETWEEN '2021-01-01' AND '2022-01-01') group by company_id) as c where c.`events` like '%afterFakeDelete%' and c.`events` not like '%afterRestore%'";
        $events = new ActiveQuery(self::class);
        $events->select(['company_id','created_at','data',new Expression('GROUP_CONCAT(`event`) as `events`')]);
        $events->andWhere($where);
        $events->groupBy(['company_id', 'created_at', 'data']);
        $query = new ActiveQuery(self::class);
        $query->from(['c'=>$events]);
        $query->andWhere(['LIKE','c.events',Company::EVENT_AFTER_FAKEDELETE])->andWhere(['NOT LIKE','c.events',Company::EVENT_AFTER_RESTORE]);
        return $query;
    }

    /**
     * @return ActiveQuery
     */
    public static function findRealDeleted() {
        return self::find()->andWhere(['or',['event'=>Company::EVENT_AFTER_DELETE],['event'=>Company::EVENT_BEFORE_DELETE]]);
    }

    /**
     * @return ActiveQuery
     */
    public static function findCreated() {
        return self::find()->andWhere(['event'=>Company::EVENT_AFTER_INSERT]);
    }

    public static function extractStatData($company) {
        $company_plan = '';
        if ($company->plan != null) {
            $company_plan = $company->plan->getAttributes([
                'team_size', 
                'period', 
                'trial_used', 
                'subscription_id', 
                'payment_plan_id', 
                'valid_till', 
                'updated_at'
            ]);
        }

        $data = [
            'count_users' => count($company->users),
            'company_plan' => $company_plan,
            'company' => $company->getAttributes([
                'id', 
                'name', 
                'country_code', 
                'timezone', 
                'signup_at'
            ]),
        ];

        return json_encode($data);
    }
}