<?php

namespace common\models\generated;

/**
 * This is the ActiveQuery class for [[CompanyMeta]].
 *
 * @see CompanyMeta
 */
class CompanyMetaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CompanyMeta[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CompanyMeta|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
