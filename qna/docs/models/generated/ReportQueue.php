<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "report_queue".
 *
 * @property int $id
 * @property int $job_id
 * @property int $user_id
 * @property string $created_at
 * @property int $attempt
 * @property int $done_at
 * @property string $src
 * @property string $data
 *
 * @property User $user
 */
class ReportQueue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report_queue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'user_id', 'src', 'data'], 'required'],
            [['job_id', 'user_id', 'attempt'], 'integer'],
            [['created_at', 'done_at'], 'safe'],
            [['data'], 'string'],
            [['src'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'attempt' => 'Attempt',
            'done_at' => 'Done At',
            'src' => 'Src',
            'data' => 'Data',
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
