<?php
/**
 * @author Vitaliy Polyakov <vitaliy.polyakov@auslogics.com>
 * @copyright Copyright (c) 2021 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */
namespace common\models;

use api2\components\behaviors\TimeIntervalDelete;
use api2\components\behaviors\TimeIntervalFind;
use api2\components\behaviors\VoipTimeIntervalFind;
use api2\components\validators\DateIntervalValidator;
use api2\components\validators\OldIntersectValidator;
use common\components\time\Time;
use Yii;

/**
 * This is the model class for table "user_voip".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $user_time
 * @property string $utc_time
 * @property int $duration
 *
 * @property User $user
 */
class UserVoip extends generated\UserVoip
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'deleteInterval' => [
                'class' => TimeIntervalDelete::class,
            ],
            'interval' => [
                'class' => TimeIntervalFind::class,
            ],
        ]);
    }
    public function rules()
    {
        return
        [
            [[ 'name', 'duration'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['duration'], function ($attr) {
                $this->user_time = $this->$attr->time->format(Time::FORMAT_MYSQL);
                $this->utc_time = $this->$attr->toUtcTime()->time->format(Time::FORMAT_MYSQL);
                $this->duration = intval($this->$attr->interval->getSeconds());
            }, 'on' => self::SCENARIO_UPDATE],
            ['utc_time', OldIntersectValidator::class, 'on' => self::SCENARIO_ADD, 'where' => function ($exists, $model) {
            $exists->andWhere(['=', 'name', $model->name]);}],
            [['duration'], DateIntervalValidator::class, 'on' => self::SCENARIO_DELETE],

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
            'name' => 'Name',
            'user_time' => 'User Time',
            'utc_time' => 'Utc Time',
            'duration' => 'Duration',
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
