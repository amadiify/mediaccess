<?php

$kernel->directives([

    // loading directives from a class

    'while'      => [ Moorexa\Rexa::class, '_while' ],
    'foreach'    => [ Moorexa\Rexa::class, '_foreach' ],
    'for'        => [ Moorexa\Rexa::class, '_for' ],
    'if'         => [ Moorexa\Rexa::class, '_if' ],
    'elseif'     => [ Moorexa\Rexa::class, '_elseif' ],
    'else'       => [ Moorexa\Rexa::class, '_else' ],
    'endif'      => [ Moorexa\Rexa::class, '_endif' ],
    'endfor'     => [ Moorexa\Rexa::class, '_endfor' ],
    'endforeach' => [ Moorexa\Rexa::class, '_endforeach' ],
    'endwhile'   => [ Moorexa\Rexa::class, '_endwhile' ],
    'fetch'      => [ Moorexa\DB::class,   'fetchRows' ],
    'endfetch'   => [ Moorexa\DB::class,   'fetchRowsEnd' ],
    'partial'    => [ Moorexa\View::class, 'loadPartial' ],
    'getrows'    => [ Moorexa\DB::class,   'getRows' ],

    // Using callback functions

    'setdefault' => function() :string
    {
        return '<!--Default-->'; 
    },

    /**
     *@method csrf token directive 
     *@return string
     */

    'csrf' => function() : string
    {  
        return csrf_token(); 
    },

    /**
     *@method form method directive 
     *@return string
     */

    'method' => function(string $method) : string
    { 
        return requestMethod($method); 
    },

    /**
     *@method php opening tag directive
     *@return string 
     */

    'php' => function() : string
    { 
        return '<?php ' . "\n // PHP starts here \n"; 
    },

    /**
     *@method php closing tag directive 
     *@return string
     */
    'endphp' => function() : string
    { 
        return "\n // PHP ends here \n ?>"; 
    },

    /**
     *@method html directive. close php tag
     *@return string 
     */

    'html' => function() : string
    { 
        return '?>' . "\n"; 
    },

    /**
     *@method html directive. open php tag
     *@return string
     */
    'endhtml' => function() : string
    { 
        return '<?php ' . "\n // PHP starts here \n";  
    },
    'model' => function(string $model)
    {
        return viewModel($model);
    },
    'request' => function(string $method)
    {
        return requestMethod($method);
    }
], 


/**
 *@package Injecting all directives within a class
 *@return void
 */
function($directives) : void
{
    // inject directives
    $directives->inject(SmartRow\Directives::class);
    
});
