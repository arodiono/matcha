<?php


use Phinx\Migration\AbstractMigration;

class CreateTagsTable extends AbstractMigration
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
        $tags = $this->table('tags');
        $tags->addColumn('tag', 'string', ['limit' => 255])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->create();

        $tagsRelations = $this->table('users-tags');
        $tagsRelations->addColumn('tag_id', 'integer', ['null' => TRUE])
            ->addColumn('user_id', 'integer', ['null' => TRUE])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addIndex('tag_id', [
                'name' => 'tag_id'
            ])
            ->addIndex('user_id', [
                'name' => 'user_id'
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'constraint' => 'tags_user_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->addForeignKey('tag_id', 'tags', 'id', [
                'constraint' => 'tags_tag_id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE'
            ])
            ->create();
    }
}
