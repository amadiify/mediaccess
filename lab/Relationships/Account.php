<?php
namespace Relationships;
use Moorexa\DB;

class Account
{
    public static function find(DB $query)
    {
        $args = func_get_args();
        $args = array_splice($args, 1);
        array_unshift($args, 'firstname=? and lastname=? and accounttypeid=?');

        call_user_func_array([$query, 'get'], $args);
    }
}