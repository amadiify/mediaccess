<?php

use Moorexa\Structure as Schema;
use Moorexa\Hash;

class VcsUsers
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up(Schema $schema)
    {
        $schema->increment('VcsUsersid');
        $schema->string('username')->unique();
        $schema->string('password');
        $schema->int('VcsRolesid')->default(1);
        $schema->tinyint('locked')->default(0);
    }

    // drop table
    public function down($drop, $record)
    {
        // $record carries table rows if exists.
        // execute drop table command
        $drop();
    }

    // options
    public function option($option)
    {
        $option->rename('VcsUsers'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status)
    {
        if ($status == 'complete')
        {
            $this->addUser();
        }
    }

    // add version control authorized users
    private function addUser()
    {
        $this->table->insert(

            ['username' => 'admin',
             'password' => Hash::digest('1234'),
             'VcsRolesid' => 2
            ],

            ['username' => 'devuser',
             'password' => Hash::digest('123456789'),
             'VcsRolesid' => 1
            ]
        );
    }
}