<?PHP 

    if( !isset( $lofi_version ))
        $lofi_version = false;
    
    $basepath = realpath(dirname(__FILE__));
    
    chdir( $basepath );

	include_once ( "./config/db_connect.php");
	include_once ( "./config/config.php");
	include_once ( "./includes/functions.php");
    
    
    $path1 =  dirname( $_SERVER['SCRIPT_NAME']);
    $path2 =  str_replace("\\","/",dirname(__FILE__));
    
    $base = getPathIntersection( $path2, $path1 );
    
    
    
    $basehref = "http://{$_SERVER['HTTP_HOST']}/{$base}/";
    
    include_once ( "./includes/mobile_device_detect.php");
    
    if(!$lofi_version && mobile_device_detect() && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
        header("Location: {$basehref}/lofi/");
    }
    
    if( !isset( $_REQUEST['range'] )) $_REQUEST['range'] = 'day';
    
	echo '<?xml version="1.0"?>';
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">

<html>
	<head>
		<title><?=$BOOKMARK?></title>
		
		<meta http-equiv="PRAGMA" content="NO-CACHE">
		<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
		<meta http-equiv="EXPIRES" content="0">
		<meta http-equiv="CONTENT-TYPE" content="text/xml; charset=ISO-8859-1">
		 
		<meta name="AUTHOR" content="Martin Zoellner">
		<meta name="COPYRIGHT" content="&copy; 2010 Martin Zoellner">
		<meta name="ROBOTS" content="NONE">

        <? if($lofi_version)print('<base href="'.$basehref.'" />'); ?>
		
		<link rel="stylesheet" href="./includes/jquery/css/sunny/jquery-ui-1.8rc3.custom.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="./style.css" type="text/css" media="all" />
		<? if($lofi_version)print('<link rel="stylesheet" href="./handheld.css" type="text/css" media="screen" />');?>
		
        <? if( !$lofi_version ): ?>        
		<script type="text/javascript" src="./includes/ofc/js/json/json2.js"></script>
		<script type="text/javascript" src="./includes/swfobject/swfobject.js"></script>
		<script type="text/javascript" src="./includes/jquery/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="./includes/jquery/js/jquery-ui-1.8rc3.custom.min.js"></script>
		<script type="text/javascript" src="./includes/pvofc.js"></script>
		<script type="text/javascript" src="./includes/script.js"></script>
        <? endif; ?>

	</head>
	<body>
		<div class="wrapper">
            <? if( !$lofi_version ): ?>
                <div id="noscript">
                    <noscript>
                    <div class="ui-state-error"><strong>JavaScript deaktiviert!</strong> Klicken Sie <a alt="noscript" href="lofi/">hier</a> um die JavaScript-freie Seite zu sehen.</div>
                    </noscript>
                </div>
            <? endif; ?>
            
			<h1><?=$BOOKMARK?></h1>
			
            <? if(!$lofi_version): ?>
            
                <div id="top_info"><span>Es sind <b id="storage_anz_files">_</b> unverarbeitete Dateien vorhanden.</span></div>
                
                <div id="outer_data_load" class="ui-corner-all ui-state-highlight">
                    <a class="data_close" href="javascript:void(0)" onclick="$('#outer_data_load').hide('scale','fast');"><img src="images/cross.png"/></a>
                    <div id="data_load"></div>
                </div>
                
            <? else: ?>
            
                <div id="top_info"><span><a alt="zur&uumlck zur normalen Ansicht" href="./">Normale Ansicht (JavaScript und Flash ben&ouml;tigt)</a></span></div>
            
            <? endif; ?>
            
			<div id="content">
                <?php
                    if( !isset( $_GET['asb'] ))
                        include_once ( './includes/chart.php');			
                ?>
                <? if( !$lofi_version ): ?>
                    <div id="tabs">
                        <ul>
                            <li><a href="#asb">Anlagensteckbrief</a></li>
                            <li><a href="#diagramm_info">Info</a></li>
                            <?=($COOKIE_LOGIN)?'<li><a href="#diagramm_debug">Debug</a></li>':'';?>
                        </ul>
                        <div id="asb">                    
                            <?php
                                include_once ( './includes/asb.php');
                            ?>
                        </div>
                        <div id="diagramm_info"></div>
                        <?=($COOKIE_LOGIN)?'<div id="diagramm_debug"></div>':'';?>
                    </div>
                <? elseif( isset( $_GET['asb'] )): ?>
                    <? include_once ( './includes/asb.php'); ?>
                <? else: ?>
                    <div id="tabs">
                        <div id="diagramm_info">
                        <? include_once ( './includes/tabledata.php');?>
                        </div>
                    </div>
                <? endif; ?>
			</div>
			<div id="navigation">
			<?php	
				include_once ( './includes/navigation.php');	
			?>
			</div>
			
			
			<br style="clear:both;" />			
		</div>
        <div id="footer">
            &copy; 2010 by <a href="mailto:martin@d-zoellner.de">Martin Zoellner</a> | 
            <a href="http://teethgrinder.co.uk/open-flash-chart-2/" target="_blank">OFC 2</a> | 
            <a href="http://jquery.com/" target="_blank">jQuery</a>
            <? if( !$lofi_version ): ?>
            |  <a href="lofi/" target="_self">LoFi Version</a>
            <? endif; ?>
        </div>
	</body>
</html>
