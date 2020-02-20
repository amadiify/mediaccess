<?php

/**
 * App Provider. Gets autoloaded with class
 * @package App provider
 */

class AppProvider extends App
{
    // define search form placeholder
    public $placeholder = 'What are you looking for?';

    /**
     * @method Boot startup 
     * This method would be called upon startup
     */
    public function boot($next)
    {
        Moorexa\Rexa::preload('alert');
        Moorexa\Rexa::preload('alert-danger');

        if (session()->has('account.token', $token))
        {
            WekiWork\Http::header('x-medi-token:'.$token);
        }
        
        // load css
        $this->loadCss([
            'fontawesome.min2.css',
            'icomoon2.css',
            'plugins.css',
            'style.css',
            'reset.css'
        ]);

        // load js
        $this->loadJs([
            'modernizr.min.js',
            'jquery.min.js' => [
                'deffer' => false
            ],
            'bootstrap.min.js' => [
                'deffer' => false
            ],
            'popper.min.js',
            'owl.carousel.min.js',
            'masonary.min.js',
            'jquery.trackpad-scroll-emulator.min.js',
            'ResizeSensor.min.js',
            'theia-sticky-sidebar.min.js',
            'youtube-video.js',
            'wan-spinner.js',
            'rater.min.js',
            'jquery-steps.min.js',
            'rangeslider.min.js',
            'kinetic.js',
            'jquery.final-countdown.min.js',
            'jquery.datetimepicker.full.min.js',
            'jquery.validate.min.js',
            'plugins.js',
            'markerclusterer.js',
            'maps.js',
            'main.js',
            'reset.js',
            'scripts@bundle' => 'bundle.js'
        ]);

       // call route! Applies Globally.
       $next();
    }

    /**
     * @method onHomeInit  
     * This method would be called upon route request to App/home
     */
    public function onHomeInit($next)
    {
        // route passed!
        $next();
    }

    // you can register more init methods for your view models.
    public function onDrugInit($next)
    {
        // save previous page
        $previous = uri()->previous();

        if (is_object($previous))
        {   
            $previous = $previous->link;

            if (strpos($previous, 'cart') === false)
            {
                session()->set('continue-shopping-link', $previous);
            }
        }

        $next();
    }
}