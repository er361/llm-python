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
use common\components\time\Time;

class UserScreen extends \common\models\generated\UserScreen
{
    public $url_s3;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'image' => [
                'class' => Media::class,
                'viewPath' => '/screen/view/%id%',
            ],
            'deleteInterval' => [
                'class' => TimeIntervalDelete::class,
            ],
            'interval' => [
                'class' => TimeIntervalFind::class,
            ],
        ]);
    }

    public function setSrc($src)
    {
        $this->image = $src;
    }

    public function getSrc()
    {
        return $this->image;
    }

    public function afterSave($insert, $changedAttributes)
    {
        //Драйвер скрина уже может быть s3, если он будет добавлен с клиента
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
            ['component' => 'screen', 'driver' => $this->driver, 'id' => $this->image]
        ]]));
        parent::afterDelete();
    }

    public static function getRandomScreen()
    {
        $screenDemo = __DIR__ . '/../data/screens';
        $d = glob($screenDemo . '/*.jpg');
        return $d[rand(0, sizeof($d) - 1)];
    }

    /**
     * @param array $activity
     * @return UserScreen
     */
    public static function createFrom($activity, $timezone)
    {
        $self = new self();
        $self->id = $activity['screen_id'];
        $self->user_id = $activity['user_id'];
        $self->image = $activity['image'];
        $self->driver = $activity['driver'];
        $self->user_time = $activity['user_time'];
        $self->utc_time = $activity['utc_time'];
        $self->monitor = $activity['monitor'];
        $self->block_id = $activity['block_id'];

        return $self;
    }
}
