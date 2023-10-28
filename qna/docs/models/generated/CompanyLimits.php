<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "company_limits".
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property string $limit
 * @property string $timezone
 * @property string $days
 * @property string $created_at
 * @property string $updated_at
 * @property string $roles
 */
class CompanyLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['timezone', 'days', 'roles'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['from', 'to'], 'string', 'max' => 8],
            [['limit'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from' => 'From',
            'to' => 'To',
            'limit' => 'Limit',
            'timezone' => 'Timezone',
            'days' => 'Days',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'roles' => 'Roles',
        ];
    }
}
