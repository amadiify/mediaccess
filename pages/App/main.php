<?php
use Moorexa\Model;
use Moorexa\Packages as Package;
use Moorexa\Controller;
use WekiWork\Http;
use Bootstrap\Alert;
use Moorexa\HttpGet as Get;
use Moorexa\HttpPost as Post;
use Request\Query;
use View\Wrapper;

/**
 * Documentation for App Page can be found in App/readme.txt
 *
 *@package	App Page
 *@author  	Moorexa <www.moorexa.com>
 **/

class App extends Controller
{
    // searchValue
    public $searchValue = null;

    // searchCategory
    public $searchCategory = null;

    // has page result
    public $hasResult = false;
    
	/**
    * App/home wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param string You can catch params sent through the $_GET request
    * @return void
    **/

	public function home()
	{
		$this->render('home');
	}
	/**
    * App/list wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function list($type, $sort, Get $get)
	{
        $this->model = Model::AppList();

        $this->listTitle = 'Medical Pratitionals';
        $this->bgImage = 'Loonzorg.jpg';

        if ($get->has('filter'))
        {
            $this->searchValue = $get->search;
        }

        if ($this->has('listMessage'))
        {
            Alert::info($this->listMessage->message);
        }

        if ($type != null)
        {
            $type = ucfirst($type);

            // create search option
            $this->searchCategory = '<input type="hidden" name="category" value="'.$type.'">';

            // check if it exists
            $check = db('account_types')->find($type);

            if ($check->rows > 0)
            {
                $this->listTitle = $type;
                $id = $check->accounttypeid;
                $group = Query::getGroups($id);

                $rows = [];
                $group->obj(function($row) use (&$rows)
                {
                    $rows[] = $row;
                });
                $this->groups = $rows;
                $name = 'get'.ucfirst($type);
                $this->list = $this->model->loadData($name, $id, $sort);
                $this->listCaption = $this->model->getCaption($type, $id);

                //$this->bgImage = image($check->image, '1920:700');

            }
        }
		$this->render('list');
	}
	/**
    * App/sign-in wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function signIn(Get $get)
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        if (session()->has('account.id'))
        {
            if ($get->has('redirectTo', $to))
            {
                $this->redir($to);
            }
            else {
                $this->redir('my/home');
            }
        }

        if ($model->has('username'))
        {
            $login = Http::body($model->getData())->post('user/login');

            $model->setErrors($login->json);

            if ($login->json->status == 'success')
            {
                $json = $login->json;

                Alert::success($json->message);

                session()->set('account.token', $json->token);

                // get info 
                $info = Http::header('x-medi-token:'.$json->token)->get('user')->json;
                session()->set('account.info', $info->data);
                session()->set('account.id', $info->data->accountid);

                $id = $info->data->accounttypeid;

                $page = 'home';

                if ($id != 7)
                {
                    // check 
                    switch ($id)
                    {
                        case 1: // doctor
                        case 2: // nurse
                            // check
                            $check = Query::getAccountInfo($info->data->accountid);
                            if ($check->rows == 0)
                            {
                                $page = $id == 1 ? 'update-doctor' : 'update-nurse';
                            }
                        break;

                        case 3: // pharmacy
                            $check = Query::getPharmacyInfo($info->data->accountid);
                            if ($check->rows == 0)
                            {
                                $page = 'update-pharmacy';
                            }
                        break;

                        case 4: // hospital
                            $check = Query::getHospitalInfo($info->data->accountid);
                            if ($check->rows == 0)
                            {
                                $page = 'update-hospital';
                            }
                        break;

                        case 6: // lab
                            $check = Query::getLabInfo($info->data->accountid);
                            if ($check->rows == 0)
                            {
                                $page = 'update-lab';
                            }
                        break;
                    }
                }

                if ($get->has('redirectTo', $page) || session()->has('redirectTo', $page))
                {
                    session()->drop('redirectTo'); // drop session
                    $this->redir($page);
                }
                else
                {
                    if ($page == 'home')
                    {
                        $previous = Moorexa\Route::previous();
                        $ignore = array_flip(['home', 'register', 'register-as-patient', 'register/non-patient', 'logout']);

                        if ($previous != false && !isset($ignore[$previous->link]))
                        {
                            $this->redir($previous->link);
                        }
                    }

                    $this->redir('my/'.$page);
                }
            }

            Alert::error($login->json->message);
        }
        else
        {
            if ($this->has('error_message'))
            {
                Alert::error($this->error_message);
            }
        }

        // has redirected message
        if ($this->has('signInMessage'))
        {
            $messageSent = $this->signInMessage;
            Alert::success($messageSent->message);
        }

		$this->render('signin', ['model'=>$model]);
	}
	/**
    * App/forget-password wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function forgetPassword()
	{
        $view='forgetpassword';

        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        if ($model->has('new_password'))
        {
            $change = Http::body($model->getData())->post('user/resetPassword');
            $model->setErrors($change->json);

            $json = $change->json;

            if ($json->status == 'success')
            {
                Alert::success($json->message);
                session()->set('storage', ['username' => $model->email]);
                $view = 'register/activation';
                $this->changeState('activation');
            }

            Alert::error($json->message);
        }
		$this->render($view, ['model' => $model]);
	}
	/**
    * App/register wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function register($view, $type='Patient')
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        if ($view == 'non-patient' || $view == 'patient')
        {
            $accounttype = 7;
            
            // get accounttypeid
            $typeid = db('account_types')->find(ucfirst($type));
            
            if ($typeid->rows > 0)
            {
                $accounttype = $typeid->accounttypeid;
            }

            if ($model->has('continue'))
            {
                $model->pop('continue');
                $view = 'complete-registration';
                session()->set('storage', $model->getData());
            }

            if (session()->has('storage'))
            {
                $model->pushData(session()->get('storage'));
            }

            if ($model->has('complete'))
            {
                $model->pop('complete','iagree');

                //create account
                $create = Http::body($model->getData())->post('user/register');
                $model->setErrors($create->json);

                if ($model->isOk() || $create->json->status == 'success')
                {
                    Alert::success($create->json->message);
                    // activation
                    $view = 'register/activation';
                    $this->changeState('activation');
                }
                else
                {
                    Alert::error($create->json->message);
                    $view = 'register/full-form';
                }
            }

            // export
            $this->render($view, ['accounttypeid' => $accounttype, 'model' => $model]);
        }

		$this->render('register');
	}
	/**
    * App/activation wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function activation($view='register/activation')
	{
        $model = createModelRule(function($body){
            $body->allow_form_input();
        });

        if ($model->has('activation_code'))
        {
            $response = Http::body($model->getData())->post('user/activate')->json;

            if ($response->status == 'success')
            {
                session()->drop('storage');
                Alert::success($response->message);
                $this->changeState('signIn');
                $view = 'signin';
            }

            Alert::error($response->message);
        }

		$this->render($view, ['model' => $model]);
	}
	/**
    * App/about wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function about($who)
	{
        $who = explode('-', $who);
        $type = ucfirst($who[0]);
        $firstname = $who[1];
        $lastname = $who[2];

        $check = db('account_types')->find($type);
      
        // get account id
        $account = db('account')->find($firstname, $lastname, $check->accounttypeid);

        $this->fullname = $firstname . ' ' . $lastname;

        $data = Model::AppList();
        $data = $data->loadSingleData('get'.ucfirst($type), $account->accountid)[0];

        $this->previous = $type;
        $this->wishlist = wishlist::get('id=?', $data->account->accountid);

        $nearby = $this->model->whatsNearBy($type, $data->account);
        $this->model->description($data);

        $this->addView($account->accountid);
        $views = 0;
        \views::get('accountid=?',$account->accountid)->obj(function($e) use (&$views){
            $views += $e->view;
        });

		$this->render('about', ['data'=>$data, 'views' => $views]);
	}
	/**
    * App/contact wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function contact()
	{
        $timing = 'opened';
        $tm = date('a');
        $h = intval(date('g'));

        if ($tm == 'pm')
        {
            if ($h > 6)
            {
                $timing = 'closed';
            }
        }
        else
        {
            if ($h < 8)
            {
                $timing = 'closed';
            }
        }

		$this->render('contact', ['timing' => $timing]);
	}
	
	/**
    * App/how-it-works wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function howItWorks()
	{
		$this->render('howitworks');
	}
	/**
    * App/request wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function request($who)
	{
        $who = explode('-', $who);
        $type = ucfirst($who[0]);
        $firstname = $who[1];
        $lastname = $who[2];

        $check = db('account_types')->find($type);
      
        // get account id
        $this->account = db('account')->find($firstname, $lastname, $check->accounttypeid);
        
		$this->render('request', ['type' => $type, 'fullname' => implode(" ", array_splice($who,1))]);
	}
	/**
    * App/make-payment wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function makePayment()
	{
        if (!session()->has('user.payment'))
        {
            $this->redir('/');
        }
        
		$this->render('makepayment');
    }
    
    // get orders
    public function getOrders()
    {
        $orders = orders::get();
        $all = [];

        $orders->obj(function($row) use (&$all){
            $all[] = $row->orderid; 
        });

        $this->render($all);
    }

    // submit chat
    public function submitChat(Post $post, $orderid)
    {
        if ($orderid != null)
        {
            $data = $post->data();
            $data['orderid'] = $orderid;
            $data['genericID'] = time();

            // get real id
            $orders = orders::get('orderid = ?', $orderid);

            $id = $data['accountid'];

            if ($orders->fromid == $id)
            {
                $data['accountid'] = $orders->accountid;
            }

            if ($data['accountid'] == $id)
            {
                $data['accountid'] = $orders->fromid;
            }

            // insert data
            db('chats')->insert($data);
        }
    }
	/**
    * App/search wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function search(Get $get)
	{
        $applistData = [
            'getDoctor' => 1,
            'getAmbulance' => 5,
            'getPharmacy' => 3,
            'getHospital' => 4,
            'getNurse' => 2,
            'getLab' => 6
        ];

        $totalRecords = 0;

        if ($get->has('s', $value) || $get->has('filter'))
        {
            if (!$get->has('s'))
            {
                $value = $get->search;
            }

            if ($get->has('category', $category))
            {
                $category = str_replace('doctors', 'doctor', $category);
                $category = str_replace('hospitals', 'hospital', $category);
                $category = str_replace('nurses', 'nurse', $category);
                $category = str_replace('labs', 'lab', $category);

                $get->set('category', $category);

                $newAppList = [];
                $categoryAsArray = explode(',', $category);

                foreach ($categoryAsArray as $category)
                {
                    $category = 'get' . ucfirst($category);
                    $newAppList[$category] = isset($applistData[$category]) ? $applistData[$category] : null;
                }

                $applistData = $newAppList;
            }

            if (strlen($value) > 1)
            {
                $this->searchValue = $value;

                $applist = Model::AppList();

                
                $applist->intelligentSearch($value);
                

                foreach ($applistData as $method => $typeid)
                {
                    if ($typeid !== null)
                    {
                        $list = $applist->loadData($method, $typeid, null);
                        $applistData[$method] = is_null($list) ? [] : $list;

                        if (!is_null($list))
                        {
                            $totalRecords += count($list);
                        }
                    }
                }

                if ($totalRecords == 0)
                {
                    Alert::info("No search results found for '$value'!");
                }

                if ($get->has('category', $category))
                {
                    $category = 'get' . ucfirst($category);

                    if (isset($applistData[$category]))
                    {
                        if (count($applistData[$category]) > 0)
                        {
                            $this->redir('list/' . $get->category . '?s=' . $value);
                        }
                    }
                }
            }
        }

        if (!$this->has('queryString'))
        {
            $this->queryString = '?s=' . $this->searchValue;
        }

		$this->render('search', ['applistData' => $applistData, 'total' => $totalRecords]);
	}
	/**
    * App/buy-drug wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function buyDrug(Get $get, $option, $category)
	{
        $this->model = Model::Drugs();

        // get drugs
        $drugs = [];

        $getDrugs = [];

        if ($get->has('category', $_category) || $category !== null)
        {
            if (is_null($_category))
            {
                $_category = $category;
            }

            $categoryArray = explode(',', $_category);

            foreach ($categoryArray as $_category)
            {
                $getCategory = db('pharmacytypes')->get('pharmacytype=?', $_category);

                if ($getCategory->rows > 0)
                {
                    $allDrugs = [];

                    // find drugs in this category
                    db('drugs')->get('pharmacytypeid=?', $getCategory->pharmacytypeid)
                    ->obj(function($row) use (&$allDrugs){
                        $this->model->getDrugInfo($row, $allDrugs);
                    });

                    if (count($allDrugs) > 0)
                    {
                        $getDrugs[$_category] = $allDrugs;
                    }
                }
            }

            if (count($getDrugs) == 0)
            {
                Alert::info('We could not find any drug from the "'.$_category.'" category.');
            }
        }
        
        db('drugs')->get()->rand()->limit(0,6)->obj(function($row) use (&$drugs){
            $this->model->getDrugInfo($row, $drugs);
        });

		$this->render('buydrug', ['drugs' => $drugs, 'fromCategory' => $getDrugs, 'category' => $category]);
	}
	/**
    * App/pharmacy wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function pharmacy($pharmacy, $option, $category, Get $get)
	{
        if (is_null($pharmacy))
        {
            $this->redir('list/pharmacy');
        }

        // check if pharmacy exists
        $getPharmacy = db('pharmacies')->get('pharmacy_name = ?', $pharmacy);

        if ($getPharmacy->rows == 0)
        {
            $this->redir('list/pharmacy', ['message' => 'We could not find "'.$pharmacy.'". Here are some of our pharmacies']);
        }

        $this->model = Model::Drugs();

        // get pharmacy account information
        $account = $getPharmacy->from('account')->get();
        $export = [
            'account' => $account
        ];

        // check web_photo
        $webphoto = $account->from('web_photo', 'accountid')->get();
        if ($webphoto->row > 0)
        {
            $export['web_photo'] = $webphoto->row();
        }
        else
        {
            $export['web_photo'] = (object) [
                'cover_image' => '388882-PC5X6X-544.jpg',
                'profile_image' => 'icon/man-3.png'
            ];
        }

        // get drugs
        $drugs = $getPharmacy->from('drugs', 'pharmacyid')->get();

        if ($drugs->rows > 0)
        {
            // check for drugs under a category
            if ($category !== null)
            {
                // get category id
                $catid = db('pharmacytypes')->get('pharmacytype=?', $category);

                if ($catid->rows > 0)
                {
                    // get drugs
                    $getDrugs = db('drugs')
                    ->query([$this->model, 'FetchCategory'], $getPharmacy, $catid)
                    ->query([$this->model, 'ApplyDrugFilter']);

                    if ($getDrugs->rows > 0)
                    {
                        $drugs = $getDrugs;
                    }
                    else 
                    {
                        if ($get->has('drug', $drug))
                        {
                            $this->searchValue = $drug;

                            // check other categories
                            $getDrugFromOthers = db('drugs')
                            ->query([$this->model, 'FetchByPharmacy'], $getPharmacy)
                            ->query([$this->model, 'ApplyDrugFilter']);

                            if ($getDrugFromOthers->rows > 0)
                            {
                                $this->option = null;
                                $drugs = $getDrugFromOthers;
                                $get->set('skip', true);
                            }
                            else
                            {
                                Alert::info('We currently do not have this drug "'.$drug.'" at the moment.');

                                $getDrugs = db('drugs')->query([$this->model, 'FetchCategory'], $getPharmacy, $catid);

                                if ($getDrugs->rows > 0)
                                {
                                    $drugs = $getDrugs;
                                }
                            }
                            
                        }
                        else
                        {
                            Alert::info('We currently do not have any drug within the "'.$category.'" category.');   
                        }
                    }
                }
            }

            $this->hasResult = true;

            // get drugs
            $_drugs = [];

            $drugs->obj(function($row) use (&$_drugs){
                $this->model->getDrugInfo($row, $_drugs);
            });

            $export['drugs'] = $_drugs;
        }

        // get nearby pharmacies
        $nearby = [];

        db('pharmacies')->get('stateid=? and pharmacyid != ?', $getPharmacy->stateid, $getPharmacy->pharmacyid)
        ->rand()
        ->limit(0,5)
        ->obj(function($row) use (&$nearby){

            $account = $row->from('account','accountid')->get();
            $photo = $row->from('web_photo', 'accountid')->get();
            $city = $row->from('cities', 'cityid')->get();

            $nearby[] = (object) [
                'account' => $account,
                'image' => ($photo->rows > 0) ? $photo->profile_image : 'icon/man-3.png',
                'pharmacy_name' => $row->pharmacy_name,
                'location' => $city->city
            ];
        });

        $export['nearby'] = $nearby;

        if ($this->has('pharmacyMessage'))
        {
            Alert::info($this->pharmacyMessage->message);
        }

		$this->render('pharmacy', ['data' => (object) $export]);
	}
	/**
    * App/drug wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function drug($pharmacy, $drugName)
	{
        // get drug category
        // check if pharmacy exists
        $isPharmacy = Query::getPharmacyId($pharmacy);

        if ($isPharmacy->rows == 0)
        {
            $this->redir('buydrug');
        }

        // now check drug from pharmacy
        $hasDrug = Query::getPharmacyDrug($isPharmacy->pharmacyid, $drugName);

        if ($hasDrug->rows == 0)
        {
            $this->redir('pharmacy/'.$pharmacy, ['message' => '"'.$drugName.'" doesn\'t exists in our record. Please check other pharmacies.']);
        }

        // set model
        $this->model = Model::Drugs();

        // get drug category
        $drugCategory = Query::getDrugCategory($hasDrug->pharmacytypeid);

        // get drug info
        $drugList = [];

        $hasDrug->obj(function($row) use (&$drugList)
        {
            $this->model->getDrugInfo($row, $drugList);
        });

        // get account
        $account = $isPharmacy->from('account', 'accountid')->get();
        $group = Query::getGroups($account->accounttypeid);

        // get previous
        $previous = uri()->previous();
        $link = explode('/', $previous->link);

        if ($link[0] != 'drug' && strlen($previous->link) > 3)
        {
            session()->set('previous.page', $previous);
        }

        // require about js
        $this->requirejs('About.js');

        // has a message
        if ($this->has('drugMessage'))
        {
            // get message object
            $message = $this->drugMessage;

            // set message for public accessibility
            Alert::{$message->type}($message->message);
        }

        // render view
		$this->render('drug', ['category' => $drugCategory->pharmacytype, 'drug' => $drugList[0], 'groups' => $group]);
	}
	/**
    * App/cart wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function cart(Post $post, Get $get, $action, $cartid)
	{
        $continueShopping = uri()->previous()->link;

        if (session()->has('previous.page', $link))
        {
            $continueShopping = $link->link;
        }

        if ($continueShopping == '/')
        {
            $continueShopping = uri()->previous()->link;
        }

        // define levels of errors
        $errors = [
            // got an invalid code ?
            [
                'message' => 'Invalid prescribtion code. Failed to add to cart',
                'type' => 'danger'
            ],

            // prescription code has been used
            [
                'message' => 'Prescribtion code validity expired. This code has been previously used by you.',
                'type' => 'danger'
            ],

            // prescription code not valid for drug
            [
                'message' => 'Sorry this drug was not prescribed using this code. Please try again with a different code.',
                'type' => 'danger'
            ]
        ];

        
        if ($post->has('prescribed', $prescribedCode))
        {
            if ($prescribedCode != '0')
            {
                // get previous link
                $previous = uri()->previous()->link;

                // check and verify if code is valid
                $isPresribed = Query::getPrescribedDrugs($prescribedCode);

                // check if drug was prescribed to user
                if ($isPresribed->rows == 0)
                {
                    $this->redir($previous, $errors[0]);
                }

                // check if it's uaed
                if ($isPresribed->isused == 1)
                {
                    $this->redir($previous, $errors[1]);
                }
                else
                {
                    // check if code is associated to drug
                    $drugs = json_decode($isPresribed->drugs);

                    // get drug id
                    $drugid = $post->get('drugid');

                    // drug found
                    $drugFound = false;

                    foreach ($drugs as $drug)
                    {
                        if ($drug->drugid == $drugid)
                        {
                            $drugFound = true;
                            break;
                        }
                    }

                    if (!$drugFound)
                    {
                        $this->redir($previous, $errors[2]);
                    }
                
                }
            }
        }

        // manage outcomes
        switch ($action)
        {
            case 'remove':
                $cart = session()->get('user.cart');

                if (isset($cart[$cartid]))
                {
                    unset($cart[$cartid]);
                }
                //save session for user
                session()->set('user.cart', $cart);
            break;

            case 'quantity':
                if ($get->has('qty', $quantity))
                {
                    $cartid = $get->cartid;
                    $cart = session()->get('user.cart');
                    $json = ['subtotal' => 0, 'shipping' => 0, 'total' => 0];

                    foreach ($cart as $index => $cc)
                    {
                        if ($index == $cartid)
                        {
                            $cc['quantity'] = intval($quantity);
                            $cart[$index]['quantity'] = $cc['quantity'];
                            $json['price'] = ($cc['price'] * $cc['quantity']) + $cc['servicefee'];
                            $json['price'] = Wrapper::money($json['price']);
                            break;
                        }
                    }

                    // get sub total and handling fee
                    foreach ($cart as $cc)
                    {
                        $json['subtotal'] += ($cc['price'] * $cc['quantity']);
                        $json['shipping'] += ($cc['servicefee']);
                    }   

                    $json['total'] = Wrapper::money(($json['subtotal'] + $json['shipping']));
                    $json['subtotal'] = Wrapper::money($json['subtotal']);
                    $json['shipping'] = Wrapper::money($json['shipping']);

                    session()->set('user.cart', $cart);

                    // return array
                    $this->render($json);
                }
            break;

        }

        // add drug to cart
        if ($post->has('pharmacyid', $pharmacyid))
        {
            $shipping = $post->get('shipping_option');
            $drug = db('drugs')->get('drugid=?', $post->drugid);

            // get previous link
            $previous = uri()->previous()->link;

            $cart = [
                'servicefee' => Query::getServiceFee($shipping, $group),
                'price' => floatval($drug->price),
                'drug_name' => $drug->drug_name,
                'shipping' => $group->group_name,
                'link' => $previous
            ];
            $data = $post->data();
            $data = array_merge($data, $cart);

            // cart saved
            $cart = session()->get('user.cart');

            if ($cart === false)
            {
                $cart = [$data];
            }
            else 
            {
                // check first
                $hasNotBeenAdded = true;

                foreach ($cart as $index => $c)
                {
                    if ($c['drugid'] == $post->drugid)
                    {
                        $hasNotBeenAdded = false;
                        break;
                    }
                }

                if ($hasNotBeenAdded)
                {
                    array_push($cart, $data);
                }
            }
            
            // save user cart session
            session()->set('user.cart', $cart);
        }

        $this->requirejs('http.js', 'before cart.js');
        

        // redirect user if cart is empty
        if (session()->has('user.cart', $cart))
        {
            if (count($cart) == 0)
            {
                $this->redir($continueShopping);
            }
        }
        else
        {
            $this->redir($continueShopping);
        }

		$this->render('cart', ['continueShopping' => $continueShopping]);
	}
	/**
    * App/checkout wrapper. 
    *
    * See documention https://www.moorexa.com/doc/controller
    *
    * @param Any You can catch params sent through the $_GET request
    * @return void
    **/

	public function checkout(Post $post)
	{
        if ($post->has('checkout'))
        {
            $cart = session()->get('user.cart');
            $data = ['subtotal' => 0, 'shipping' => 0];
            $totalDrugs = 0;

            // get sub total and handling fee
            foreach ($cart as $cc)
            {
                $data['subtotal'] += ($cc['price'] * $cc['quantity']);
                $data['shipping'] += ($cc['servicefee']);
                $totalDrugs++;
            }  

            $post->set('amount', floatval(($data['subtotal'] + $data['shipping'])));

            // get ref
            $getref = Http::get('payment/ref')->json;
            $ref = $getref->ref;

            $narration = 'Order for '.$totalDrugs.' drug'.($totalDrugs > 1 ? 's' : '').'. Paying with '.ucfirst($post->paymentmethod); // narration

            $body = [
                'txref' => $ref,
                'amount' => $post->amount,
                'narration' => $narration
            ];
            
            // push request
            $create = Http::body($body)->put('payment')->json;

            if ($create->status == 'success')
            {
                $post->set('ref', $ref);
                $data = $post->data();

                session()->set('checkout', $data);

                if ($post->paymentmethod == 'cash')
                {
                    $this->redir('my/payment/success');
                }

                // render make payment view
                $this->render('makepayment', ['checkout' => [
                    'phone' => $post->telephone,
                    'ref' => $ref,
                    'email' => $post->email,
                    'amount' => $post->amount]
                    ]);

                // stop execution from this point
                return false;
            }
        }

        // check session
        if (!session()->has('account.id', $accountid))
        {
            // set redirect to in session
            session()->set('redirectTo', 'checkout');

            // redirect user
            $this->redir('signin');
        }

        // get account information
        $account = Query::getAccount($accountid);

        // get countryid
        $country = $account->from('states', 'stateid')->get();

        $cart = session()->get('user.cart');
        $json = ['subtotal' => 0, 'shipping' => 0];

        // get sub total and handling fee
        foreach ($cart as $cc)
        {
            $json['subtotal'] += ($cc['price'] * $cc['quantity']);
            $json['shipping'] += ($cc['servicefee']);
        }   

        $json['total'] = Wrapper::money(($json['subtotal'] + $json['shipping']));
        $json['subtotal'] = Wrapper::money($json['subtotal']);
        $json['shipping'] = Wrapper::money($json['shipping']);


		$this->render('checkout', [
            'account' => $account,
            'countryid' => $country->countryid,
            'cart' => $json
        ]);
	}
}
// END class