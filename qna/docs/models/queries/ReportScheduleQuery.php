<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * Class ReportScheduleQuery
 * @package common\models\queries
 */
class ReportScheduleQuery extends ActiveQuery
{
    /**
     * @param int $userId
     * @return ReportScheduleQuery
     */
    public function byUser(int $userId): ReportScheduleQuery
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * @param int $id
     * @return ReportScheduleQuery
     */
    public function byId(int $id): ReportScheduleQuery
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * @param array $ids
     * @return ReportScheduleQuery
     */
    public function byIds(array $ids): ReportScheduleQuery
    {
        return $this->andWhere(['id' => $ids]);
    }
}