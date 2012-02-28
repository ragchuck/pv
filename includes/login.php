<?php
if( isset( $_POST['pwd'] ) && md5( $_POST['pwd'] ) === "b43a6f330140e1050cad1f226af5e1be" )
{
    setcookie( "pvlogin" , "OK" , time()+60*60*24*30 /* 30 days */ , '/pv/' );
    echo "true";
}
else
{
    echo "false";
}
?>