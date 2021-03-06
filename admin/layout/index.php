<!DOCTYPE html>
<html lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo backend::getTitle(); ?> - Backend</title>
	<?php echo layout::getCSS(); ?>
</head>

<body>
<div id="wrapper">
	<div id="navi">
		<?php echo backend::getNavi(); ?>
	</div><!--end #navi-->
    
    <div id="user-mobil">
        <span class="fa fa-chevron-down"></span>
        <h4>CP</h4>
    </div>
	
    <div id="wrap">
        <div id="subnavi">
            <div id="user">
            	
                <a href="http://dynao.de" target="_blank">
                	<img src="layout/img/logo.png" alt="Logo" />
                </a>
                    
                <h3><?php echo dyn::get('user')->get('firstname')." ".dyn::get('user')->get('name'); ?></h3>
                <a href="<?php echo dyn::get('hp_url'); ?>" target="_blank"><?php echo lang::get('visit_site'); ?></a>
                
                <a href="index.php?logout=1" class="fa fa-lock logout"> <span>Logout</span></a>
            
            </div><!--end #user-->
            
            <h1><?php echo backend::getPageName(); ?></h1>
            
            <div id="mobil"><?php echo backend::getPageName(); ?></div>
            <?php echo backend::getSubnavi(); ?>
            
        </div><!--end #subnavi-->
        
        <div id="content">
            <?php echo dyn::get('content'); ?>		
        </div><!--end #content-->
        
        <div class="clearfix"></div>
    </div><!--end #wrap-->
	
	<div id="tools">
	
		<a id="trash" data-toggle="tooltip" data-placement="bottom" data-original-title="Nicht in der Beta verfügbar!" href=""></a>
        
        <?php 
		if($addonNavi = backend::getAddonNavi()) {
        	echo '<div id="addon-mobil">'.lang::get('addon_navi').'</div>';
        	echo $addonNavi; 
		}
		?>
        
		
	</div><!--end #tools-->
</div>
<?php echo layout::getJS(); ?>
</body>
</html>