<?php

use Moorexa\Structure as Schema;

class VcsHistory
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up(Schema $schema)
    {
        $schema->increment('VcsHistoryid');
        $schema->bigint('VcsUsersid');
        $schema->string('TransactionType');
        $schema->datetime('TransactionDate')->current();
        $schema->text('Transaction');
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
        $option->rename('VcsHistory'); // rename table
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