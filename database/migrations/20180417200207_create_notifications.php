<?php


use Phinx\Migration\AbstractMigration;

class CreateNotifications extends AbstractMigration
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
        $notifications = $this->table('notifications');
        $notifications
            ->addColumn('who_id', 'integer', ['limit' => 11])
            ->addColumn('whom_id', 'integer', ['limit' => 11])
            ->addColumn('has_been_read', 'integer', ['limit' => 1, 'default' => 0])
            ->addColumn('type', 'enum', ['values' => ['like', 'unlike', 'mutually', 'visit']])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addForeignKey('who_id', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->addForeignKey('whom_id', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->create();
    }
}
