<?php


use Phinx\Migration\AbstractMigration;

class CreateActivityTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $activity = $this->table('activity');
        $activity
            ->addColumn('user_id', 'integer', ['limit' => 11])
            ->addColumn('activity_user_id', 'integer', ['limit' => 11])
            ->addColumn('activity_type','integer', ['limit' => 1])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addForeignKey('activity_type', 'activity_type', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->addForeignKey('user_id', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->addForeignKey('activity_user_id', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->create();
    }
}
