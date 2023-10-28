<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_app_15m_n".
 *
 * @property string $hash
 * @property string $name
 * @property string|null $url
 */
class UserApp15mN extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_app_15m_n';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hash', 'name'], 'required'],
            [['hash'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 1024],
            [['url'], 'string', 'max' => 255],
            [['hash'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'hash' => 'Hash',
            'name' => 'Name',
            'url' => 'Url',
        ];
    }
}
