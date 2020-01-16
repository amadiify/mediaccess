<?php

use Moorexa\Plugins as Plugin;

/**
 * @package RequestAuth
 * @author  Moorexa Assist
 */

class RequestAuth extends Authenticate
{
    // allow specific requests
    public $allow = []; 

    // allow all except this requests
    public $allowAllExcept = [];

    // platform
    private $platform = null;

    // #code here.
    public function isAuthorized(&$platform=null)
    {
        $headers = Plugin::headers();

        if ($headers->has('x-authorize-token', $token))
        {
            // check if token sent is valid
            $isvalid = Moorexa\DB::Platforms('token = ?', $token);

            if ($isvalid->rows == 1)
            {
                if ($isvalid->platform == 'web-admin')
                {
                    ApiManager::$storage['accounttypeid'] = 8;
                }

                $this->platform = $isvalid->platform;

                // set platform
                $platform = $this->platform;

                // good
                return true;
            }

        }
        else
        {
            $headers->json('error', 'Authorize token missing in header');
        }

        return false;
    }

    // verify token
    public function tokenValid($token, $uri=[], &$errors=[], &$id=0)
    {
        switch (gettype($uri))
        {
            case 'array':
                $id = intval(end($uri));
            break;

            default:
                $id = intval($uri);
        }

        $track = Moorexa\DB::table('account_track');

        if (is_int($id) && $id !== false && $id > 0)
        {
            // match token for user
            // is user in ?
            $user = $track->get('accountid = ? and isloggedin = ?', $id, 1);

            if ($user->rows == 1)
            {
                // check token
                if ($token == $user->session_token)
                {
                    // check expire time
                    $expire = $user->token_expires;
                    
                    $dt = new \DateTime();
                    $dt->setTimezone((new \DateTimeZone('Africa/Lagos')));

                    // expire date
                    $dt2 = new \DateTime();
                    $dt2->setTimestamp($expire);

                    // if current is less than expire then we are good!
                    if ($dt2->diff($dt)->i > 0)
                    {
                        $dt->add((new \DateInterval('P0Y0M0DT0H5M6S')));

                        // add more time
                        $new = $dt->getTimestamp();
                        // push user
                        ApiManager::$storage['user'] = Moorexa\DB::table('account')->get('accountid = ?', $id);

                        // return true
                        return true;
                    }
                    else
                    {
                        $errors[] = 'Session expired.';
                    }
                }
                else
                {
                    $errors[] = 'Invalid x-medi-token for user '.$user->email;
                }
            }
            else
            {
                $errors[] = 'Invalid AccountID #'.$id;
            }
        }
        else
        {
            // just verify that token is associated with a user and is logged in.
            $user = $track->get('session_token = ? and isloggedin = ?', $token, 1);

            if ($user->rows == 1)
            {
                // set id
                $id = $user->accountid;

                // check expire time
                $expire = $user->token_expires;
                    
                $dt = new \DateTime();
                $dt->setTimezone((new \DateTimeZone('Africa/Lagos')));

                // expire date
                $dt2 = new \DateTime();
                $dt2->setTimestamp($expire);

                // if current is less than expire then we are good!
                if ($dt2->diff($dt)->i > 0)
                {
                    $dt->add((new \DateInterval('P0Y0M0DT0H5M6S')));

                    // add more time
                    $new = $dt->getTimestamp();

                    // add more time
                    $track->update(['token_expires' => $new], 'accountid = ?', $id);

                    // push user
                    ApiManager::$storage['user'] = Moorexa\DB::table('account')->get('accountid = ?', $id);

                    // return true
                    return true;
                }
                else
                {
                    $errors[] = 'Session expired.';
                }
            }
            else
            {
                $errors[] = 'Invalid x-medi-token. Authorization failed.';
            }

        }
        
        return false;
    }

    // is administrator
    public function isAdmin()
    {
        $header = Plugin::headers();

        if ($this->isAuthorized())
        {
            if ($this->platform == 'web-admin')
            {
                if ($header->has('x-medi-token', $token))
                {
                    // verify token
                    $track = Moorexa\DB::table('account_track');
                
                    // just verify that token is associated with a user and is logged in.
                    $user = $track->get('session_token = ? and isloggedin = ?', $token, 1);

                    if ($user->rows == 1)
                    {
                        // get roleid
                        $account = Moorexa\DB::table('account')->get('accountid = ?', $user->accountid);
                        
                        if ($account->accounttypeid == 8)
                        {
                            // set id
                            $id = $user->accountid;
                            
                            // check expire time
                            $expire = $user->token_expires;
                                
                            $dt = new \DateTime();
                            $dt->setTimezone((new \DateTimeZone('Africa/Lagos')));

                            // expire date
                            $dt2 = new \DateTime();
                            $dt2->setTimestamp($expire);

                            // if current is less than expire then we are good!
                            if ($dt2->diff($dt)->i > 0)
                            {
                                $dt->add((new \DateInterval('P0Y0M0DT0H5M6S')));

                                // add more time
                                $new = $dt->getTimestamp();
                                
                                // push user
                                ApiManager::$storage['user'] = Moorexa\DB::table('account')->get('accountid = ?', $id);

                                // return true
                                return true;
                            }
                            else
                            {
                                $header->json([
                                    'status' => 'error',
                                    'message' => 'Session expired.'
                                ]);
                            }
                            
                        }
                        else
                        {
                            $header->json([
                                'status' => 'error',
                                'message' => 'Access denied. Your access level is restricted to enter this section of GTELESAVE.'
                            ]);
                        }
                    }
                    else
                    {
                        $header->json([
                            'status' => 'error',
                            'message' => 'Invalid x-medi-token. Authorization failed.'
                        ]);
                    }
                }
                else
                {
                    $header->json([
                        'status' => 'error',
                        'message' => 'x-medi-token missing in request headers. Authorization failed.'
                    ]);
                }
            }
            else
            {
                $header->json([
                    'status' => 'error',
                    'message' => 'Access denied. Authorization failed.'
                ]);
            }
        }
        else
        {
            $header->json([
                'status' => 'error',
                'message' => 'Authorize token missing in header or not valid'
            ]);
        }

        return false;
    }
}