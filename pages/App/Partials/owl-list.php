<?php

class OwlList extends Moorexa\Model
{
    public $list = [];

    public function __construct($option=null)
    {
        $this->list = [];

        $types = account_types::get('showpublic = 1');
        
        $types->obj(function($row) use ($option){
            // get accounr 
            account::get('accounttypeid=? and isverified = 1', $row->accounttypeid)
            ->rand()
            ->limit(0, 8)
            ->obj(function($account) use ($row, $option){
                $data = [];
                $data['account'] = $account;  
                
                $views = 0;
                \views::get('accountid=?',$account->accountid)->obj(function($e) use (&$views){
                    $views += $e->view;
                });
                $data['views'] = $views;
                $data['type'] = $row->accounttype;

                // check web_photo
                $webphoto = db('web_photo')->get('accountid=?',$account->accountid);
                if ($webphoto->row > 0)
                {
                    $data['web_photo'] = $webphoto->row();
                }
                else
                {
                    $data['web_photo'] = (object) [
                        'cover_image' => '388882-PC5X6X-544.jpg',
                        'profile_image' => 'icon/man-3.png'
                    ];
                }

                if (strlen($data['account']->about) > 10)
                {
                    if ($option === null)
                    {
                        $this->list[] = (object) $data;
                    }
                    elseif ($option == 'switch')
                    {
                        $this->model('app/about')->getTotalRating($account->accountid, $ratings, $allratings);

                        if ($allratings > 3)
                        {
                            $this->list[] = (object) $data;
                        }
                    }
                }
            });
        });
    }
}