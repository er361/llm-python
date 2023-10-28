<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "timezone".
 *
 * @property int $id
 * @property string $timezone
 * @property string $timezone_microsoft
 * @property string $offset
 */
class Timezone extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'timezone';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['timezone', 'timezone_microsoft'], 'required'],
            [['timezone', 'timezone_microsoft', 'offset'], 'string', 'max' => 255],
            [['timezone'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'timezone' => 'Timezone',
            'timezone_microsoft' => 'Timezone Microsoft',
            'offset' => 'Offset',
        ];
    }
}
