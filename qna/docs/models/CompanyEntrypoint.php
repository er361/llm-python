<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Хранение url первого входа пользователя - владельца компании
 * url поступает из app.traqq.com в виде строки, максимальный размер - 512 символов
 *
 * @task https://pm.auslogics.com/projects/67/tasks/21533
 *
 * @property string $urlValueHostPath
 */
class CompanyEntrypoint extends generated\CompanyEntrypoint
{
    /**
     * @inheritDoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function getUrlValueHostPath() {
        $parts = parse_url($this->url_value);

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $path = isset($parts['path']) ? $parts['path'] : '';

        return "$scheme$host$path";
    }

}
