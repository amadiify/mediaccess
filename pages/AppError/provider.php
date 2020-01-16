<?php

/**
 * AppError Provider. Gets autoloaded with class
 * @package AppError provider
 */

class AppErrorProvider extends AppError
{
    /**
     * @method Boot startup 
     * This method would be called upon startup
     */
    public function boot($next)
    {
       $this->loadProvider('App', $this)->loadCustom()->loadStatic();
       // call route! Applies Globally.
       $next();
    }

    /**
     * @method onHomeInit  
     * This method would be called upon route request to AppError/home
     */
    public function onHomeInit($next)
    {
        // route passed!
        $next();
    }

    // you can register more init methods for your view models.
}