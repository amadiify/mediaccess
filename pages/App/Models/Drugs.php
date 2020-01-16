<?php
namespace Moorexa;

use Moorexa\DB;
use Moorexa\Event;
use Moorexa\HttpGet as Get;
use Moorexa\HttpPost;

/**
 * Drugs model class auto generated.
 *
 *@package	Drugs Model
 *@author  	Moorexa <www.moorexa.com>
 **/

class Drugs extends Model
{
    // @override table name
    public $table = "";

    // @override connection. switch database 
    public $switchdb = "";

    // set up database structure
    public function __structure($schema)
    {
        //. code here
    }

    public function getTotalRating($drugid, &$rating=null, &$allratings=0)
    {
        $rating = \Query::getDrugRating($drugid);
        // options
        $count = \Query::getRatingOptions()->rows;
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
 
        $rating = ceil(abs($ratings / $count));
 
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

    // get drug info
    public function getDrugInfo($row, &$drugs=[])
    {
        // get pharmacy type
        $type = $row->from('pharmacytypes')->get();

        // get image
        $image = $row->from('drug_images', 'drugid')->get();

        // get pharmacy
        $pharmacy = $row->from('pharmacies')->get();
        
        $drugs[] = (object) [
            'pharmacytype' => $type->pharmacytype,
            'pharmacy' => $pharmacy->pharmacy_name,
            'image' => ($image->row > 0) ? $image->image : image('listing-bg.jpg'),
            'drug' => $row->row(),
            'isverified' => $type->isverified,
            'account' => $row->from('account')->get()->row()
        ];
    }

    // is active
    public function isActive($category)
    {
        $get = boot()->singleton(Get::class);

        if (!$get->has('skip'))
        {
            $uri = array_flip(uri()->paths());

            if (isset($uri[$category]))
            {
                return true;
            }

            if ($get->has('category', $categories))
            {
                $categories = array_flip(explode(',', $categories));

                if (isset($categories[$category]))
                {
                    return true;
                }
            }
        }

        return false;
    }

    // apply drug filter
    public function queryApplyDrugFilter(DB $query)
    {
        $get = boot()->singleton(Get::class);

        if ($get->has('drug', $drug))
        {
            $query->like('drug_name', '%'.$drug.'%');
        }
    }

    // fetch category
    public function queryFetchCategory(DB $query, $getPharmacy, $catid)
    {
        $query->query('FetchByPharmacy', $getPharmacy);
        $query->andWhere('pharmacytypeid =?', $catid->pharmacytypeid);
    }

    // fetch by pharmacy
    public function queryFetchByPharmacy(DB $query, $getPharmacy)
    {
        $query->get('pharmacyid=?', $getPharmacy->pharmacyid);
    }

    // submit reviews
    public function submitReview(HttpPost $post, string $pharmacy, string $drugName)
    {
        $data = $post->data();

       if (!session()->has('account.id', $id))
       {
           Form::method('submitReview', function($form) use ($data, $pharmacy, $drugName){
               $form->on('drug/'.$pharmacy.'/'.$drugName)->push($data);
           });

           session()->set('redirectTo', 'drug/'.$pharmacy.'/'.$drugName);
           $this->redir('sign-in');
       }
       else
       {
            $rating = $data['rating'];
            // $table = db('rating_option');
            $code = strval($id.time().mt_rand(1000,30000));
            $drugid = $data['drugid'];
            $ratingfrom = $id;
            
            // get pharmacy id
            $pharmacy = \Query::getPharmacyId($pharmacy);
            
            if ($pharmacy->accountid != $ratingfrom)
            {
                // get rating id
                foreach ($rating as $type => $val)
                {
                    $row = \Query::getRatingOption($type);

                    if ($row->row == 1)
                    {
                        \Query::addDrugRating([
                        'ratingcode' => $code,
                        'optionid' => $row->optionid,
                        'drugid' => $drugid,
                        'ratingfrom' => $ratingfrom,
                        'rating' => doubleval($val)]);
                    }
                }
                
                //add to review
                $add = \Query::addDrugReview([
                    'ratingcode' => $code,
                    'drugid' => $drugid,
                    'userid' => $id,
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
      $reviews = \Query::getDrugRatingReviewsByCode($code);

      $reviews->obj(function($row) use (&$total){
          $total += $row->rating;
      });

      $options = \Query::getRatingOptions()->rows;

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

    }
}