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
    <!--********************* SITE CONTENT *********************-->
    <!--********************************************************-->
    <div class="site-content">
        <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            Start Dashboard Site Content
        ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
        <div class="dashboard-site-content">
            <div class="dashborad-menu-area">
                <div class="btn-close"><span class="icon-cross"></span></div>
                <div class="site-logo-block">
                    <a class="navbar-brand site-logo" href="<?=url("dashboard")?>">
                        <img src="<?=$assets->image("icon/icons.png")?>" alt="mediaccess logo" class="my-logo">
                    </a>  
				</div><!--~./ site-logo-block ~-->
				
                <div class="dashborad-menu-main tse-scrollable">
                    <div class="tse-content">
                        <div class="filter-tab-area">
                            <ul class="dashborad-menu nav nav-tabs" role="tablist">
                                <li><a class="active" href="<?=url("my/home")?>">
                                    <span class="icon icon-user4"></span>
                                    <span>Overview</span>
                                </a></li>
                                <li><a href="<?=url("profile-settings")?>" role="tab">
                                    <span class="icon icon-settings-12"></span>
                                    <span>Setting</span>
                                </a></li>
                                <li><a href="<?=url("orders")?>" role="tab">
                                    <span class="icon icon-shopping-cart has-badge">
                                        <?=\Moorexa\Rexa::runDirective(true,'notification','orders')?>
                                    </span>
                                    <span>Orders</span>
                                </a></li>
                                <li><a href="<?=url("gallery")?>">
                                    <span class="icon icon-photo-camera"></span>
                                    <span>Gallery</span>
                                </a></li>
                                <li><a href="<?=url("wishlist")?>">
                                    <span class="icon icon-like"></span>
                                    <span>Wishlist</span>
                                </a></li>
                                <li><a href="<?=url("reviews")?>">
                                    <span class="icon icon-chat"></span>
                                    <span>Reviews</span>
                                </a></li>
                                <li><a href="<?=url("drugs")?>">
                                    <span class="icon icon-aid-kit has-badge">
                                        <span class="badge-count">5</span>
                                    </span>
                                    <span>Drugs</span>
                                </a></li>
                                <li><a href="<?=url("app")?>" >
                                    <span class="icon icon-home4"></span>
                                    <span>Home</span>
                                </a></li>
                                <li><a href="<?=url("logout")?>">
                                    <span class="icon icon-logout-1"></span>
                                    <span>logout</span>
                                </a></li>
                            </ul>
                        </div><!--~./ filter-tab-area ~-->
                    </div>
                </div><!--~./ dashborad-menu-main ~-->
			</div>
			
			<div class="dashborad-contant-area">
                <!--~~~~~ Start Dashborad Header ~~~~~--> 
                <header class="dashborad-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-lg-4 col-md-4">
                                <div class="header-search-form">
                                    <form class="search-form" action="#" method="get">
                                        <div class="input-group">
                                            <input name="s" class="form-controllar" placeholder="Type in to search..." type="search">
                                        </div>
                                    </form>
                                </div><!--~~./ header-search-form ~~-->
                            </div>

                            <div class="col-lg-2 col-md-2">
                                <?php if($thisModel->info->accounttypeid != 7) { ?>
                                    <div class="row text-right" style="align-items:center;">
                                        <div class="col-md-6">Avaliable</div>
                                        <div class="col-md-6">
                                            <?php $checked = 'checked';?>
                                            <?php if($thisModel->info->isavaliable == 0) { ?>
                                                <?php $checked = null;?>
                                            <?php } ?>
                                            <input type="checkbox" <?=$checked?> data-style="ios" data-on="Yes" data-off="No" data-toggle="toggle" data-width="80" data-onstyle="success" data-offstyle="danger" id="toggleAvalibility" data-accountid="<?=$thisModel->id?>" onchange="avaliabilityChanged(this)">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="header-right">
                                    <div class="user-registration-area dropdown">
                                        <a class="user-reg-btn" href="<?=url("my")?>">
                                            <div class="user-thumb">
                                                <img src="<?=$assets->image("$thisModel->profile_image")?>" alt="img">
                                            </div>
                                            <div class="user-name"><?=$thisProvider->firstname?></div>
                                        </a>
                                    </div><!--~~./ user-registration-area ~~-->
                                    <div class="user-tools-right">
                                        <ul class="list">
                                            <li><a href="<?=url("profile-settings")?>"><span class="icon-settings4"></span></a></li>
                                            <li><a href="<?=url("account-info")?>"><span class="icon-info"></span></a></li>
                                            <li><a href="<?=url("logout")?>"><span class="icon-logout-1"></span></a></li>
                                        </ul>
                                    </div><!--~~./ user-tools-right ~~-->
                                </div><!--~~./ header-right ~~-->
                            </div>
                        </div>
                    </div>
				</header><!--~./ end dashborad header ~-->
				
				<!--~~~~~ Start Dashborad Contant ~~~~~--> 
                <div class="dashborad-contant tab-content">
                    <?=\Moorexa\Rexa::runDirective(true,'partial','breadcum')?>