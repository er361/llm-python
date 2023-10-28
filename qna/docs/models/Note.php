<?php

namespace common\models;

use common\dictionaries\NoteColor;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\db\Expression;

/**
 * Class Note
 * @package common\models
 *
 * Заметки пользователя
 *
 * @property User $user
 */
class Note extends generated\Note {
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	/**
	 * @inheritDoc
	 */
	public function behaviors() {
		return [
			'timestamp' => [
				'class' => TimestampBehavior::class,
				'attributes' => [
					BaseActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
					BaseActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
				],
				'value' => new Expression('CURRENT_TIMESTAMP'),
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function scenarios() {
		$commonAttributes = ['text', 'color'];

		return [
			self::SCENARIO_CREATE => array_merge($commonAttributes, ['user_id', 'user_date', '!utc_date']),
			self::SCENARIO_UPDATE => $commonAttributes,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function rules() {
		return [
			[['user_id'], 'required'],
			[['user_id'], 'integer'],
			[['user_id'], 'exist', 'skipOnError' => true, 'targetRelation' => 'user'],

			[['user_date', 'utc_date'], 'date', 'format' => 'php:Y-m-d'],
			[
				['user_date'],
				'default',
				'value' => function ($model, $attribute) {
					return $this->user->getLocalCurrentDateTime('Y-m-d');
				},
				'when' => function ($model) {
					return !is_null($this->user);
				},
			],
			[['utc_date'], 'default', 'value' => date('Y-m-d')],

			[['text'], 'trim'],
			[['text'], 'required', 'on' => self::SCENARIO_CREATE],
			[['text'], 'string', 'min' => 1, 'max' => 2000, 'skipOnEmpty' => false],
            [['text'], function ($attr) {
                $this->text = mb_strlen(strip_tags($this->$attr));
            }],

			[['color'], 'in', 'range' => NoteColor::list()],
			[['color'], 'default', 'value' => null],
		];
	}

	/**
	 * @return ActiveQuery
	 */
	public function getUser() {
		return $this->hasOne(User::class, ['id' => 'user_id']);
	}

	public function beforeSave($insert) {
		if (!parent::beforeSave($insert)) {
			return false;
		}

		\Yii::$app->stat->user([
			'user_id' => $this->user->id,
			'event' => ($insert) ? \common\models\Stat::EVENT_USER_NOTE_CREATE : \common\models\Stat::EVENT_USER_NOTE_UPDATE,
			'data' => UserStat::extractStatData($this->user),
		]);

		return true;
	}

	public function beforeDelete() {
		if (!parent::beforeDelete()) {
			return false;
		}

		\Yii::$app->stat->user([
			'user_id' => $this->user->id,
			'event' => \common\models\Stat::EVENT_USER_NOTE_DELETE,
			'data' => UserStat::extractStatData($this->user),
		]);

		return true;
	}
}
