<?php

namespace Providers;

use Moorexa\Rexa;
use account_types;

/**
 * @package Permission Provider
 * This provider should be registered in kernel/registry.php
 * In the boot array, we can access this provider via Providers
 */

class PermissionProvider
{
    private $types = [];

    /**
     * @method Boot startup 
     * This method would be called upon startup
     */
    public function boot()
    {
        // get account types
        account_types::get()->obj(function($row){
            $this->types[$row->accounttype] = $row->accounttypeid;
        });

        // end directive
        Rexa::directive('end', function(){
            return '<?php } ?>';
        });

        foreach ($this->types as $type => $id)
        {
            // create directive for account types
            Rexa::directive('is'.ucfirst($type), function() use ($id){
                return '<?php if (Moorexa\Provider::permission("canRead", "'.$id.'")) { ?>';
            });

            // create directive for account types
            Rexa::directive('isNot'.ucfirst($type), function() use ($id){
                return '<?php if (Moorexa\Provider::permission("canRead", "'.$id.'") === false) { ?>';
            });
        }

        // set bind config
        bindConfig(':myid|:fromid', session()->get('account.id'));
    }

    // can read function
    public function canRead($id)
    {
        $info = session()->get('account.info');

        if ($info->accounttypeid == $id)
        {
            return true;
        }

        return false;
    }

    public function is(string $accountType)
    {
        if (isset($this->types[$accountType]))
        {
            return $this->canRead($this->types[$accountType]);
        }

        return false;
    }
}