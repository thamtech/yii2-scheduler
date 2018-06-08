<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Create the scheduler task and log tables.
 */
class m150510_090513_Scheduler extends Migration
{
    const TABLE_SCHEDULER_LOG = '{{%scheduler_log}}';
    const TABLE_SCHEDULER_TASK = '{{%scheduler_task}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_SCHEDULER_TASK, [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'display_name' => $this->string()->notNull(),
            'schedule' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'status_id' => $this->integer()->unsigned()->notNull(),
            'started_at' => $this->dateTime()->null()->defaultValue(null),
            'last_run' => $this->dateTime()->null()->defaultValue(null),
            'next_run' => $this->dateTime()->null()->defaultValue(null),
        ], $tableOptions);

        $this->createIndex('idx_name', self::TABLE_SCHEDULER_TASK, 'name', true);

        $this->createTable(self::TABLE_SCHEDULER_LOG, [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'scheduler_task_id' => $this->integer()->unsigned()->notNull(),
            'started_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'ended_at' => $this->dateTime()->defaultValue(null),
            'output' => $this->text()->notNull(),
            'error' => $this->boolean()->defaultValue(false),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_scheduler_log_scheduler_task_id', // foreign key id
            self::TABLE_SCHEDULER_LOG,        // this table
            'scheduler_task_id',              // column in this table
            self::TABLE_SCHEDULER_TASK,       // foreign table
            'id',                             // foreign column
            'CASCADE',                        // on delete
            'CASCADE'                         // on update
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_SCHEDULER_LOG);
        $this->dropTable(self::TABLE_SCHEDULER_TASK);
    }
}
