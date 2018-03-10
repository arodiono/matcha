<?php


use Phinx\Migration\AbstractMigration;

class CreateLikeTable extends AbstractMigration
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
        $likes = $this->table('likes');

        $likes
            ->addColumn('whom_id', 'integer')
            ->addColumn('who_id', 'integer')
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addColumn('updated_at', 'timestamp', [
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addIndex('whom_id', [
                'name' => 'whom_id'
            ])
            ->addIndex('who_id', [
                'name' => 'who_id'
            ])
            ->addForeignKey('whom_id', 'users', 'id', [
                'constraint' => 'whom_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('who_id', 'users', 'id', [
                'constraint' => 'who_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}
