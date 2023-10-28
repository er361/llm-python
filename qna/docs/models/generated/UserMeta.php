<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "user_meta".
 *
 * @property int $id
 * @property int $user_id
 * @property string $meta_key
 * @property string $meta_value
 * @property string $updated
 */
class UserMeta extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_meta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'meta_key', 'updated'], 'required'],
            [['user_id'], 'integer'],
            [['updated'], 'safe'],
            [['meta_key'], 'string', 'max' => 32],
            [['meta_value'], 'string', 'max' => 256],
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
            'meta_key' => 'Meta Key',
            'meta_value' => 'Meta Value',
            'updated' => 'Updated',
        ];
    }
}
