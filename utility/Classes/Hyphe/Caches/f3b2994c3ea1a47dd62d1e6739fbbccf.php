<?php
	namespace Button;
	class Request extends \Hyphe\Engine {
	
    private $href = '';

    public function __construct($props, $var)
	{
	
        if (isset($props->href))
        {
            $this->href = $props->href;
        }
        else
        {
            $this->href = 'request/' . $var->who;
        }

        if (!session()->has('account.id'))
        {
            $this->href = 'sign-in?redirectTo=' . $this->href;
        }
    
	}

    public function render()
	{
	
        ?>
	
            <a href="<?=url("$this->href")?>" class="btn btn-default" style="justify-content: center;"><?=$this->props->children?></a>
        
	<?php
    
	}

	public static function ___cacheData()
	{
	  return "8f0b7a114bc15b7f12c47b540bba2fee";
	}
	}