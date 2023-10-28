<?php

namespace common\models;

use app\components\utms\Utms;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\db\Expression;
use app\components\utms\UtmItem;

/**
 * This is the model class for table "company_utm".
 *
 * @property int $id
 * @property int $company_id
 * @property string $type
 * @property string $value
 * @property string $utm_created_at
 * @property string $created_at
 *
 * @property Company $company
 */
class CompanyUtm extends generated\CompanyUtm
{

    const SCENARIO_CREATE = 'create';

    public function rules() {
        return array_merge(parent::rules(),[
            [['company_id', 'type', 'utm_created_at'], 'required'],
            [['type', 'value', 'company_id'], 'uniqueValidation', 'on' => CompanyUtm::SCENARIO_CREATE ],
            [['created_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at'], 'default', 'value' => function ($value) {
                $value = (new \DateTime($this->created_at ? $this->created_at : 'now'))->format('Y-m-d H:i:s');
                return $value;
            }],
        ]);
    }

    public function uniqueValidation($attribute, $params)
    {
        $query = CompanyUtm::find()
            ->where(['type' => $this->type])
            ->andWhere(['value' => $this->value])
            ->andWhere(['company_id' => $this->company_id]);
        if ($query->count() > 0) {
            $this->addError($attribute, 'Utm Item exists for company id ' . $this->company_id);
        }
    }

}
