<?php
namespace Moorexa;

use Moorexa\DB;
use Moorexa\Event;
use Moorexa\HttpGet as Get;
use Moorexa\DB\ORMReciever;

/**
 * List model class auto generated.
 *
 *@package	List Model
 *@author  	Moorexa <www.moorexa.com>
 **/

class AppList extends Model
{
    // @override table name
    public $table = "account";

    // account 
    public $data = [];

    // search query
    public static $search = null;

    // get all data
    public function loadData($method, $typeid, $sort)
    {
        if (substr($method,0,3)=='get'&&strlen($method)>3)
        {
            $args = func_get_args();
            $args = array_splice($args,3);
            $method = substr($method, 3);

            $this->data = DB::lazy([AppList::class, 'fetchAll'], $sort, $typeid);

            return call_user_func_array([$this, $method], $args);
        }
    }

    public function queryAccountActive(DB $query, $typeid) : void
    {
        $query->get()->query('AddWhere', $typeid);
    }

    public function queryAddWhere(DB $query, $typeid, $func = 'where')
    {
        $get = boot()->get('Moorexa\HttpGet');

        $argument = ['accounttypeid=? and isverified=? and isblocked=?', $typeid, 1, 0];

        if ($get->has('category', $category) && uri()->view == 'search')
        {
            $category = explode(',', $category);

            $typeQuery = [];

            foreach ($category as $type)
            {
                $accountType = db('account_types')->get('accounttype=?', ucfirst($type));
                if ($accountType->rows > 0)
                {
                    $typeQuery[] = $accountType->accounttypeid;
                }
            }

            if (count($typeQuery) > 0)
            {
                $argument = [];
                $createString = str_repeat('accounttypeid=?,', count($typeQuery));
                $createString = rtrim($createString, ',');
                $createString = str_replace(',', ' or ', $createString);
                $createString .= ' and isverified=? and isblocked=?';
                $argument[] = $createString;

                foreach ($typeQuery as $index => $typeid)
                {
                    $argument[] = $typeid;
                }

                $argument[] = 1; // is verified ?
                $argument[] = 0; // is blocked ?
            }
        }

        call_user_func_array([$query, $func], $argument);
    }

    public function queryAddSorting(DB $query, $sort) : void
    {
        $query->if($sort != null, function($req) use ($sort)
        {
            $sort = ltrim(substr($sort, strpos($sort, '-')), '-');
            $req->like('groups', "%$sort%");
        });
    }

    // apply account filter
    public function queryApplyAccountFilter(DB $query)
    {
        $get = boot()->get('Moorexa\HttpGet');

        if ($get->has('group', $group))
        {
            $query->like('groups', "%$group%");
        }

        if ($get->has('city', $city))
        {
            // get city id
            $city = db('cities')->get('city=?', $city);
            if ($city->rows > 0)
            {
                $query->andWhere('cityid=?', $city->cityid);
            }
        }

        if ($get->has('state', $state))
        {
            // get state id
            $state = db('states')->get('state=?', $state);
            if ($state->rows > 0)
            {
                $query->andWhere('stateid=?', $state->stateid);
            }
        }
    }

    public function fetchAll(DB $lazy, $sort, int $typeid) : void
    {
        $get = boot()->get('Moorexa\HttpGet');

        if ($get->has('s', $search))
        {
            $lazy->get()->query('AddWhere', $typeid)->like('firstname', "%$search%");
            $lazy->query('AddWhere', $typeid, 'orWhere')->like('lastname', "%$search%");
        }
        else
        {
            $lazy->query('AccountActive', $typeid);
            $lazy->query('ApplyAccountFilter');
        }

        $lazy->query('AddSorting', $sort);
    }

    // get single data
    public function loadSingleData($method, $accountid)
    {
        if (substr($method,0,3)=='get'&&strlen($method)>3)
        {
            $args = func_get_args();
            $args = array_splice($args,3);
            $method = substr($method, 3);
            $this->data = $this->get('accountid=?', $accountid);
            return call_user_func_array([$this, $method], $args);
        }
    }

    // get caption
    public function getCaption($type, $typeid)
    {
        $text = '';

        $general = function(&$s=null,&$tense=null) use ($typeid)
        {
            $rows = $this->data->rows;
            $s = $rows > 1 ? 's' : '';
            $tense = $rows > 1 ? 'There are ' : 'Currently, there is just ';

            return $rows; 
        };

        $get = boot()->get('Moorexa\HttpGet');

        $total = $general($s, $tense);
        if ($total > 0)
        {
            $type = strtolower($type);
            $text = $tense.''.$total.' verified '.$type.''.$s.' around you, ';

            if ($get->has('search', $search))
            {
                if ($total == 1)
                {
                    $search = str_ireplace('nurses', 'nurse', $search);
                    $search = str_ireplace('doctors', 'doctor', $search);
                    $search = str_ireplace('hospitals', 'hospital', $search);
                }

                $text = 'Showing search result for '. $search . '';

                if (!$get->has('city') && !$get->has('state'))
                {
                    $text .= ' around you';
                }

                $text .= '. A total of '.$total.' record'.$s.' found.';
            }
        }

        if ($text == '')
        {
            $text = 'We currently do not have any '.strtolower($type).' around you. You may have to keep tab on us, glad you checked in.';
            $this->placeholder = 'What else can we find for you?';
        }

        return $text;
    }

    // get nurses
    public function Nurse()
    {
        if ($this->data->rows > 0)
        {
            $list = [];

            $this->data->obj(function($row) use (&$list){
                // check if submitted nurse info
                $nurse = db('nurses')->get('accountid=?',$row->accountid);
                if ($nurse->rows > 0)
                {
                    $data = [];
                    $data['nurse'] = $nurse->row();
                    $data['account'] = $row;
                    // get info
                    $info = db('account_information')->get('accountid=?', $row->accountid);
                    $data['info'] = $info->row();

                    $views = 0;
                    \views::get('accountid=?',$row->accountid)->obj(function($e) use (&$views){
                        $views += $e->view;
                    });
                    $data['views'] = $views;

                    // check web_photo
                    $webphoto = db('web_photo')->get('accountid=?',$row->accountid);
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

                    $list[] = (object) $data;
                }
            });

            return $list;
        }
    }

    // apply filter
    public function queryApplyFilter(DB $query, $row, $key)
    {
        $query->get('accountid=?',$row->accountid);

        $get = boot()->get('Moorexa\HttpGet');

        if ($get->has('s', $search))
        {
            $query->like($key, "%$search%");
        }
    }

    // apply doctor filter
    public function queryApplyDoctorFilter(DB $query, $row)
    {
        $get = boot()->get('Moorexa\HttpGet');

        $query->get('accountid=?',$row->accountid);

        if ($get->has('specialization', $specialization))
        {
             // get specialization id
             $specialization = db('specializations')->get('specialization=?', $specialization);

             if ($specialization->rows > 0)
             {
                 $query->andWhere('specializationid=?', $specialization->specializationid);
             }
        }
    }

    // apply doctor filter
    public function queryApplyHospitalFilter(DB $query, $row)
    {
        $get = boot()->get('Moorexa\HttpGet');

        if ($get->has('specialization', $specialization))
        {
             // check for specialization
             $specialization = db('hospital_specializations')->get('specialization=?', $specialization);

             if ($specialization->rows == 0)
             {
                 $this->data = $query->queryShouldFail();
             }
             else 
             {
                 // rebuild query
                 $table = db('hospitals');
                 $data = $table->get()
                 ->join('hospital_specializations')
                 ->on('hospitals.hospitalid = hospital_specializations.hospitalid and hospital_specializations.specialization = :spec')
                 ->bind(['spec' => $get->specialization]);

                 $this->data = $data;
                 $query->queryShouldReturn($data);
             }
        }

        $query->query('ApplyFilter', $row, 'hospital_name');
    }

    // get hospital
    public function Hospital()
    {
        if ($this->data->rows > 0)
        {
            $list = [];

            $this->data->obj(function($row) use (&$list){

                // check if submitted hospital info
                $hospitals = db('hospitals')->query([$this, 'ApplyHospitalFilter'], $row);

                if ($hospitals->rows > 0)
                {
                    $data = [];
                    $data['hospital'] = $hospitals->row();
                    $data['account'] = $row;  
                    
                    $views = 0;
                    \views::get('accountid=?',$row->accountid)->obj(function($e) use (&$views){
                        $views += $e->view;
                    });
                    $data['views'] = $views;

                    // check web_photo
                    $webphoto = db('web_photo')->get('accountid=?',$row->accountid);
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

                    $list[] = (object) $data;
                }
            });

            return $list;
        }
    }

    // get ambulance
    public function Ambulance()
    {
        if ($this->data->rows > 0)
        {
            $list = [];

            $this->data->obj(function($row) use (&$list){
                $data = [];
                $data['account'] = $row;  
                
                $views = 0;
                \views::get('accountid=?',$row->accountid)->obj(function($e) use (&$views){
                    $views += $e->view;
                });
                $data['views'] = $views;

                // check web_photo
                $webphoto = db('web_photo')->get('accountid=?',$row->accountid);
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

                $list[] = (object) $data;
            });

            return $list;
        }
    }

    // get pharmacy
    public function Pharmacy()
    {
        if ($this->data->rows > 0)
        {
            $list = [];

            $this->data->obj(function($row) use (&$list){
                
                // check if submitted pharmacies info
                $pharmacies = db('pharmacies')->query([$this, 'ApplyFilter'], $row, 'pharmacy_name');

                if ($pharmacies->rows > 0)
                {
                    $data = [];
                    $data['pharmacy'] = $pharmacies->row();
                    $data['account'] = $row; 
                    
                    $views = 0;
                    \views::get('accountid=?',$row->accountid)->obj(function($e) use (&$views){
                        $views += $e->view;
                    });
                    $data['views'] = $views;

                    // check web_photo
                    $webphoto = db('web_photo')->get('accountid=?',$row->accountid);
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

                    $list[] = (object) $data;
                }
            });

            return $list;
        }
    }

    // get doctors
    public function Doctor()
    {
        if ($this->data->rows > 0)
        {
            $list = [];

            $this->data->obj(function($row) use (&$list){
                // check if submitted doctor info
                $doctor = db('doctors')->query([$this, 'ApplyDoctorFilter'], $row);
                if ($doctor->rows > 0)
                {
                    $data = [];
                    $data['doctor'] = $doctor->row();
                    $data['account'] = $row;
                    // get info
                    $info = db('account_information')->get('accountid=?', $row->accountid);
                    $data['info'] = $info->row();

                    $views = 0;
                    \views::get('accountid=?',$row->accountid)->obj(function($e) use (&$views){
                        $views += $e->view;
                    });
                    $data['views'] = $views;

                    // check web_photo
                    $webphoto = db('web_photo')->get('accountid=?',$row->accountid);
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

                    $list[] = (object) $data;
                }
                else {
                    $this->data = $doctor;
                }
            });

            return $list;
        }
    }

    // get lab
    public function Lab()
    {

    }

    private function matchWords($index=0, $max=1, &$matched = 0, $source, $dest, &$line='')
    {
        $arrayString = implode(' ', $source);

        if (stripos($dest, $arrayString) !== false)
        {
            $matched = 1;
            $line = $arrayString;
            return true;
        }

        if (isset($source[$index]))
        {
            $word = trim(strtolower($source[$index])); // get from pointer

            if (strlen($word) > 0)
            {
                $word = preg_quote($word);
                $match = preg_match("/($word)/", $dest);

                if ($match === 0 && $word == '&')
                {
                    $match = preg_match("/(and)/", $dest);
                }

                if ($match !== 0)
                {
                    $matched++;
                    $dest = stristr($dest, $word);
                    $dest = substr($dest, strlen($word));
                    $line .= ' ' . $word;
                    $line = trim($line);
                }
            }

            if ($index < ($max+1))
            {
                $index++;
                $this->matchWords($index, $max, $matched, $source, $dest, $line);
            }
        }

        if ($line !== '' && $matched > 0)
        {
            return true;
        }

        return false;
    }

    // intelligent search
    public function intelligentSearch(string $search)
    {
        $searchArray = explode(' ', $search);

        $get = boot()->get('Moorexa\HttpGet'); 

        if (!session()->has('search-data'))
        {
            $proffessions = [
                'doctor', 'doctors', 'ambulance', 'ambulances', 'hospital',
                'hospitals', 'nurse', 'nurses', 'lab', 'labs',
                'drug', 'drugs', 'blood', 'pharmacy', 'pharmacies'
            ];
    
            $drugs = [
                'medicine', 'drug', 'pill', 'prescribed', 'prescribe', 'store'
            ];

            $drugsCategories = [];
            db('pharmacytypes')->get()->obj(function($row) use (&$drugsCategories){
                $drugsCategories[] = $row->pharmacytype;
            });

            $groups = [];
            db('groups')->get()->obj(function($row) use (&$groups){
                $groups[] = $row->group_name;
            });

            $cities = [];
            db('cities')->get()->obj(function($row) use (&$cities){
                $cities[] = $row->city;
            });

            $states = [];
            db('states')->get()->obj(function($row) use (&$states){
                $states[] = $row->state;
            });

            $specializations = [];
            db('specializations')->get()->obj(function($row) use (&$specializations){
                $specializations[] = $row->specialization;
            });

            $hospitalSpecializations = [];
            db('hospital_specializations')->get()->obj(function($row) use (&$hospitalSpecializations){
                $hospitalSpecializations[] = $row->specialization;
            });

            $searchdata = [
                'proffessions' => $proffessions,
                'drugs' => $drugs,
                'drugsCategories' => $drugsCategories,
                'groups' => $groups,
                'cities' => $cities,
                'states' => $states,
                'specializations' => $specializations,
                'hospitalSpecializations' => $hospitalSpecializations
            ];

            session()->set('search-data', $searchdata);
        }
        else {
            extract(session()->get('search-data'));
        }

        // query to send
        $query = [];
        $target = null;
        $accountTypes = [];
        $drugsCats = [];

        $drugsQueried = [];

        $searchString = implode(' ', $searchArray);

        $matched = function(string $search, string $other) use ($searchString)
        {
            static $found;

            if ($search == strtolower($other))
            {
                return true;
            }

            if (strtolower($searchString) == strtolower($other))
            {
                return true;
            }

            $array = explode(" ", $other);

            if ($found == null)
            {
                $match = $this->matchWords(0, str_word_count($other), $find, $array, strtolower($searchString), $line);

                if ($match)
                {
                    if (is_int($find) && $find >= 1 && strlen($line) > 1)
                    {
                        if (strpos($searchString, $line) !== false)
                        {
                            $found = true;
                            return true;
                        }
                    }

                    return false;
                }
            }

            return false;
        };

        foreach ($searchArray as $search)
        {
            $search = strtolower($search);

            // check specialization
            foreach ($specializations as $speciality)
            {
                if ($matched($search, $speciality))
                {
                    $target = 'doctor';
                    $query['specialization'] = $speciality;
                    break;
                }
            }

            // check for hospital specialization
            foreach ($hospitalSpecializations as $_speciality)
            {
                if ($matched($search, $_speciality))
                {
                    $target = 'hospital';
                    $query['specialization'] = $_speciality;
                    break;
                }
            }

            // check state
            foreach ($states as $state)
            {
                if ($matched($search, $state))
                {
                    $query['state'] = $state;
                    break;
                }
            }

            // check city
            foreach ($cities as $city)
            {
                if ($matched($search, $city))
                {
                    $query['city'] = $city;
                    break;
                }
            }

            // check groups
            foreach ($groups as $group)
            {
                if ($matched($search, $group))
                {
                    $query['group'] = $group;

                    // apply target
                    $group = db('groups')->get('group_name = ?', $group);
                    // get account type
                    $accountType = db('account_types')->get('accounttypeid=?', $group->accounttypeid);
                    $target = strtolower($accountType->accounttype);
                    break;
                }
            }

            // check proffessions
            foreach ($proffessions as $proffession)
            {
                if ($search == strtolower($proffession))
                {
                    $target = $proffession;
                    break;
                }
            }

            // check drugs
            foreach ($drugs as $drug)
            {
                if ($search == strtolower($drug))
                {
                    $target = 'buydrug';
                    break;
                }
            }

            // check drug category
            foreach ($drugsCategories as $index => $drugCat)
            {
                if ($matched($search, $drugCat))
                {
                    $target = 'buydrug';
                    $drugsCats[] = $drugCat;
                }
            }
        }

        $drugs = db('drugs');
        $drugsMatched = [];

        // check for multiple inclusions
        foreach ($searchArray as $search)
        {
            // check proffessions
            foreach ($proffessions as $proffession)
            {
                if ($search == strtolower($proffession))
                {
                    $accountTypes[] = $proffession;
                }
            }

            $isDrug = $drugs->get()->like('drug_name', '%'.$search.'%');
            if ($isDrug->rows > 0)
            {
                $drugsMatched[] = $search;
            }
        }

        if (count($accountTypes) > 1)
        {
            $target = null;
            $accountTypes = array_unique($accountTypes);
            $query['category'] = implode(',', $accountTypes);
        }

        if (count($drugsCats) > 0)
        {
            $target = 'buydrug';
            $drugsCats = array_unique($drugsCats);
            $query['category'] = implode(',', $drugsCats);
        }

        if (count($drugsMatched) > 0)
        {
            $query['drug'] = implode(',', $drugsMatched);
            $target = 'finddrug';
        }

        $query = http_build_query($query);

        if (strlen($query) > 1)
        {
            $query = '?filter=yes&' . $query . '&search=' . implode(' ', $searchArray);
            $this->search = implode(' ', $searchArray);
        }

        if (!is_null($target))
        {
            switch (trim(strtolower($target)))
            {
                case 'doctor':
                case 'doctors':
                    $this->redir('list/doctor' . $query);
                break;

                case 'ambulance':
                case 'ambulances':
                    $this->redir('list/ambulance' . $query);
                break;

                case 'hospital':
                case 'hospitals':
                    $this->redir('list/hospital' . $query);
                break;

                case 'nurse':
                case 'nurses':
                    $this->redir('list/nurse' . $query);
                break;

                case 'buydrug':
                    $this->redir('buydrug' . $query);
                break;

                case 'pharmacy':
                case 'pharmacies':
                    $this->redir('list/pharmacy'. $query);
                break;

                case 'lab':
                case 'labs':
                    $this->redir('list/lab' . $query);
                break;
                
                case 'finddrug':
                    $prev = Route::previous();
                    $this->redir($prev->link . $query);
                break;

                default:
                    $this->filter = $query;
            }
        }
        else 
        {
            $this->queryString = $query;

            if (!$get->has('filter'))
            {
                if (strlen($query) > 5)
                {
                    $this->redir('app/search/' . $query);
                }
            }
        }

    }
}