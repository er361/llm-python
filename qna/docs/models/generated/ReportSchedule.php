<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "report_schedule".
 *
 * @property int $id
 * @property int $user_id
 * @property string $email type json
 * @property string $report_type
 * @property string $by
 * @property string $output
 * @property string $period
 * @property string $frequency
 * @property string $daily type json
 * @property string $weekly
 * @property string $monthly
 * @property int $time
 * @property string $users type json
 * @property string $groups type json
 * @property string $timezone
 * @property int $detailed
 * @property int $empty
 * @property int $round
 * @property string $sort type json
 * @property string $last_execute
 * @property string $monthly_array
 *
 * @property User $user
 */
class ReportSchedule extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'email', 'report_type', 'period', 'frequency', 'time'], 'required'],
            [['user_id', 'time', 'detailed', 'empty', 'round'], 'integer'],
            [
                [
                    'email',
                    'report_type',
                    'by',
                    'output',
                    'period',
                    'frequency',
                    'daily',
                    'weekly',
                    'monthly',
                    'monthly_array',
                    'users',
                    'groups',
                    'sort'
                ],
                'string'
            ],
            [['last_execute'], 'safe'],
            [['timezone'], 'string', 'max' => 64],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
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
            'email' => 'Email',
            'report_type' => 'Report Type',
            'by' => 'By',
            'output' => 'Output',
            'period' => 'Period',
            'frequency' => 'Frequency',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'time' => 'Time',
            'users' => 'Users',
            'groups' => 'Groups',
            'timezone' => 'Timezone',
            'detailed' => 'Detailed',
            'empty' => 'Empty',
            'round' => 'Round',
            'sort' => 'Sort',
            'last_execute' => 'Last Execute',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
