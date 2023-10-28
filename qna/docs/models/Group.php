<?php
/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use common\models\generated\Group as GeneratedGroup;


class Group extends GeneratedGroup
{
    const NO_GROUP_NAME = 'No group';
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['name', 'in', 'range' => [self::NO_GROUP_NAME], 'not' => true],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'duration',
            'activity',
            'app_name',
            'user_id',
            'user_count',
            'amount_earned'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        $users = User::find()->joinWith('accessGroups')->where(['user_access.group_id'=>$this->id])->all();
        foreach ($users as $user) {
            if ( count($user->accessGroups)==1 ) {
                $user->role = User::ROLE_USER;
                $user->save();
            }
        }

        return parent::beforeDelete();
    }

}