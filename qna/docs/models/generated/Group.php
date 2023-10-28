<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "group".
 *
 * @property int $id
 * @property string $name
 * @property int $company_id
 *
 * @property Company $company
 * @property UserAccess[] $userAccesses
 * @property User[] $owners
 * @property UserAppGroupCache[] $userAppGroupCaches
 * @property UserGroup[] $userGroups
 * @property User[] $users
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name', 'company_id'], 'unique', 'targetAttribute' => ['name', 'company_id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_id' => 'Company ID',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[UserAccesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccesses()
    {
        return $this->hasMany(UserAccess::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[Owners]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(User::className(), ['id' => 'owner_id'])->viaTable('user_access', ['group_id' => 'id']);
    }

    /**
     * Gets query for [[UserAppGroupCaches]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAppGroupCaches()
    {
        return $this->hasMany(UserAppGroupCache::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[UserGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroups()
    {
        return $this->hasMany(UserGroup::className(), ['group_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('user_group', ['group_id' => 'id']);
    }
}
