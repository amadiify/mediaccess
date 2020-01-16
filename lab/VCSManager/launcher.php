<?php

class Launcher extends Assist
{
    public static function migrate($arr)
    {
        array_push($arr, '-tables');
        array_push($arr, '-from=tables/');
        parent::migrate($arr);
    }

    public static function _new($arr)
    {
        array_push($arr, '-tables/');
        parent::_new($arr);
    }

    // peek
    public static function peek($arr)
    {
        array_unshift($arr, 'peek');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }

    // push
    public static function push($arr)
    {
        array_unshift($arr, 'push');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }

    public static function publish($arr)
    {
        array_unshift($arr, 'publish');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }

    public static function set($arr)
    {
        array_unshift($arr, 'set');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }

    public static function pull($arr)
    {
        array_unshift($arr, 'pull');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }

    public static function rollback($arr)
    {
        array_unshift($arr, 'rollback');
        array_push($arr, '-remote');
        parent::vcs($arr);
    }
}