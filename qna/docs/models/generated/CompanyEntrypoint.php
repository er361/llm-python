<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_entrypoint".
 *
 * @property int $id
 * @property int $company_id
 * @property string $url_value
 * @property string|null $created_at
 *
 * @property Company $company
 */
class CompanyEntrypoint extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_entrypoint';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'url_value'], 'required'],
            [['company_id'], 'integer'],
            [['created_at'], 'safe'],
            [['url_value'], 'string', 'max' => 512],
            [['company_id'], 'unique'],
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
            'url_value' => 'Url Value',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
