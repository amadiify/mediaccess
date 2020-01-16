<?php
namespace Moorexa;

use Moorexa\DB;
use Moorexa\Event;
use WekiWork\Fileio;
use Bootstrap\Alert;
use Messaging\Mail;
use WekiWork\Http;
use Moorexa\HttpPost as Post;

/**
 * My model class auto generated.
 *
 *@package	My Model
 *@author  	Moorexa <www.moorexa.com>
 **/

class My extends Model
{
    // profile image
    public $profile_image = 'man-3.png';

    // cover image
    public $cover_image = '388882-PC5X6X-544.jpg';
    
    // has web photo
    private $hasWebPhoto = false;
    
    // account id
    public $id = 0;
    
    // account info
    public $info = null;

    // triggers
    public $triggers = [
        'orders' => ['approve' => 'get|post:approveOrder'],
        'drugs' => ['edit' => 'get|post:editDrug', 'delete' => 'get:deleteDrug']
    ];

    public function __construct()
    {
        $this->id = session()->get('account.id');
        $this->info = session()->get('account.info');

        $check = DB::web_photo()->get('accountid=?',$this->id);

        if ($check->row != 0)
        {
            $this->profile_image = image($check->profile_image, '70:70');
        }

        $this->hasWebPhoto = $check;
    }

    // approve orders
    public function approveOrder($id, Mail $mail)
    {
        $track = \orders::get('orderid=?', $id);
        
        if ($track->rows > 0)
        {
            // approve if status is pending
            if ($track->status == 'pending')
            {
                // approve
                $user = \account::get('accountid=?', $track->fromid);

                // send email
                $order = db('templates')->get('tag = ?', 'order-approved');
                $template = $order->template;
                $group = \groups::get('groupid = ?', $track->groupid);

                $service = $group->group_name;

                if (stripos($service, 'service') === false)
                {
                    $service .= ' service';
                }

                $fullname = ucwords($user->firstname . ' ' . $user->lastname);
                $date = date('F jS Y');
                $agent = \account::get('accountid = ?', $track->accountid);
                $agent = ucwords($agent->firstname . ' ' . $agent->lastname);

                // now replace
                $template = str_replace('{agent}', $agent, $template);
                $template = str_replace('{fullname}', $fullname, $template);
                $template = str_replace('{date}', $date, $template);
                $template = str_replace('{service}', $service, $template);

                $link = url('sign-in?redirectTo=my/orders');
                $template = str_replace('{link}', $link, $template);

                $mail->subject('Mediaccess')
                ->from('noreply@mediaccessng.com')
                ->to($user->email)
                ->html($template);
                //->send(); // send mail

                if ($track->update(['status' => 'success']))
                {
                    Alert::success('Order has been approved successfully.');
                }
            }
        }
    }

    // update cover and profile info
    public function updateCover($model)
    {
        //. code here
        if ($model->has('profile_image') || $model->has('cover_image'))
        {
            $check = $this->hasWebPhoto;
            
            if ($check->row == 0)
            {
                $check->insert([
                    'accountid' => $this->id,
                    'profile_image' => $this->profile_image,
                    'cover_image' => $this->cover_image
                ]);
            }

            $upload = new Fileio();
            
            $upload->from($model)
            ->upload('profile_image','cover_image')
            ->to('./pages/My/Uploads/', function($key, $path) use (&$check){
                $check->update([$key => $path], 'accountid=?', $this->id);
                $this->{$key} = image($path, '70:70');
            }); 

            $success = false;

            foreach ($upload as $key => $info)
            {
                if ($info['code'] == 200)
                {
                    $success = true;
                    break;
                }
            }

            if ($success)
            {
                Alert::success('Profile Updated successfully.');
            }

            Alert::error('Failed to update profile information. Please try again');
            
        }
    }

    // update password
    public function updatePassword(&$model)
    {
        if ($model->has('old_password'))
        {
            $change = \WekiWork\Http::body($model->getData())->post('user/changepassword');
            $model->setErrors($change->json);

            if ($change->json->status == 'success')
            {
                $model->clear();
                Alert::success($change->json->message);
            }

            Alert::error($change->json->message);
        }
    }

    // reply review
    public function replyReview(HttpPost $post)
    {
        if ($post->has('reviewid', $reviewid))
        {
            $post->remove('reviewid');
            $post->set('date_created', date('Y-m-d g:i:s a'));
            // add comment
            \reviews::insert($post->data());
            // update 
            \reviews::update(['replied' => 1], 'reviewid=?', $reviewid);
            Alert::success('Your reply has been published successfully');
        }
    }

    // edit drug
    public function editDrug($id, HttpPost $post)
    {
        $model = $this->formRule;
        $drug = db('drugs');

        if ($model->has('drug_name'))
        {
            $update = $drug->update($model->getData())->where('drugid=?', $id);

            if ($update->ok)
            {
                if ($post->has('drug_image'))
                {
                    $image = $post->file('drug_image');

                    if (is_object($image))
                    {
                        if ($image->error == 0)
                        {
                            $destination = MY_PATH . 'Uploads/' . md5($id . $image->name) .'.'. extension($image->name);

                            if (move_uploaded_file($image->tmp_name, $destination))
                            {
                                $drugImage = db('drug_images');
                                $drugImage->get('drugid=?', $id, function($get)
                                {
                                    $get->obj(function($row){
                                        if (file_exists($row->image))
                                        {
                                            @unlink($row->image);
                                        }
                                    });
                                })
                                ->delete('drugid=?', $id) // delete all
                                ->insert(['drugid' => $id, 'image' => $destination]); // insert new
                            }
                        }
                    }
                }

                Alert::success('Drug information updated successfully.');
                Event::emit('edit.sent');
            }

            Alert::error('Failed! Please try again');
        }

        // get drug info
        $drug = $drug->get('drugid=?', $id);

        if ($drug->rows > 0)
        {
            $this->formRule->pushObject($drug);
        }
    }

    // delete drug
    public function deleteDrug($id)
    {
        $del = db('drugs')->delete('drugid=? and inorder=?', $id, 0);
        
        if ($del->ok)
        {
            $drugImage = db('drug_images');
            $drugImage->get('drugid=?', $id, function($get)
            {
                $get->obj(function($row){
                    if (file_exists($row->image))
                    {
                        @unlink($row->image);
                    }
                });
            })
            ->delete('drugid=?', $id);

            return true;
        }

        return false;
    }

    // add drug
    public function postCreateDrugs(HttpPost $post)
    {
        $model = &$this->formRule;

        if ($model->has('drug_name'))
        {
            // check pharmacyid
            if ($model->pharmacyid != 0)
            {
                $model->pop('drug_image');
                $add = Http::body($model->getData())->put('drug');
                $model->setErrors($add->json);

                if ($add->json->status == 'success')
                {
                    $image = $post->file('drug_image');

                    if (is_object($image))
                    {
                        if (strlen($image->name) > 1)
                        {
                            // get id
                            $drug = db('drugs')->get($model->getData());

                            if ($drug->rows > 0)
                            {
                                if ($image->error == 0)
                                {
                                    $id = $drug->drugid;
                                    $destination = MY_PATH . 'Uploads/' . md5($id . $image->name) .'.'. extension($image->name);

                                    if (move_uploaded_file($image->tmp_name, $destination))
                                    {
                                        $drugImage = db('drug_images');
                                        $drugImage->insert(['drugid' => $id, 'image' => $destination])->go();
                                    }
                                }
                            }
                        }
                    }

                    // clear model
                    $model->clear();

                    Alert::success($add->json->message);
                }

                Alert::error($add->json->message);
            }

            Alert::error('You have not updated your pharmacy information.');
        }
    }

    // get drug as json
    public function getDrugAsJson($drug, $location)
    {
        // get model
        $model = $this->model('App/Drugs');

        // get rows
        $rows = [];
        
        // get drug row
        $drug = $model->getDrugInfo($drug, $rows);

        $json = [
            'image' => $rows[0]->image,
            'title' => $rows[0]->drug->drug_name,
            'description' => $rows[0]->drug->description,
            'type' => $rows[0]->pharmacytype,
            'location' => $location,
            'pharmacy' => $rows[0]->pharmacy
        ];

        $json = str_replace('"',"¶", json_encode($json));
        return $json;
    }

    // prescribe drug
    public function postPrescribe(Post $post)
    {
        // get drugid
        $drugs = $post->drug;

        // get notes
        $notes = $post->note;

        // json data
        $json = [];

        // push drug 
        foreach ($drugs as $index => $drugid)
        {
            $json[] = ['drugid' => $drugid, 'note' => $notes[$index]];
        }

        // encode json
        $json = json_encode($json);

        // prescribe
        $prescribe = Http::body([
            'patientid' => $post->patientid,
            'drugs' => $json
        ])->post('doctor/prescribe');

        // add notification
        \Medi\Data::addNotification('drugs', $post->patientid);

        // emit prescribed event
        Event::emit('prescribed', $prescribe->json);
    }
}