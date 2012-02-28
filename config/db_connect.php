<?PHP

$host = "localhost";
$user = "solardb";
$database = "solardb";
$pass = "Test123";
$db = @ mysql_connect($host, $user, $pass)
	or die ( 'Datenbankverbindung fehlgeschlagen: ' . mysql_error () );
$db_select = @ mysql_select_db($database, $db)	
    or die ( 'Auswahl der Datenbank fehlgeschlagen: ' . mysql_error () );


?>