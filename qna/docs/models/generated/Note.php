<?php

namespace common\models\generated;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "note".
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_date
 * @property string $utc_date
 * @property string $text
 * @property string|null $color
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class Note extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'note';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'user_date', 'utc_date', 'text'], 'required'],
            [['user_id'], 'integer'],
            [['user_date', 'utc_date', 'created_at', 'updated_at'], 'safe'],
            [['text', 'color'], 'string'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetRelation' => 'user'],
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
            'user_date' => 'User Date',
            'utc_date' => 'UTC Date',
            'text' => 'Text',
            'color' => 'Color',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
