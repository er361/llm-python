<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_stat".
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $created_at
 * @property string|null $event
 * @property string|null $data
 */
class CompanyStat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_stat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id'], 'integer'],
            [['created_at'], 'safe'],
            [['data'], 'string'],
            [['event'], 'string', 'max' => 30],
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
            'created_at' => 'Created At',
            'event' => 'Event',
            'data' => 'Data',
        ];
    }
}
