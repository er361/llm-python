<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "adm_user".
 *
 * @property int $id
 * @property int $client_id
 * @property string $secret
 * @property int|null $disabled
 *
 * @property AdmToken[] $admTokens
 */
class AdmUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'adm_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'secret'], 'required'],
            [['client_id', 'disabled'], 'integer'],
            [['secret'], 'string', 'max' => 255],
            [['client_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'secret' => 'Secret',
            'disabled' => 'Disabled',
        ];
    }

    /**
     * Gets query for [[AdmTokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmTokens()
    {
        return $this->hasMany(AdmToken::className(), ['user_id' => 'id']);
    }
}
