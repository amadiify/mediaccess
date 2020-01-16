<?php

/**
 * @package Mustlogin Middleware
 * @author  Moorexa inc.
 */

class Mustlogin
{
    /**
     *
     * request waiting, call render to push view to browser.
     *
     * @return void
     **/
    function request($render)
    {
        // call render function
        $auth = new Authenticate();

        // call route! Applies Globally.
        if ($auth->apply('request@isAuthorized'))
        {
            $header = Moorexa\Plugins::headers();

            if ($header->has('x-medi-token', $token))
            {
                // verify token
                $auth = $auth->apply('request.auth');

                if ($auth->tokenValid($token, null, $errors))
                {
                    // call endpoint
                    $render();
                }
                else
                {
                    $header->json([
                        'status' => 'error',
                        'message' => $errors[0]
                    ]);
                }
            }
            else
            {
                $header->json([
                    'status' => 'error',
                    'message' => 'x-medi-token missing in request header'
                ]);
            }
        }
    }

    /**
     *
     * request closed, render called.
     *
     * @return void
     **/
    function requestClosed()
    {
        // what would you like to do here?
    }

    // #cool stuffs down here
}