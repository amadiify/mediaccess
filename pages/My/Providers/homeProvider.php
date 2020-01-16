<?php

/**
 * @package Home View Page Provider
 * @author Moorexa <moorexa.com>
 */

class MyHomeProvider extends MyProvider
{
    /**
     * @method viewDidEnter
     * 
     * #called upon rendering view
     */
    public function viewDidEnter()
    {
        
    }

    /**
     * @method viewWillEnter
     * 
     * #called before rendering view
     */
    public function viewWillEnter($next)
    {
        // route passed
        $next();
    }
}