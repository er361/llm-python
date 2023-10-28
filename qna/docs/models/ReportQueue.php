<?php

namespace common\models;

use Yii;

class ReportQueue extends \common\models\generated\ReportQueue
{
    const REPORT_TYPE_ACTIVITY = 'activity';
    const REPORT_TYPE_AMOUNT = 'amount';
    const REPORT_TYPE_TIME_ADJUSTMENT = 'time-adjustment';
    const REPORT_TYPE_DELETED = 'deleted';
    const REPORT_TYPE_IDLE = 'idle';
    const REPORT_TYPE_APP = 'app';
	const REPORT_TYPE_WEEKLY = 'weekly';

    const OUTPUT_TYPE_DATA = 'data';
    const OUTPUT_TYPE_CSV = 'csv';
    const OUTPUT_TYPE_PDF = 'pdf';

    const BY_PEOPLE = 'people';
    const BY_GROUP = 'group';
    const BY_DATE = 'date';

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['attempt'], 'default', 'value' => 0]
        ]);
    }

    public static function getSrcPath() {
        return Yii::getAlias('@app/api2/tmp/');
    }

    public static function setAsDone($jobId, $attempt) {
        $job = self::findByJobId($jobId);
        if ($job) {
            $job->done_at = date('Y-m-d H:i:s');
            $job->attempt = $attempt;
            $job->save();
        }
    }

    public function getResult() {
//        $path = $this->getSrc();
//
//        if (!$this->asFile()) {
//            return json_decode(file_get_contents($path));
//        } else {
//            Yii::$app->response->sendFile($path, basename($path));
//            Yii::$app->response->send();
//        }

        return json_decode(file_get_contents($this->getSrc()));
    }

    public static function findByJobId($jobId) {
        return self::find()
            ->where(['job_id' => $jobId])
            ->one();
    }

    public function isDone() {
        return !is_null($this->done_at);
    }

    public function getData() {
        return json_decode($this->data);
    }

    public function asFile() {
        $data = $this->getData();

        if ($data->output != self::OUTPUT_TYPE_DATA) {
            return true;
        }

        return false;
    }

    public function getSrc() {
        return $this->src;
    }

    public function getOutputType() {
        $data = $this->getData();

        if (in_array($data->output, [
            self::OUTPUT_TYPE_CSV,
            self::OUTPUT_TYPE_PDF
        ])) {
            return $data->output;
        }

        return false;
    }

    public function deleteSrc() {
        unlink($this->src);
    }

    public function srcExists() {
        return file_exists($this->src);
    }
}
