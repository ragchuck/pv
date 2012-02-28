<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>Photovoltaik - Daten Laden</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="../style.css" type="text/css" media="all" />
	</head>
<body>
<h1>Photovoltaik - Daten laden</h1>
<?php
    
    require_once "./data_processing_config.php";
    require_once ( './functions.php' );
    
    $arFiles	= array_filter ( scandir( '../daten/' ), 'fileFilterZIP' );	
    sort($arFiles);
    
    $anzFiles = count($arFiles);
    $aktFile = 1;

    $put = array();
?>
<h2>Es sind <?=$anzFiles;?> Dateien vorhanden</h2>
<?php

    if( $anzFiles == 0 )
        die("<p>keine neuen Dateien vorhanden</p></body></html>");
        
    require_once './ProgressBar/ProgressBar.class.php';
    
    $bar = new ProgressBar();
    $bar->setMessage('loading ...');
    $bar->setAutohide(true);
    $bar->setSleepOnFinish(0);
    $bar->setForegroundColor('#3F41FF');
    
    $bar->initialize($anzFiles); //print the empty bar

    
    foreach( $arFiles AS $ZipFile )
    {
        
        include "./load_zipfile.php";

        $bar->increase(); //calls the bar with every processed element

        $bar->setMessage("File $aktFile/$anzFiles: $ZipFile");
        
        $aktFile++;
    }
    
    print( $put["message"] );
?>
</body>
</html>
