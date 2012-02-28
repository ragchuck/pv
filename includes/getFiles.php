<?php
require_once ( './functions.php' );
$arFiles	= array_filter ( scandir( '../daten/' ), 'fileFilterZIP' );	
sort($arFiles);
print_r(json_encode($arFiles));
?>