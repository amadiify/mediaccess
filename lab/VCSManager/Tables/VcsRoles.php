<?php

use Moorexa\Structure as Schema;

class VcsRoles
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up(Schema $schema)
    {
        $schema->increment('VcsRolesid');
        $schema->string('Role')->unique();
        $schema->string('Permission')->default('push,pull'); // push versions
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
        $option->rename('VcsRoles'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status)
    {
        if ($status == 'complete')
        {
            $this->addRoles();
        }
    }

    // roles
    private function addRoles()
    {
        $this->table->insert('Role, Permission',
            // roles
            ['devuser', 'push,pull,peek'],
            ['superuser', 'push,publish,rollback,set,pull,peek']
        );
    }
}