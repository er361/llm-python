<?php

namespace common\models\generated;

use Yii;

/**
 * This is the model class for table "stat".
 *
 * @property int $id
 * @property string $event
 * @property string $date
 * @property int $count
 * @property double $amount
 */
class Stat extends \yii\db\ActiveRecord {
	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'stat';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['date'], 'required'],
			[['date'], 'safe'],
			[['count'], 'integer'],
			[['amount'], 'number'],
			[['event'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'event' => 'Event',
			'date' => 'Date',
			'count' => 'Count',
			'amount' => 'Amount',
		];
	}
}
