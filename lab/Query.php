<?php

namespace Request;
use Moorexa\DB;

class Query
{
    // get all public account
    public static function publicAccounts()
    {
        return db('account_types')->get('showpublic=1')->orderby('accounttype', 'asc');
    }

    // get groups
    public static function getGroups(int $accountTypeId)
    {
        return db('account_groups')->get('accounttypeid = ?', $accountTypeId);
    }

    // get users in a group from account and return rows
    public static function getRowsFromGroupUsers(string $group)
    {
        return db('account')->get()->like("accountgroups", "%$group%")->rows;
    }

    // is account verified by medi access ?
    public static function isVerified(int $accountid)
    {
        return db('account_verification')->get('accountid=? and isverified=?', $accountid, 1)->row;
    }

    // get related drugs from a category in a pharmacy
    public static function relatedDrugs($drug)
    {
        // get pharmacyid
        $pharmacyid = $drug->drug->pharmacyid;

        // get pharmacy typeid
        $pharmacytypeid = $drug->drug->pharmacytypeid;

        // get drugid
        $drugid = $drug->drug->drugid;

        // return drug
        return db('drugs')->get('pharmacyid=? and pharmacytypeid=? and drugid != ?')->bind($pharmacyid, $pharmacytypeid, $drugid)->rand();
    }

    // get drug image
    public static function getDrugImage(int $drugid)
    {
        // check drug table
        $drugTable = db('drug_images')->get('drugid=?', $drugid);

        if ($drugTable->rows > 0)
        {
            return $drugTable->image;
        }

        return image('listing-bg.jpg');
    }

    // query for account id
    public function queryApplyId(DB $query, $id)
    {
        $query->get('accountid=?', $id);
    }

    // get account information for a user
    public static function getAccountInfo(int $id)
    {
        return db('account_information')->query('ApplyId', $id);
    }

    // get pharmacy info for a user
    public static function getPharmacyInfo(int $id)
    {
        return db('pharmacies')->query('ApplyId', $id);
    }

    // get hospital info for a user
    public static function getHospitalInfo(int $id)
    {
        return db('hospitals')->query('ApplyId', $id);
    }

    // get doctor info for a user
    public static function getDoctorInfo(int $id)
    {
        return db('doctors')->query('ApplyId', $id);
    }

    // get lab info for a user
    public static function getLabInfo(int $id)
    {
        return db('labs')->query('ApplyId', $id);
    }

    // get pharmacyid
    public static function getPharmacyId(string $pharmacy)
    {
        return db('pharmacies')->get('pharmacy_name=?', $pharmacy);
    }

    // get pharmacy drugs
    public static function getPharmacyDrug(int $pharmacyid, string $drugName)
    {
        return db('drugs')->get('pharmacyid=? and drug_name=?', $pharmacyid, $drugName);
    }

    // get drug category
    public static function getDrugCategory(int $categoryId)
    {
        return db('pharmacytypes')->get('pharmacytypeid=?', $categoryId);
    }

    // add drug rating
    public static function addDrugRating(array $rating)
    {
        return db('drug_ratings')->insert($rating)->go();
    }

    // get drug ratings
    public static function getDrugRating(int $drugid)
    {
        return db('drug_ratings')->get('drugid=?', $drugid);
    }

    // get drug rating option
    public static function getRatingOption(string $option)
    {
        return db('drug_rating_option')->get('options=?')->bind($option);
    }

    // get drug rating option
    public static function getDrugRatingReviewsByCode(string $code)
    {
        return db('drug_ratings')->get('ratingcode=?')->bind($code);
    }

    // get drug rating options
    public static function getRatingOptions()
    {
        return db('drug_rating_option')->get();
    }

    // add drug review
    public static function addDrugReview(array $review)
    {
        return db('drug_reviews')->insert($review);
    }

    // get drug reviews
    public static function getDrugReviews(int $drugid)
    {
        return db('drug_reviews')->get('drugid=?', $drugid);
    }

    // get account 
    public static function getAccount(int $accountid)
    {
        return db('account')->query('ApplyId', $accountid);
    }

    // get prescribed drugs
    public static function getPrescribedDrugs(string $prescribedCode)
    {
        // get accountid
        $accountid = session()->get('account.id');
        // run query
        return db('prescribtion_codes')->get('accountid=? and prescribtion_code = ?')->bind($accountid, $prescribedCode);
    }

    // get service fee for account group
    public static function getServiceFee(int $groupid, &$group=null)
    {
        // get group information
        $group = db('account_groups')->get('groupid=?', $groupid);

        // return service fee
        return intval($group->service_fee);
    }

    // get pharmacy drugs
    public static function getPharmacyDrugs(int $pharmacyid)
    {
        return db('drugs')->get('pharmacyid=?', $pharmacyid);
    }

    // get drugs in a list
    public static function getPharmacyTypeDrugs(int $pharmacytypeid)
    {
        return db('drugs')->get('pharmacytypeid=?', $pharmacytypeid);
    }

    // get pharmacy type ID
    public static function getPharmacyTypeId(string $pharmacytype)
    {
        return db('pharmacytypes')->get('pharmacytype=?', $pharmacytype);
    }

    // get pharmacy
    public static function getPharmacy(int $pharmacyid)
    {
        return db('pharmacies')->get('pharmacyid=?', $pharmacyid);
    }

    public function applyLike($query, string $drugName)
    {
        $query->like('drug_name', '%'.$drugName.'%');
    }

    // check for drugs within a category
    public static function getDrugsInCategory(string $drugName, int $categoryId)
    {
        return db('drugs')->get('pharmacytypeid=?', $categoryId)->query('applyLike', $drugName);
    }

    // check for drugs
    public static function getDrugsIfGlobal(string $drugName)
    {
        return db('drugs')->get()->query('applyLike', $drugName);
    }

    // get drug id
    public static function getDrugId(string $drugName)
    {
        return db('drugs')->get('drug_name = ?', $drugName)->drugid;
    }

    // get orders
    public static function getOrders(int $accountid)
    {
        return db('orders')->get('accountid = ? and status = ?')->bind($accountid, 'success');
    }

    // get doctor prescribtions
    public static function getDoctorPrescribtions(int $doctorid)
    {
        return db('prescribtion_codes')->get('doctorid = ?', $doctorid)->orderby('prescribtionid','desc');
    }

    // get patient prescribtions
    public static function getPatientPrescribtions(int $accountid)
    {
        return db('prescribtion_codes')->get('accountid = ?', $accountid)->orderby('prescribtionid','desc');
    }

    // get drug info
    public static function getDrugInfo(int $drugid)
    {
        return db('drugs')->get('drugid=?', $drugid);
    }

    // get query
    public static function getPayments()
    {
        return db('payments')->get('accountid=?', session()->get('account.id'))->orderby('paymentid', 'desc');
    }

    // get cart for user
    public static function getUserShoppingCart()
    {
        return db('cart')->get('accountid=?', session()->get('account.id'))->orderby('cartid', 'desc');
    }

    // cart order complate
    public static function cartOrderComplete(string $txref, int $cartid)
    {
        return db('cart')->update(['dateDelivered' => date('Y-m-d g:i:s')])->where('txref = ? and cartid = ?')->bind($txref, $cartid);
    }

    // get pharmacy order
    public static function getPharmacyShoppingCart()
    {
        // get account id
        $accountid = session()->get('account.id');

        // get pharmacy info
        $pharmacy = self::getPharmacyInfo($accountid);

        if ($pharmacy->rows == 0)
        {
            return $pharmacy;
        }

        return db('cart')->get('pharmacyid = ?', $pharmacy->pharmacyid)->orderby('cartid', 'desc');
    }

    // get cart info
    public static function getCartInfo(int $cartid)
    {
        return db('cart')->get('cartid = ?', $cartid);
    }

    // get billing information
    public static function getBillingInformation(string $txref)
    {
        // billing info
        $billing = db('cartOrderDetails')->get('txref = ?', $txref);

        return json_decode($billing->orderDetails);
    }

    // return column
    public static function getTableColumn(string $table, int $key, string $column)
    {
        return db($table)->get()->primary($key)->{$column};
    }
}