<?php


use Phinx\Migration\AbstractMigration;

class CreateMessagesTable extends AbstractMigration
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
        $messages = $this->table('messages');
        $messages
            ->addColumn('sender', 'integer', ['limit' => 11])
            ->addColumn('receiver', 'integer', ['limit' => 11])
            ->addColumn('message', 'string')
            ->addColumn('has_been_read', 'boolean', ['default' => false])
            ->addColumn('conversation_id', 'integer')
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->addForeignKey('conversation_id', 'conversations', 'id',
                array('delete' => 'cascade', 'update' => 'no action' ))
            ->create();
    }
}