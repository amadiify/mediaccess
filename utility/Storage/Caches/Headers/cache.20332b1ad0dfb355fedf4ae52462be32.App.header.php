<?=\Moorexa\Rexa::runDirective(true,'setdefault')?>
<!DOCTYPE html>
<html lang="en-us">
<head> 
	<title><?=$package->name?></title>

	<!-- meta tags -->
	<?=\Moorexa\Rexa::runDirective(true,'partial','meta-tags.md')?>

	<!-- link tags -->
	<link rel="canonical" href="<?=url($package->url)?>">
	<!-- favicon -->
	<link rel="icon" type="image/png" href="<?=$package->icon?>" sizes="32x32">
	
	<!-- css -->
	<?=$assets->loadCss($__css)?>
  
</head>
	
<body>
    
<!--********************************************************-->
    <!--********************* SITE HEADER **********************-->
    <!--********************************************************-->
    <header class="site-header header-style-one">
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            Start Site Navigation
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <div class="site-navigation">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="navbar navbar-expand-lg navigation-area">
                            <div class="site-logo-block">
                                <a class="navbar-brand site-logo" href="<?=url("")?>">
                                    <img src="<?=$assets->image("mediaccess.png")?>" alt="logo">
                                </a>  
                            </div><!--~./ site-logo-block ~-->
                            
                            <div class="mainmenu-area">
                                <nav class="menu">
                                    <?php $view = uri()->view;?>
                                    <ul id="nav">
                                        <li><a href="<?=url("")?>">Home</a>
                                        </li>
                                        <li  class="<?=($view == 'list' || $view == 'about' ? 'active-link dropdown-trigger mega-menu' : 'dropdown-trigger mega-menu')?>">
                                            <a href="#">Explore</a>
                                            <div class="mega-menu-content carousel-nav-dots owl-carousel">
                                                <?php $account_types = db('account_types')->get('showpublic = 1');?>
												<?php
$account_types = $account_types;if ($account_types->rows > 0){
while ($row = $account_types->obj())
{ ?>
													<a class="cat-item" href="<?=url("list/$row->accounttype")?>">
														<div class="cat-thumb">
															<img src="<?=image($row->image)?>" alt="cat">
														</div>
														<span class="cat-name"><?=ucwords($row->accounttype)?></span>
													</a>
												<?php }} ?>

                                            </div><!--/.mega-menu-content-->
                                        </li>
                                        <li  class="<?=($view == 'how-it-works' ? 'active-link' : '')?>"><a href="<?=url("how-it-works")?>">How it works</a>
                                        </li>
                                        <li class="dropdown-trigger">
                                            <a href="#">Store</a>
                                            <ul class="dropdown-content">
                                                <li><a href="<?=url("about-us")?>">About us</a></li>
                                                <li><a href="<?=url("pricing")?>">Pricing</a></li>
                                            </ul>
                                        </li>
                                        <li  class="<?=($view == 'buydrug' ? 'active-link' : '')?>"><a href="<?=url("buydrug")?>">Drugs</a></li>
                                        <li  class="<?=($view == 'contact' ? 'active-link' : '')?>"><a href="<?=url("contact")?>">Contact</a></li>
                                    </ul>
                                </nav><!--/.menu-->
                            </div><!--~./ mainmenu-wrap ~-->
                            
                            <div class="header-navigation-right">
                                <div class="header-card-area">
                                    <?php $hasCart = Wrapper::hasCart();?>
                                    <a  href="<?=($hasCart ? url('cart') : '#')?>">
                                        <span class="icon-paper-bag"></span>
                                        <?php if($hasCart) { ?>
                                            <sup><?=Wrapper::totalInCart()?></sup>
                                        <?php } ?>
                                    </a>
                                </div><!--~./ site-logo-block ~-->
                                
                                <div class="search-wrap">
                                    <div class="search-btn" data-toggle="modal" data-target="#header_search_model">
                                        <span class="icon icon-search32"></span>
                                        <span class="text">Search</span>
                                    </div>
                                </div><!--~./ search-wrap ~-->
                                
                                
                                <div class="user-registration-area dropdown">
                                    <?php if(!session()->has('account.id')) { ?>
                                        <a class="user-reg-btn" href="<?=url("app/sign-in")?>">
                                            <span class="icon icon-user-1"></span>
                                            <span class="text">Account</span>
                                        </a>
                                    <?php } else { ?>
                                        <?php $user = session()->get('account.info');?>
                                        <a class="user-reg-btn" href="<?=url("my/home")?>">
                                            <span class="icon icon-user-1"></span>
                                            <span class="text"><?=ucwords($user->firstname)?></span>
                                        </a>
                                    <?php } ?>
                                </div>
                                <div class="add-listing-area">
                                <?php if(!session()->has('account.id')) { ?>
                                    <a class="btn btn-default" href="<?=url("app/register")?>">+ register </a>
                                <?php } else { ?>
                                    <a class="btn btn-default" href="<?=url("my/logout")?>"> logout </a>
                                <?php } ?>
                                </div>
                            </div><!--~./ header-navigation-right ~-->
                        </div><!--~./ navigation-area ~-->
                    </div>
                </div>
            </div>
        </div><!--~./ site-navigation ~-->
        
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            Start Mobile Menu
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <div class="mobile-menu">
            <a class="mobile-logo" href="<?=url("")?>">
                <img src="<?=$assets->image("icons.png")?>" alt="logo">
            </a>
        </div><!--~~./ end mobile menu ~~-->
        
    </header>
    <!--~~~ Sticky Header ~~~-->
	<div id="sticky-header"></div><!--~./End site header ~-->
	

	<!--********************************************************-->
    <!--********************* SITE CONTENT *********************-->
    <!--********************************************************-->
    <div class="site-content">
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            Start Frontpage Banner Section
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->