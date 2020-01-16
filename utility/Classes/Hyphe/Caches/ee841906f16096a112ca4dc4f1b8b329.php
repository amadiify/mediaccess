<?php
	class Hello extends \Hyphe\Engine {
	
    private $version = '1.0';

    public function render()
	{
	

        $name = $this->props->name;

        $students = [ 
            'frank',
            'daniel',
            'ifeanyi'
        ];

        ?>
	
            <h1>Welcome <?=$this->props->name?> i love <?=$this->props->framework?></h1>
            <small>
                <?=$this->props->children?> and my current version is <?=$this->version?>
            </small>

            <ul>
                <?php foreach ($students as $student) { ?>
                    <li><?=$student?></li>
                <?php } ?> 
            </ul>

            <h3><?=$this->goodbye($this->props->name)?></h3>
        
	<?php
    
	}

    public function goodbye($name)
	{
	
        return "GoodBye {$name}";
    
	}

	public static function ___cacheData()
	{
	  return "8840721cf7d95116cbf04ab412c5e45d";
	}
	}