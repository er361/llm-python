<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_invoice_preference".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $street
 * @property string $city
 * @property string $state
 * @property string $postcode
 * @property string $country_code
 * @property double $tax_rate
 * @property string $tax_id
 * @property string $bank_name
 * @property string $bank_account_number
 * @property string $bank_account_holder
 * @property string $bank_swift
 * @property string $bank_transfer_instruction
 *
 * @property User $user
 */
class UserInvoicePreference extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_invoice_preference';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['tax_rate'], 'number'],
            [['name', 'city', 'state', 'bank_account_holder', 'bank_transfer_instruction'], 'string', 'max' => 128],
            [['street', 'bank_account_number'], 'string', 'max' => 255],
            [['postcode'], 'string', 'max' => 12],
            [['country_code'], 'string', 'max' => 2],
            [['tax_id'], 'string', 'max' => 24],
            [['bank_name'], 'string', 'max' => 64],
            [['bank_swift'], 'string', 'max' => 32],
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
            'user_id' => 'User ID',
            'name' => 'Name',
            'street' => 'Street',
            'city' => 'City',
            'state' => 'State',
            'postcode' => 'Postcode',
            'country_code' => 'Country Code',
            'tax_rate' => 'Tax Rate',
            'tax_id' => 'Tax ID',
            'bank_name' => 'Bank Name',
            'bank_account_number' => 'Bank Account Number',
            'bank_account_holder' => 'Bank Account Holder',
            'bank_swift' => 'Bank Swift',
            'bank_transfer_instruction' => 'Bank Transfer Instruction',
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
