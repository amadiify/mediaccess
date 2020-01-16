<?php
namespace Moorexa;

use Moorexa\DB;
use Moorexa\Event;

/**
 * About model class auto generated.
 *
 *@package	About Model
 *@author  	Moorexa <www.moorexa.com>
 **/

class About extends Model
{
   // check what's nearby
   public function whatsNearBy($accountType, $account)
   {
       $nearby = [];
       $config = [];

       // get random 3 
       $atypes = \account_types::get('accounttype != ? and showpublic = 1', $accountType)->rand();

       $atypes->obj(function($row) use ($account, &$nearby, &$config){
            $accounttypeid = $row->accounttypeid;
            // load config
            $config[$row->accounttype] = $row;

            // check account
            $account = \account::get('accounttypeid = ? and stateid = ?')->bind($accounttypeid, $account->stateid);
            if ($account->rows > 0)
            {
                $account->obj(function($ac) use (&$nearby, $row){
                    $nearby[$row->accounttype][] = $ac;
                });
            }
       });

       export_variables($nearby, $config);
   }

   // load description
   public function description($account)
   {
       $description = [];
       $accounttype = $account->account->accounttypeid;
       $id = $account->account->accountid;

       $name = ucwords($account->account->lastname .' '. $account->account->firstname);
       $this->address = isset($account->info) ? $account->info->address_to_place_of_work : $account->account->address;

       switch ($accounttype)
       {
           case 1:
           case 2:
              if ($accounttype == 1)
              {
                  // get specialization
                  $doctor = \doctors::get('accountid=?', $id);

                  $specialization = $doctor->from('specializations')->get();

                  $description['specialization'] = 'General';

                  if ($specialization->row == 1)
                  {
                    $description['specialization'] = ucfirst($specialization->specialization);
                  }
              }

              $info = $account->info;
              $exper = $info->years_of_experience;
              $s = $exper > 1 ? 'years' : 'year';

              $description['Experience'] = $exper . ' ' . $s;
              $description['City'] = \cities::get('cityid=?',$info->cityid)->city;
              $description['Place of work'] = $info->present_place_of_work;
              $description['Work Address'] = $info->address_to_place_of_work;

           break;

           default:
              if ($accounttype == 4)
              {
                  // get hospital specialization
                  $description['specialization'] = 'General';

                  $hospital = \hospitals::get('accountid=?',$id);
                  $specialization = $hospital->from('hospital_specializations', 'hospitalid')->get();

                  if ($specialization->rows > 0)
                  {
                      $spec = [];
                      $specialization->obj(function($row) use (&$spec){
                         $spec[] = ucwords($row->specialization);
                      });
                      $description['specialization'] = implode(', ', $spec);
                  }

                  $description['hospital name'] = $hospital->hospital_name;
                  $description['location'] = \states::get('stateid=?',$hospital->stateid)->state;
                  $description['city'] = \cities::get('cityid=?',$hospital->cityid)->city;
                  $description['hospital address'] = $hospital->address;
                  $name = $hospital->hospital_name;
                  $this->address = $hospital->address;
              }

              elseif ($accounttype == 3)
              {
                  // get pharmacy specialization
                  $description['specialization'] = 'General';

                  // load pharmacy
                  $pharmacy = \pharmacies::get('accountid=?',$id);
                  $types = $pharmacy->from('pharmacy_type_list','pharmacyid')->get();
                  if ($types->rows > 0)
                  {
                      $spec = [];
                      $types->obj(function($row) use (&$spec){
                          $type = $row->from('pharmacytypes')->get();
                          $spec[] = $type->pharmacytype;
                      });
                      $total = count($spec);
                      if ($total < 5)
                      {
                         $description['specialization'] = implode(', ', $spec);
                      }
                      else
                      {
                          $firstFive = array_splice($spec, 1, 5);
                          $other = count(array_splice($spec, 5));

                          $description['specialization'] = implode(', ', $firstFive) . ' (+'.$other.' more)';
                      }
                  }

                  $description['pharmacy name'] = $pharmacy->pharmacy_name;
                  $description['location'] = \states::get('stateid=?', $pharmacy->stateid)->state;
                  $description['city'] = \cities::get('cityid=?', $pharmacy->cityid)->city;
                  $description['pharmacy address'] = $pharmacy->address;
                  $name = $pharmacy->pharmacy_name;
                  $this->address = $pharmacy->address;
              } 

              elseif ($accounttype == 6)
              {
                  $lab = \labs::get('accountid=?',$id);

                  $description['lab name'] = $lab->lab_name;
                  $description['location'] = \states::get('stateid=?', $lab->stateid)->state;
                  $description['city'] = \cities::get('cityid=?', $lab->cityid)->city;
                  $description['lab address'] = $lab->address;
                  $name = $lab->lab_name;
                  $this->address = $lab->address;
              }

              else
              {
                  $description['location'] = \states::get('stateid=?', $account->account->stateid)->state;
                  $description['office address'] = $account->account->address;
              }
       }

       export_variables($description);

       $this->about_name = $name;
   }

   public function submitReview(HttpPost $post)
   {
       $data = $post->data();

       if (!session()->has('account.id', $id))
       {
           Form::method('submitReview', function($form) use ($data){
               $form->on('about/'.$this->who)->push($data);
           });

           session()->set('redirectTo', 'about/'.$this->who);
           $this->redir('sign-in');
       }
       else
       {
            $rating = $data['rating'];
            // $table = db('rating_option');
            $code = strval($id.time().mt_rand(1000,30000));
            $accountid = $data['accountid'];
            $ratingfrom = $id;
            
            if ($accountid != $ratingfrom)
            {
                // get rating id
                foreach ($rating as $type => $val)
                {
                    $row = db('rating_option')->get('options=?')->bind($type);
                    if ($row->row == 1)
                    {
                        \rating::insert(['ratingcode' => $code,
                                        'optionid' => $row->optionid,
                                        'accountid' => $accountid,
                                        'ratingfrom' => $ratingfrom,
                                        'rating' => doubleval($val)])->go();
                    }
                }

                //add to review
                $add = \reviews::insert([
                    'ratingcode' => $code,
                    'accountid' => $accountid,
                    'userid' => $id,
                    'review_title' => $data['review_title'],
                    'review' => $data['review'],
                    'date_created' => date('Y-m-d g:i:s a')
                ]);

                if ($add->ok)
                {
                    \Bootstrap\Alert::success('Your review has been added successfully. Thanks');
                }
            }
       }
   }

   // get total stars
   public function totalStar($code, &$stars)
   {
      $total = 0;

      \rating::get('ratingcode=?', $code)->obj(function($row) use (&$total){
          $total += $row->rating;
      });

      $options = \rating_option::get()->rows;

      $rating = round(($total/$options), 1);

      $stars = [];

      for ($i=$rating; $i > 0; $i--)
      {
          if ($i > 1)
          {
            $stars[] = 'fa-star';
          }
          else
          {
              if ($i >= 0.5)
              {
                  $stars[] = 'fa-star-half-o';
              }
              else
              {
                  $stars[] = 'fa-star-o';
              }
          }
      }

      $c = count($stars);

      if ($c != 5)
      {
          for($c; $c != 5; $c++)
          {
             $stars[] = 'fa-star-o';
          }
      }

      return $total;
   }

   // get rating for a group
   public function getGroupRating($groupid, $accountid)
   {
        $total = 0;
        $rating = \rating::get('optionid=? and accountid=?', $groupid, $accountid);

        if ($rating->rows > 0)
        {
            $rating->obj(function($row) use (&$total){
                $total += $row->rating;
            });

            $total = abs(ceil(($total/5)/$rating->rows));

            if ($total > 100)
            {
                $total = 100;
            }
        }

        return $total;
   }

   // get total rating
   public function getTotalRating($accountid, &$rating=null, &$allratings=0)
   {
       $rating = \rating::get('accountid=?', $accountid);
       // options
       $count = \rating_option::get()->rows;
       // all ratings
       $ratings = 0;
       $allratings = $rating->rows;

       if ($rating->rows > 0)
       {
           $rating->obj(function($row) use (&$ratings){
                $ratings += $row->rating;
           });

           $ratings = ceil($ratings / $rating->rows);
       }

       $rating = abs($ratings / $count);

       if ($rating > 5)
       {
           $rating = 5;
       }

       $stars = [];

       for ($i=$rating; $i > 0; $i--)
       {
           if ($i > 1)
           {
             $stars[] = 'fa-star';
           }
           else
           {
               if ($i >= 0.5)
               {
                   $stars[] = 'fa-star-half-o';
               }
               else
               {
                   $stars[] = 'fa-star-o';
               }
           }
       }
 
       $c = count($stars);
 
       if ($c != 5)
       {
           for($c; $c != 5; $c++)
           {
              $stars[] = 'fa-star-o';
           }
       }

       return $stars;
   }

   // get all reviews
   public function getTotalReviews($accountid)
   {
       return \reviews::get('accountid=?', $accountid)->rows;
   }

   // allow report
   public function allowReport($accountid)
   {
       $allow = true;

       if (session()->has('account.id', $id))
       {
            if ($id == $accountid)
            {
                $allow = false;
            }
       }

       return $allow;
   }

   // make a report
   public function makeAReport()
   {
       $model = createModelRule('report', function($body){
            $body->allow_form_input();
       });

       if ($model->isOk())
       {
           
       }
   }

   // add view
   public function addView($id)
   {
       $agent = md5($_SERVER['HTTP_USER_AGENT']);
       // check
       $has = \views::get('accountid=? and agent=?', $id, $agent);

       if ($has->rows > 0)
       {
            $lastseen = new \DateTime($has->lastseen);
            $current = new \DateTime();

            $diff = $current->diff($lastseen);

            $h = intval($diff->format('%h'));

            if ($h >= 1)
            {
                // update view
                $has->update(['view' => ($has->view + 1), 'lastseen' => date('Y-m-d g:i:s a')]);
            }
       }
       else
       {
           $has->insert([
              'accountid' => $id,
              'agent' => $agent,
              'view' => 1,
              'lastseen' => date('Y-m-d g:i:s a')
           ]);
       }
   }

   // is top rated
   public function isTopRated($row)
   {
       
       return false;
   }
}