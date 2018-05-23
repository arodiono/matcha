<?php


use Phinx\Migration\AbstractMigration;

class CreateFakeAccountTable extends AbstractMigration
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
        $fake = $this->table('fake');
        $fake
            ->addColumn('reporter', 'integer', ['limit' => 11])
            ->addColumn('fake', 'integer', ['limit' => 11])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addForeignKey('reporter', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->addForeignKey('fake', 'users', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->create();
    }
}
