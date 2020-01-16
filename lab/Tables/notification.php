<?php

use Moorexa\Structure as Schema;

class Notification
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up(Schema $schema)
    {
        $schema->increment('notificationid');
        $schema->string('target');
        $schema->string('accountid');
        $schema->string('hash');
        $schema->int('seen')->default(0);
        // and more.. 
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
        $option->rename('notification'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status)
    {
        if ($status == 'waiting' || $status == 'complete')
        {
            // do some cool stuffs.
            // $this->table => for ORM operations to this table.
        }
    }
}