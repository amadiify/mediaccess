<?php
namespace View;

use Request\Query;

class Wrapper
{
    // wrap wishlist
    public static function containWishlist($data, string $previous) : array
    {
        $add = url('add-wishlist/'.$data->account->accountid.'/'.$previous);
        $remove = url('remove-wishlist/'.$data->account->accountid.'/'.$previous);
        $addWish = 'oauth';

        if (!session()->has('account.id'))
        {
            $url = url('sign-in?redirectTo='.$add);
        }
        else
        {
            $accountid = session()->get('account.id');
            $check = db('wishlist')->get('id=? and addedby=?',$data->account->accountid, $accountid);

            if ($check->rows == 0)
            {
                $url = $add;
                $addWish = 'true';
            }
            else
            {
                $url = $remove;
                $addWish = 'false';
            }
        }

        return get_defined_vars();
    }

    // return fullname
    public static function getFullname(\Moorexa\DBPromise $account) : string
    {
        return ucwords($account->firstname . ' ' . $account->lastname);
    }

    // return money
    public static function money($digit)
    {
        return '₦' . number_format($digit, 2, '.', ',');
    }

    // check if user has cart items
    public static function hasCart()
    {
        if (session()->has('user.cart', $cart))
        {
            if (count($cart) > 0)
            {
                return true;
            }
        }

        return false;
    }

    // get total items in cart
    public static function totalInCart()
    {
        $cart = session()->get('user.cart');

        return count($cart);
    }

    // cart has drug
    public static function cartHasDrug(int $drugid, &$cartid=null)
    {
        if (self::hasCart() && self::totalInCart() > 0)
        {
            $cart = session()->get('user.cart');

            $hasdrug = false;

            foreach ($cart as $index => $c)
            {
                if ($c['drugid'] == $drugid)
                {
                    $hasdrug = true;
                    $cartid = $index;

                    break;
                }
            }

            if ($hasdrug)
            {
                return true;
            }
        }

        return false;
    }

    // get billing information
    public static function billingInfo(string $key, string $value)
    {
        switch ($key)
        {
            case 'country':
                return Query::getTableColumn('countries', $value, 'country');

            case 'state':
                return Query::getTableColumn('states', $value, 'state');

            case 'city':
                return Query::getTableColumn('cities', $value, 'city');
            
            case 'telephone':
                return '<a href="tel:'.$value.'" style="text-decoration:underline">'.$value.'</a>';

            case 'email':
                return '<a href="mailto:'.$value.'" style="text-decoration:underline">'.$value.'</a>';
        }

        return $value;
    }
}