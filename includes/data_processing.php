<?php
/**
 *
 *@desc: Script to extract and import Zip-Archives from SMA Sunny WebBox
 *@author: Martin Zoellner
 *@date: 12.3.2010
 *
 */
 
 
/**
 *
 *Functions:
 *
 */ 
 

include "./data_processing_config.php";

/* set to the user defined error handler */
$old_error_handler = set_error_handler("ErrorHandler");

/* file zu extract */
$ZipFile = $_POST['file'];

include "./load_zipfile.php";

print_r(json_encode($put));

?>