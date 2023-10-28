<?php

namespace common\models\generated;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "settings_changed_sign".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company
 * @property int $user
 * @property int $server
 * @property int $company_info
 * @property int $user_info
 */
class SettingsChangedSign extends ActiveRecord
{
    public const MAX_VALUE = 255;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'settings_changed_sign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['company', 'user', 'server', 'company_info', 'user_info'], 'integer', 'max' => self::MAX_VALUE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'company' => 'Company`s changed sign',
            'user' => 'User`s changed sign',
            'server' => 'Server`s changed sign',
            'company_info' => 'Company`s info changed sign',
            'user_info' => 'User`s info changed sign',
        ];
    }
}
