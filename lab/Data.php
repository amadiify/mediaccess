<?php
namespace Medi;

use WekiWork\Http;
use Bootstrap\Alert;

class Data 
{
    private static $accounts = [];
    private static $endpoints = [
        'Doctor' => 'doctor/{id}',
        'Nurse' => 'nurse/{id}',
        'Hospital' => 'hospital/{id}',
        'Lab' => 'lab/{id}',
        'Pharmacy' => 'pharmacy/{id}',
        'Patient' => 'user/{id}',
        'Ambulance' => 'user/{id}'
    ];

    // get account types
    public static function getAccountTypes()
    {
        if (count(self::$accounts) == 0)
        {
            $types = db('account_types')->get();
            if ($types->rows > 0)
            {
                $types->obj(function($row){
                    self::$accounts[$row->accounttype] = $row->accounttypeid;
                });
            }
        }
    }

    // retrive data
    public static function __callStatic($type, $data)
    {
        $type = ucfirst($type);
        // get account type
        self::getAccountTypes();

        if (count($data)==0)
        {
            if (session()->has('account.id'))
            {
                $id = session()->get('account.id');
            }
            else
            {
                Alert::error('Invalid Account ID');

                return false;
            }
        }
        else
        {
            $id = $data[0];
        }

        // now check for existance
        if (self::hasType($type))
        {
            // get endpoint
            $endpoint = self::$endpoints[$type];
            // replace {id}
            $endpoint = str_replace('{id}', $id, $endpoint);

            // trigger request
            $request = Http::get($endpoint);
            $response = $request->json;

            if ($response->status == 'success')
            {
                $info = db('account')->get('accountid=?',$id);
                $response->account = $info->row();

                // get web photo
                $photo = db('web_photo')->get('accountid=?',$id);
                $response->web_photo = (object)[
                    'cover_image' => '388882-PC5X6X-544.jpg',
                    'profile_image' => 'icon/man-3.png'
                ];

                if ($photo->row == 1)
                {
                    $response->web_photo = $photo->row();
                }

                return $response;
            }

            Alert::error($response->message);
        }

        return false;
    }

    // check for type
    private static function hasType($type, &$id=null)
    {
        if (isset(self::$accounts[$type]))
        {
            // get id
            $id = self::$accounts[$type];

            return true;
        }

        return false;
    }

    // pull request
    public static function pull()
    {
        // get account type
        if (session()->has('account.info', $info))
        {
            $typeid = $info->accounttypeid;

            // get
            $account = db('account_types')->get('accounttypeid=?',$typeid);
            $name = $account->accounttype;

            $pull = self::{$name}($info->accountid);

            return $pull;
        }
        else
        {
            redirect('app/sign-in');
        }

        return false;
    }

    // watch request
    public function watchQueryRequest(string $table, \Moorexa\DB $query, $request)
    {
        if ($request->bindHas('pharmacyid'))
        {
            // $query->like('')
            //$request->lock();
        }
    }

    // add notification
    public static function addNotification(string $target, int $accountid)
    {
        db('notification')->insert([
            'target' => $target,
            'accountid' => $accountid,
            'hash' => md5(time()),
            'seen' => 0
        ])->go();
    }
}