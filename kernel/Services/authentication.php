<?php

$kernel->authentication(function(Authenticate $auth)
{
    // load authentication handlers
    $auth->apply('isAuthenticated.auth@loginFirst');
});