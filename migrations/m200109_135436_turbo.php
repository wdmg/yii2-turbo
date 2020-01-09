<?php

use yii\db\Migration;

/**
 * Class m200109_135436_turbo
 */
class m200109_135436_turbo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        if (class_exists('\wdmg\pages\models\Pages')) {
            $userTable = \wdmg\pages\models\Pages::tableName();

            if (is_null($this->getDb()->getSchema()->getTableSchema($userTable)->getColumn('in_turbo')))
                $this->addColumn($userTable, 'in_turbo', $this->boolean()->defaultValue(true));

        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        if (class_exists('\wdmg\pages\models\Pages')) {
            $userTable = \wdmg\pages\models\Pages::tableName();

            if (!is_null($this->getDb()->getSchema()->getTableSchema($userTable)->getColumn('in_turbo')))
                $this->dropColumn($userTable, 'in_turbo');

        }

    }
}
