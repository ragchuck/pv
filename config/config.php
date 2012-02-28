<?PHP 

$stmt	= "SELECT `feld`, `wert` "
		. "FROM `parameter` ";
		
$sql = mysql_query($stmt);



while ( $row = mysql_fetch_assoc ( $sql ) )
	$$row['feld'] = $row['wert'];
    
    
$COOKIE_LOGIN = ( isset( $_COOKIE['pvlogin'] ) && $_COOKIE['pvlogin'] === "OK" );

?>