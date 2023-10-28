<?php

namespace common\models\generated;

use Yii;

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
class CompanyUtm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_utm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'type'], 'required'],
            [['company_id'], 'integer'],
            [['utm_created_at', 'created_at'], 'safe'],
            [['type'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 64],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'type' => 'Type',
            'value' => 'Value',
            'utm_created_at' => 'Utm Created At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
