<?php


use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
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
        $users = $this->table('users');
        $users->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('password', 'string', ['limit' => 60])
            ->addColumn('username', 'string', ['limit' => 255])
            ->addColumn('first_name', 'string', [
                'limit' => 255,
                'null' => TRUE
            ])
            ->addColumn('last_name', 'string', [
                'limit' => 255,
                'null' => TRUE
            ])
            ->addColumn('gender', 'string', [
                'limit' => 6,
                'null' => TRUE
            ])
            ->addColumn('sex_preference', 'string', [
                'limit' => 32,
                'default' => 'bisexual'
            ])
            ->addColumn('bio', 'string', [
                'limit' => 150,
                'null' => TRUE
            ])
            ->addColumn('profile_photo', 'string', [
                'limit' => 32,
                'null' => TRUE
            ])
            ->addColumn('rating', 'integer', [
                'limit' => 11,
                'null' => TRUE
            ])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'null' => TRUE
            ])
            ->create();
    }
}
