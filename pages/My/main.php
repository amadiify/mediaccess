<?php
use Moorexa\Model;
use Moorexa\Packages as Package;
use Moorexa\Controller;
use Bootstrap\Alert;
use WekiWork\Http;
use Medi\Data;
use Moorexa\Provider;
use Moorexa\HttpGet;
use Moorexa\Event;

/**
 * Documentation for My Page can be found in My/readme.txt
 *
 *@package	My Page
 *@author  	Moorexa <www.moorexa.com>
 **/

class My extends Controller
{
	/**
    * My/home wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function home($action)
	{
        switch ($action)
        {
            case 'switch':
                $account = account::update(['isavaliable'=>uri(4)], 'accountid=?', uri(3))->get();
                session()->set('account.info', $account->row());
            break;
        }

        // get this week monday
        $monday = date('Y-m-d', strtotime('this week monday'));
        $sunday = date('Y-m-d', strtotime('this week sunday'));

        // build points
        $points = [0,0,0,0,0,0,0];

        // target
        $target = 'accountid';

        // get data
        if (Provider::permission()->is('Patient'))
        {
            $target = 'fromid';
        }

        if (Provider::permission()->is('Pharmacy'))
        {
            // get points from cart for pharmacy
            $this->provider->getPointsFromPharmacyCart($monday, $sunday, $points);
        }

        // get points from orders
        $this->provider->getPointsFromOrders($target, $monday, $sunday, $points);

        // get points from cart
        $this->provider->getPointsFromUserCart($monday, $sunday, $points);
        
        dropbox('points', $points);

        $thisWeek = array_reduce($points, function($left, $right){ return $left + $right; });

        $this->requirejs('php-vars.js', 'before main.js');
 
		$this->render('home', ['thisWeek' => Wrapper::money($thisWeek)]);
	}
	/**
    * My/logout wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function logout()
	{
        session()->drop('account.info', 'account.id', 'account.token');
        $previous = Moorexa\Route::previous();
        
        if ($previous != false && $previous->controller != 'My')
        {
            $this->redir($previous->link);
        }

        $this->redir('app/sign-in', ['message' => 'You have signed out successfully. Please come back soon.']);
	}
	/**
    * My/update-doctor wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function updateDoctor()
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        $view = 'updatedoctor';

        $this->model->updateCover($model);

        if ($model->has('home_address'))
        {
            $update = Http::multipart()->post('doctor')->json;
            $model->setErrors($update);

            if ($update->status == 'success')
            {
                Alert::success($update->message);
                $view = 'home';
                $this->changeState('home');
            }

            Alert::error($update->message);
        }

        Alert::warning('You need to update your account information to continue.');

		$this->render($view, ['model'=>$model]);
	}
	/**
    * My/profile-settings wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function profileSettings()
	{
        $model = createModelRule('account', function($body){
            $body->accountid = $this->model->id;
            $body->allow_form_input();
        });

        $this->model->updateCover($model);

        // change password
        $this->model->updatePassword($model);

        if ($model->has('firstname'))
        {
            $update = $model->update();

            if ($update)
            {
                Alert::success('Profile information updated successfully.');
            }

            Alert::error('Could not update profile information.');
        }

        $account = session()->get('account.info');

        if ($model->has('present_place_of_work'))
        {
            $path = 'doctor';

            switch (intval($account->accounttypeid))
            {
                case 2:
                    $path = 'nurse';
                break;
            }

            $update = Http::body($model->getData())->post($path);
            $model->setErrors($update->json);

            if ($update->json->status == 'success')
            {
                Alert::success($update->json->message);
            }

            Alert::error('Could not update work information.');
        }

        $data = Data::pull();

        // get account type
        $acctype = db('account_types')->get('accounttypeid=?',$data->account->accounttypeid);
        $this->acctype = strtolower($acctype->accounttype) . '-form';

        // check if it exists
        $path = MY_PARTIAL . $this->acctype . '.html';

        $this->hasTypeForm = true;

        if (!file_exists($path))
        {
            $this->hasTypeForm = false;
        }

		$this->render('profilesettings', ['model'=>$model,'data'=>$data]);
	}
	/**
    * My/update-nurse wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function updateNurse()
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        $view = 'updatenurse';

        $this->model->updateCover($model);

        if ($model->has('present_place_of_work'))
        {
            $update = Http::multipart()->post('nurse')->json;
            $model->setErrors($update);

            if ($update->status == 'success')
            {
                Alert::success($update->message);
                $view = 'home';
                $this->changeState('home');
            }

            Alert::error($update->message);
        }

        Alert::warning('You need to update your account information to continue.');

		$this->render($view, ['model'=>$model]);
	}
	/**
    * My/account-info wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function accountInfo()
	{
		$this->render('accountinfo');
	}
	/**
    * My/add-wishlist wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function addWishlist($id, $type)
	{
        $model = createModelRule('wishlist', function($body){
            $body->addedby = session()->get('account.id');
        });

        $model->id = $id;
        $model->type = $type;

        if ($model->id != $model->addedby)
        {
            $create = $model->create();

            if ($create)
            {
                Alert::success('Added to wishlist successfully.');
            }
        }

        $model = Model::AppList('\App');
        $this->list = $model->loadSingleData('get'.ucwords($type), $id);

        Alert::error('Could not add to wishlist.');

		$this->render('addwishlist');
	}
	/**
    * My/remove-wishlist wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function removeWishlist($id, $type)
	{
        $model = createModelRule('wishlist', function($body){
            $body->addedby = session()->get('account.id');
        });

        $model->id = $id;
        $model->type = $type;
        $model->identity('addedby');

        $this->changeState('wishlist');
        
        if ($model->remove())
        {
            Alert::success('Removed from wishlist successfully.');

            $this->wishlist();
        }

		$this->render('wishlist');
	}
	/**
    * My/wishlist wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function wishlist()
	{
        // get all items
        $wishlist = db('wishlist')->get('addedby=?',$this->model->id);

        $list = [];
        $model = Model::AppList('\App');

        $wishlist->obj(function($row) use (&$list, &$model){
            $line = $model->loadSingleData('get'.ucwords($row->type), $row->id);
            $line[0]->type = $row->type;
            $list[] = $line;
        });
        
        $newlist = [];

        if (count($list) == 0)
        {
            Alert::info('You have noting on your wishlist presently!');

            $this->list = [];
        }
        else
        {
            foreach ($list as $index => $data)
            {
                $newlist[] = $data[0];
            }

            $this->list = $newlist;
        }

		$this->render('wishlist');
	}
	/**
    * My/gallery wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function gallery(Moorexa\HttpPost $post, Moorexa\HttpGet $get)
	{
        if (!$post->isEmpty())
        {
            $file = $post->files['upload'];
            $allowed = array_flip(['png','jpg','jpeng','gif']);
            $status = ['success' => 0, 'failed' => 0];

            array_walk($file['name'], function($name, $index) use ($allowed, &$file, &$status){
                $extension = extension($name);
                $size = $file['size'][$index];
                $size = convertToReadableSize($size, $base);
                if ($base == 'kb' || $base == 'mb')
                {
                    $upload = true;

                    if ($base == 'mb')
                    {
                        if (intval($size[0]) > 2)
                        {
                            $upload = false;
                        }
                    }

                    if ($upload)
                    {
                        $destination = MY_PATH . 'Uploads/' . md5($this->model->id . $name) . '.' . $extension;
                        if (move_uploaded_file($file['tmp_name'][$index], $destination))
                        {
                            $upload = \photo_gallery::insert(['accountid'=>$this->model->id, 'photo'=>$destination]);
                            
                            if ($upload->ok)
                            {
                                $status['success'] += 1;
                            }
                            else
                            {
                                $upload = false;
                            }

                        }
                        else
                        {
                            $upload = false;
                        }
                    }
                    
                    if (!$upload)
                    {
                        $status['failed'] += 1;
                    }
                }
            });

            $c = $status['success'];
            $f = $status['failed'];

            if ($c > 0)
            {
                Alert::success('('.$c.') photo[s] uploaded successfully, ('.$f.') failed.');
            }
            else
            {
                Alert::error('('.$f.') failed, ('.$c.') was uploaded');
            }
        }
        else
        {   
            if ($get->has('del'))
            {
                $id = $get->del;
                // get path
                $photo = photo_gallery::get('photoid=?', $id);
                unlink($photo->photo);
                $del = $photo->pop();

                if ($del->ok)
                {
                    Alert::success('Photo deleted successfully');
                }
                
                Alert::error('Failed to delete photo, please try again');
            }
        }

		$this->render('gallery');
	}
	/**
    * My/reviews wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function reviews()
	{
		$this->render('reviews');
	}
	/**
    * My/update-hospital wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function updateHospital()
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
            $body->specialization = 'surgery';
        });

        if ($model->has('hospital_name'))
        {
            $cert = $model->pop('cac_certificate');
            $specialization = $model->pop('specialization');

            // post
            $post = Http::attach(['cac_certificate' => $cert])->body($model->getData())->post('hospital');
            $model->setErrors($post->json);

            if ($post->json->status == 'success')
            {
                $info = Http::get('hospital');
                $json = $info->json;
                $id = $json->data->hospitalid;

                // add specialization
                $spec = explode(',', $specialization);
                foreach ($spec as $i => $specf)
                {
                    $data = ['hospitalid' => $id, 'specialization' => $specf];
                    hospital_specializations::insert($data);
                }

                Alert::success($post->json->message);
                $this->switchState('my','home');
            }

            $model->specialization = $specialization;

        }
        else
        {
            $info = Http::get('hospital');
            $json = $info->json;

            if (is_object($json) && $json->status == 'success')
            {
                $data = $json->data;
                $model->hospital_name = $data->hospital_name;
            }
        }

		$this->render('updatehospital', ['model' => $model]);
	}
	/**
    * My/update-pharmacy wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function updatePharmacy()
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        if ($model->has('pharmacy_name'))
        {
            $cert = $model->pop('cac_certificate');
            $specialization = $model->pop('specialization');

            // post
            $post = Http::attach(['cac_certificate' => $cert])->body($model->getData())->post('pharmacy');
            $model->setErrors($post->json);

            if ($post->json->status == 'success')
            {
                $info = Http::get('pharmacy');
                $json = $info->json;
                $id = $json->data->pharmacyid;

                // add specialization
                $spec = explode(',', $specialization);
                foreach ($spec as $i => $specf)
                {
                    $type = pharmacytypes::get('pharmacytype=?', $specf);
                    if ($type->rows > 0)
                    {
                        $data = ['pharmacyid' => $id, 'pharmacytypeid' => $type->pharmacytypeid];
                        pharmacy_type_list::insert($data);
                    }
                }

                Alert::success($post->json->message);
                $this->switchState('my','home');
            }

            $model->specialization = $specialization;
        }
        else
        {
            $specialization = [];
            pharmacytypes::get()->obj(function($r) use (&$specialization){
                $specialization[] = $r->pharmacytype;
            });
            $model->specialization = implode(',', $specialization);
        }

		$this->render('updatepharmacy', ['model' => $model]);
	}
	/**
    * My/payment wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function payment($status, $ref)
	{
        if ($status != null && session()->has('checkout'))
        {
            $checkout = session()->get('checkout');

            $ref = $checkout['ref'];
            $cart = session()->get('user.cart');

            unset($checkout['ref'], $checkout['checkout'], $checkout['amount']);

            // update payment
            $data = [
                'txref' => $ref,
                'status' => $status
            ];

            Http::body($data)->post('payment');

            // add to cart
            foreach ($cart as $cartInfo)
            {
                $data = [
                    'pharmacyid' => $cartInfo['pharmacyid'],
                    'accountid' => session()->get('account.id'),
                    'drugid' => $cartInfo['drugid'],
                    'quantity' => $cartInfo['quantity'],
                    'txref' => $ref,
                    'shipping' => $cartInfo['shipping'],
                    'amount' => ($cartInfo['price'] + $cartInfo['servicefee'])
                ];

                if (db('cart')->insert($data)->ok)
                {
                    // get pharmacyinfo
                    $pharmacy  = Query::getPharmacy($cartInfo['pharmacyid']);

                    // add notification
                    Medi\Data::addNotification('orders', $pharmacy->accountid);
                }
            }

            // add to orderdetails
            $json = json_encode($checkout);

            db('cartOrderDetails')->insert(['txref' => $ref, 'orderDetails' => $json]);

            Alert::success('Your order has been made successfully. You will be contacted shortly.');

            session()->drop('user.cart', 'checkout');

            $this->render('payment');

            return false;
        }

        if ($status != null && session()->has('user.payment'))
        {
            $payment = toObject(session()->get('user.payment'));

            $data = [
                'txref' => $payment->txref,
                'status' => $status
            ];

            Http::body($data)->post('payment');

            if ($status == 'success')
            {
                $account = account::get('accountid=?', $payment->accountid);
                $type = account_types::get('accounttypeid = ?', $account->accounttypeid);

                // get payment id
                $payments = payments::get('txref = ?', $payment->txref);

                if ($payments->rows > 0)
                {
                    $paymentid = $payments->paymentid;

                    $endpoint = 'order/' . strtolower($type->accounttype);

                    $body = [
                        'accountid' => $payment->accountid,
                        'paymentid' => $paymentid,
                        'groupid' => $payment->groupid,
                        'remark' => $payment->message
                    ];

                    $order = Http::body($body)->put($endpoint);

                    // add notification
                    Medi\Data::addNotification('orders', $payment->accountid);

                    // show success
                    Alert::success($order->json->message);
                }
            }
            else {
                Alert::error('Payment '.$status.', please try again later');
            }

            session()->drop('user.payment');
        }

		$this->render('payment');
	}
	/**
    * My/orders wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function orders($orderid, $action, HttpGet $get)
	{   
        $view = 'orders';

        // manage tracking
        if ($get->has('tracking', $tracking) && $orderid == null)
        {
            if ($tracking == 'complete' && $get->has('ref', $ref))
            {
                // get cartid
                $idPos = strpos($ref, ':');

                if ($idPos !== false)
                {
                    $getCartId = intval(substr($ref, $idPos+1));
                    $txref = substr($ref, 0, $idPos);

                    if (Query::cartOrderComplete($txref, $getCartId)->ok)
                    {
                        Alert::success('Your order was marked complete. Thank you for your purchase.');
                    }
                }
            }
        }

        if ($orderid !== null && is_numeric($orderid))
        {
            if ($action == 'close')
            {
                $close = orders::update(['dateclosed' => date('Y-m-d g:i:s')])
                ->where('orderid = ?', $orderid)
                ->go();

                if ($close->ok)
                {
                    Alert::success('Order was closed successfully');
                }
            }

            // get order information
            $order = orders::get('orderid = ?', $orderid);
            if ($order->rows > 0)
            {
                $account = account::get('accountid = ?', $order->fromid);
                $this->status = 'opened';

                if ($order->dateclosed != '')
                {
                    $this->status = 'closed';
                }

                // get service type
                $group = groups::get('groupid = ?', $order->groupid);
                $this->service = ucfirst($group->group_name);

                // get account type
                $type = account_types::get('accounttypeid = ?', $account->accounttypeid);
                $this->type = ucfirst($type->accounttype);

                // send vars
                $this->account = $account;
                $this->order = $order;

                // get amount
                $payment = payments::get('paymentid = ?', $order->paymentid);
                $this->amount = $payment->amount;
                
                $view = 'order';
            }
        }

        if ($orderid !== null && is_string($orderid))
        {
            if ($orderid == 'cart-order')
            {
                $cartid = $action;

                // check if order exists
                $cartInfo = Query::getCartInfo($cartid);

                if ($cartInfo->rows > 0)
                {
                    $view = $orderid;
                    $this->txref = $cartInfo->txref;
                }
            }
        }

		$this->render($view);
	}
	/**
    * My/conversation wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function conversation($orderid)
	{
        // get all orders
        $orders = \orders::get();
        // all orders
        $allorders = [];

        $orders->obj(function($row) use (&$allorders){
            $allorders[] = $row->orderid;
        });

        $this->allorders = implode(',', $allorders);

        // read chats
        $chats = db('chats')->get('orderid=?', $orderid);

        $allchats = [];

        if ($chats->rows > 0)
        {
            $chats->obj(function($row) use (&$allchats){
                $id = session()->get('account.id');
                $account =  \account::get('accountid=?', $row->accountid);
                $fullname = ucwords($account->firstname .' '. $account->lastname);
                $position = 'right';

                if ($row->accountid != $id)
                {
                    // pull right
                    $position = 'left';
                }

                $media = [];
                $media[] = '<li class="media">';
                $media[] = '<span class="chatAuthor pull-'.$position.'">'.$fullname.'</span>';
                $media[] = '<span class="pull-'.$position.'"><div class="media-object glyphicon glyphicon-user" style="font-size: 24px; width: 24px; height: 24px;" title="'.$fullname.'"></div></span>';
                $media[] = '<span class="chatText pull-'.$position.'">'.$row->message.'</span>';
                $media[] = '</li>';

                $allchats[] = implode("\n", $media);
            });
        }

        $this->allchats = $allchats;

		$this->render('conversation');
	}
	/**
    * My/drugs wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function drugs($category, $drugName, HttpGet $get)
	{
        $model = $this->formRule;
        $drugPartial = 'drug-categories';

        Moorexa\Event::on('edit.sent', function() use ($model){
            $model->clear();
            $this->changeState('drugs');
        });

        $this->modelAction('deleteDrug', function(bool $status) : void
        {
            if ($status)
            {
                $this->changeState('drugs');
                Alert::success('Drug deleted successfully!');
            }

            Alert::error('Failed to delete drug. Please try again');
        });

        $drugs = null;

        if (Provider::permission()->is('Pharmacy'))
        {
            // get drugs 
            $drugs = Http::get('drug');
            $drugs = $drugs->json;
        }

        // is patient
        if (Provider::permission()->is('Patient'))
        {
            $this->requirecss('prescribe.css');
        }

        //search result found
        $foundSearch = false;

        // check if $category is valid
        if ($category !== null)
        {
            $isCategory = Query::getPharmacyTypeId($category);

            $drugs = null;

            if ($isCategory->rows > 0)
            {
                $drugPartial = 'category-drugs';
                $drugs = Query::getPharmacyTypeDrugs($isCategory->pharmacytypeid);
                if ($drugs->rows == 0)
                {
                    $drugs = null;
                }

                if ($get->has('s', $search))
                {
                    // check drugs within current category
                    $getDrugs = Query::getDrugsInCategory($search, $isCategory->pharmacytypeid);

                    if ($getDrugs->rows > 0)
                    {
                        $drugs = $getDrugs;
                        $foundSearch = true;
                    }
                }
            }
        }

        if ($get->has('s', $search) && !$foundSearch)
        {
            // check for search results globally
            $getDrugs = Query::getDrugsIfGlobal($search);

            if ($getDrugs->rows > 0)
            {
                $drugs = $getDrugs;
                $drugPartial = 'show-drugs';
                $this->set('get', $get);
            }
            else
            {
                Alert::error('No search result for "'.$search.'"');
            }
        }

        // drug added
        if ($drugName !== null)
        {
            $drugList = session()->get('drug.list');
            if ($drugList == false)
            {
                $drugList = [];
            }   

            if (!isset($drugList[$drugName]))
            {
                Alert::success('Drug added to prescribtion list.');
            }

            $drugList[$drugName] = 1;
            session()->set('drug.list', $drugList);
        }

		$this->render('drugs', ['model' => $model, 'drugs' => $drugs, 'partial' => $drugPartial]);
	}
	/**
    * My/prescribe wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function prescribe($action, $drugName)
	{
        $drugList = session()->get('drug.list');

        if ($drugList === false)
        {
            $drugList = [];
        }

        switch (strtolower($action))
        {
            case 'remove':
                if (isset($drugList[$drugName]))
                {
                    Alert::success('Drug "'.$drugName.'" has been removed from the list.');

                    unset($drugList[$drugName]);

                    // drug list
                    session()->set('drug.list', $drugList);
                }
            break;
        }

        Event::on('prescribed', function($response) use (&$drugList)
        {
            if ($response->status == 'success')
            {
                Alert::success($response->message);
                $drugList = [];
                session()->set('drug.list', []);
            }

            Alert::error($response->message);
        });

        // get doctorid
        $doctor = Query::getDoctorInfo($this->id);

        // get prescriptions
        $prescriptions = Query::getDoctorPrescribtions($doctor->doctorid);

		$this->render('prescribe', ['list' => $drugList, 'prescriptions' => $prescriptions]);
	}
}
// END class