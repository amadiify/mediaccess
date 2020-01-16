<?php

/**
 * @package IsAuthenticatedAuth
 * @author  Moorexa Assist
 */

class IsAuthenticatedAuth extends Authenticate
{
    // allow specific requests
    public $allow = ['app/login']; 

    // allow all except this requests
    public $allowAllExcept = [];

    // #code here.
    public function loginFirst()
    {
        
    }

}