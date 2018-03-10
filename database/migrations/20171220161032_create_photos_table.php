<?php


use Phinx\Migration\AbstractMigration;

class CreatePhotosTable extends AbstractMigration
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
        $photos = $this->table('photos');
        $photos->addColumn('photo', 'string', ['limit' => 32])
            ->addColumn('user_id', 'integer', ['limit' => 11])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addIndex('user_id', [
                'name' => 'user_id'
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'constraint' => 'photos_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}
