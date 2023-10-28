<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_settings".
 *
 * @property int $company_id
 * @property int $screenshots
 * @property int $video
 * @property int $apps
 * @property int $limits
 * @property int $start_week
 * @property string $timezone
 * @property int $invoicing
 * @property string $currency
 * @property int $activity
 *
 * @property Company $company
 */
class CompanySettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id', 'screenshots', 'video', 'apps', 'limits', 'start_week', 'invoicing', 'activity'], 'integer'],
            [['timezone'], 'string', 'max' => 64],
            [['currency'], 'string', 'max' => 3],
            [['company_id'], 'unique'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'company_id' => 'Company ID',
            'screenshots' => 'Screenshots',
            'video' => 'Video',
            'apps' => 'Apps',
            'limits' => 'Limits',
            'start_week' => 'Start Week',
            'timezone' => 'Timezone',
            'invoicing' => 'Invoicing',
            'currency' => 'Currency',
            'activity' => 'Activity',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
}
