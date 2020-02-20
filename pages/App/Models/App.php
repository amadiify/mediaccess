<?php
namespace Moorexa;

use WekiWork\Http;

class App extends Model
{
    public function postRequest(HttpPost $post)
    {
        if ($post->service_type != '')
        {
            // get ref
            $getref = Http::get('payment/ref')->json;
            $ref = $getref->ref;

            // create payment.
            $groups = \account_groups::get('group_name =?', $post->service_type);
            $amount = $groups->service_fee; // amount 
            $narration = 'Paying for '. $post->service_type . ' consultation to '. $post->consultant; // narration

            $body = [
                'txref' => $ref,
                'amount' => $amount,
                'narration' => $narration
            ];

            // push request
            $create = Http::body($body)->put('payment')->json;

            if ($create->status == 'success')
            {
                $body['accountid'] = $post->accountid;
                $info = session()->get('account.info');
                $body['email'] = $info->email;
                $body['phone'] = $info->telephone;
                $body['groupid'] = $groups->groupid;
                $body['message'] = strip_tags($post->request_message);
                
                session()->set('user.payment', $body);
                $this->redir('make-payment');
            }
        }
    }
}