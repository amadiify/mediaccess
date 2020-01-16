<?php

/**
 * My Provider. Gets autoloaded with class
 * @package My provider
 */

class MyProvider extends My
{
    /**
     * @method Boot startup 
     * This method would be called upon startup
     */
    public $fullname;
     
    public function boot($next)
    {
       $this->loadProvider('App', $this);
       $this->requirejs('bootstrap/bootstrap4-toggle.min.js');
       $this->requirecss('bootstrap/bootstrap-toggle.min.css');
       $this->requirejs('http.js', 'before My.js');

       $info = session()->get('account.info');

       if (is_object($info))
       {
         $this->firstname = ucwords($info->lastname . ' ' . $info->firstname);
       }
       else
       {
           $this->redir('app/sign-in');
       }

       Moorexa\Rexa::preload('alert');

       // register notification count directive
       Moorexa\Rexa::directive('notification', [$this, 'notificationDirective']);

       WekiWork\Http::onReadyStateChange(function($req, $response, $json){

            if (is_object($json) && $response->json->status == 'error')
            {
                if (isset($response->json->message) && $response->json->message == 'Session expired.')
                {
                    $this->redir('app/sign-in', ['error_message' => 'Session Expired.']);
                }
            }
       });

       Moorexa\Provider::permission();

       // call route! Applies Globally.
       $next();
    }

    /**
     * @method onHomeInit  
     * This method would be called upon route request to My/home
     */
    public function onHomeInit($next)
    {
        // route passed!
        $next();
    }

    public function onProfileSettingsInit($next)
    {
        $this->requirejs('bootstrap-tagsinput.js')
        ->requirecss('bootstrap-tagsinput.css');

        $next();
    }

    public function onUpdateHospitalInit($next)
    {
        $this->requirejs('bootstrap-tagsinput.js')
        ->requirecss('bootstrap-tagsinput.css');

        $next();
    }

    public function onUpdatePharmacyInit($next)
    {
        $this->requirejs('bootstrap-tagsinput.js')
        ->requirecss('bootstrap-tagsinput.css');

        $next();
    }
    // you can register more init methods for your view models.
    public function onConversationInit($next)
    {
        $this->requirecss('mgVideoChat/mgVideoChat-1.15.0.css')
        ->requirejs('mgVideoChat/mgVideoChat-1.15.0-min.js', ['deffer' => false])
        ->requirejs(function($script){
            $script->insert('mgChatinit.js');
        });

        $next();
    }

    public function onDrugsInit($next)
    {
        $this->formRule = createModelRule(function($body)
        {
            $body->allow_form_input();
            
            $id = session()->get('account.id');

            $body->accountid = $id;
            $body->pharmacyid = 0;

            // get pharmacyid
            $pharmacy = Query::getPharmacyInfo($id);

            if ($pharmacy->rows > 0)
            {
                $body->pharmacyid = $pharmacy->pharmacyid;
            }
        });

        $next();
    }

    // notificatio directive
    public function notificationDirective(string $segment)
    {
        // set badge count template
        $badgeTemplate = '<span class="badge-count">:count</span>';

        // get notifications
        $notification = db('notification')->get('target = ? and accountid = ? and seen = ?', $segment, session()->get('account.id'), 0);

        if ($notification->rows > 0)
        {

            if (uri()->view == $segment)
            {
                db('notification')
                ->update(['seen' => 1])
                ->where('target = ? and accountid = ? and seen = ?')
                ->bind($segment, session()->get('account.id'), 0)
                ->go();

                return null;
            }

            return strtr($badgeTemplate, [':count' => $notification->rows]);
        }

        return null;
    }

    // get points from orders
    public function getPointsFromOrders(string $target = 'accountid', string $monday, string $sunday, array &$points)
    {
        // get accountid
        $id = session()->get('account.id');

        // get orders
        $orders = orders::get($target .' = ? and dateissued >= ? and dateissued <= ?', $id, $monday, $sunday);

        // get rows 
        $orders->obj(function($row) use (&$points){
            // get day
            $day = intval(date('w', strtotime($row->dateissued))) - 1;
            // get payment
            $payment = $row->from('payments', 'paymentid')->get();
            // add to points
            $points[$day] += floatval($payment->amount);
        });
    }

    // get points from user cart
    public function getPointsFromUserCart(string $monday, string $sunday, &$points)
    {
        // get accountid
        $id = session()->get('account.id');

        // get drugs purchases
        $cart = db('cart')->get('accountid = ? and dateRequested >= ? and dateRequested <= ?', $id, $monday, $sunday);

        $cart->obj(function($row) use (&$points){
            // get day
            $day = intval(date('w', strtotime($row->dateRequested))) - 1;
            // add to points
            $points[$day] += floatval($row->amount);
        });
    }

    // get points from pharmacy cart
    public function getPointsFromPharmacyCart(string $monday, string $sunday, &$points)
    {
        // get accountid
        $id = session()->get('account.id');

        // get pharmacy info
        $info = Query::getPharmacyInfo($id);

        // get drugs purchases
        $cart = db('cart')->get('pharmacyid = ? and dateRequested >= ? and dateRequested <= ?', $info->pharmacyid, $monday, $sunday);

        $cart->obj(function($row) use (&$points){
            // get day
            $day = intval(date('w', strtotime($row->dateRequested))) - 1;
            // get drug
            $drug = $row->from('drugs', 'drugid')->get();
            // get amount
            $amount = floatval($drug->price) * $row->quantity;
            // add to points
            $points[$day] += floatval($amount);
        });
    }
}