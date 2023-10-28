<?php

namespace common\models\generated;

use Yii;
use DateTime;
use DateTimeZone;

/**
 * This is the model class for table "user_limits".
 *
 * @property int $id
 * @property int $user_id
 * @property string $from
 * @property string $to
 * @property string $limit
 * @property string $timezone
 * @property string $timezone_value
 * @property string $days
 * @property string $created_at
 * @property string $start_date
 */
class UserLimits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_limits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'from', 'to', 'limit', 'days'], 'required'],
            [['timezone', 'days', 'timezone_value'], 'string'],
            [['created_at'], 'safe'],
            [['from', 'to'], 'string', 'max' => 8],
            [['limit'], 'string', 'max' => 5],
            [['start_date'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'from' => 'From',
            'to' => 'To',
            'limit' => 'Limit',
            'timezone' => 'Timezone',
            'timezone_value' => 'Timezone Value',
            'days' => 'Days',
            'created_at' => 'Created At',
            'start_date' => 'Start Date',
        ];
    }

}
