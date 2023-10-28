<?php

/**
 * @author Pavel A. Lebedev <pavel.lebedev@auslogics.com>
 * @copyright Copyright (c) 2020 Auslogics Labs Pty Ltd (https://www.auslogics.com)
 */

namespace common\models;

use api2\components\behaviors\Media;
use api2\components\behaviors\TimeIntervalDelete;
use api2\components\behaviors\TimeIntervalFind;
use api2\jobs\MediaDelete;
use api2\jobs\MediaToS3;

/**
 * Class UserVideo
 *
 * @inheritdoc
 * @package common\models
 */
class UserVideo extends \common\models\generated\UserVideo
{
    public $url_s3;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'image' => [
                'class' => Media::class,
                'viewPath' => '/video/view/%id%',
                'mediaComponent' => \Yii::$app->video,
                'ownerProp' => 'src',
            ],
            'deleteInterval' => [
                'class' => TimeIntervalDelete::class,
            ],
            'interval' => [
                'class' => TimeIntervalFind::class,
            ],
        ]);
    }

    public function afterSave($insert, $changedAttributes)
    {
        //Драйвер видео уже может быть s3, если он будет добавлен с клиента
        if ($insert && $this->driver != 's3') {
            \Yii::$app->queue->push(new MediaToS3([
                'model' => self::class,
                'id' => $this->id,
            ]));
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        \Yii::$app->queue->push(new MediaDelete(['media' => [
            ['component' => 'video', 'driver' => $this->driver, 'id' => $this->src]
        ]]));
        parent::afterDelete();
    }

    /**
	 * @param array $activity
	 * @return UserVideo
	 */
    public static function createFrom($activity)
    {
        $self = new self();
        $self->id = $activity['video_id'];
        $self->user_id = $activity['user_id'];
        $self->src = $activity['src'];
        $self->driver = $activity['video_driver'];
        $self->user_time = $activity['user_time'];
        $self->utc_time = $activity['utc_time'];
        $self->duration = $activity['video_duration'];
        $self->block_id = $activity['block_id'];

        return $self;
    }

    public static function getRandomVideo()
    {
        $videoDemo = __DIR__ . '/../data/videos';
        $d = glob($videoDemo . '/*.mp4');
        return $d[rand(0, sizeof($d) - 1)];
    }
}
