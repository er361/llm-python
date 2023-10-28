<?php

namespace common\models\generated;

use common\models\CompanyMetaQuery;
use Yii;

/**
 * This is the model class for table "company_meta".
 *
 * @property int $id
 * @property int $company_id
 * @property string $meta_key
 * @property string $meta_value
 * @property string $updated
 *
 * @property Company $company
 */
class CompanyMeta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'meta_key', 'updated'], 'required'],
            [['company_id'], 'integer'],
            [['updated'], 'safe'],
            [['meta_key'], 'string', 'max' => 64],
            [['meta_value'], 'string', 'max' => 256],
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
            'meta_key' => 'Meta Key',
            'meta_value' => 'Meta Value',
            'updated' => 'Updated',
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

    /**
     * {@inheritdoc}
     * @return CompanyMetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyMetaQuery(get_called_class());
    }
}
