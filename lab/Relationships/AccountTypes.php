<?php

namespace Relationships;

use Moorexa\DB;

class AccountTypes
{
    public static function find(DB $query, string $type)
    {
        $query->get('accounttype=?', $type);
    }

    // find all publics
    public static function findAllPublic(DB $query)
    {
        $query->get('showpublic=1')->orderby('accounttype','asc');
    }
} 