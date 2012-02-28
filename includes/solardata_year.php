<?php

include './ofc/php-ofc-library/open-flash-chart.php';
include '../config/db_connect.php';
    
if ( $_REQUEST['t'] )
{
    $time = (int)$_REQUEST['t'];
}
else
{
    $time = time();
}


$year = mktime( 0 , 0 , 0 , 1 , 1 , date( 'Y' , $time ) );
$lyear = mktime( 0 , 0 , 0 , 1 , 1 , date( 'Y' , $time )-1 );

$time_axis  = array();	
$ttime_axis  = array();	

for ( $i = 0 ; $i < 12 ; $i++ )
{
    $time_axis[$i] = date ( 'F' , strtotime('+'.$i.' month' , $year ) );
    $ttime_axis[$i] =  strtotime('+'.$i.' month' , $year );
    $ltime_axis[$i] =  strtotime('+'.$i.' month' , $lyear );
}


$data_curr	= array_fill ( 0 , 12 , null );
$data_last	= array_fill ( 0 , 12 , null );
$data_soll	= array_fill ( 0 , 12 , null );

$last_year = date ( 'Y' , $time ) - 1;
$curr_year = date ( 'Y' , $time );

/*********************************************************************/
// Werte für das aktuelle Jahr ermitteln

$stmt 	=	"SELECT date_format( timestamp, '%c' )-1 AS m, max( ETotal ) - min( ETotal ) AS ETotal "
        .	"  FROM solardata "
        .	" WHERE date <> 0 "
        .	"   AND date_format(date,'%Y') = $curr_year"
        .	" GROUP BY date_format( timestamp, '%c' ) "
        .	" ORDER BY TimeStamp LIMIT 100";

$ok = mysql_query ( $stmt );
    
if($ok)
    while ( $row = mysql_fetch_object ( $ok ) )
        $data_curr[$row->m]    = round((float)$row->ETotal,3) ;
        
    
/*********************************************************************/
// Werte für das letzte Jahr ermitteln

$stmt 	=	"SELECT date_format( timestamp, '%c' )-1 AS m, max( ETotal ) - min( ETotal ) AS ETotal "
        .	"  FROM solardata "
        .	" WHERE date <> 0 "
        .	"   AND date_format(date,'%Y') = $last_year"
        .	" GROUP BY date_format( timestamp, '%c' ) "
        .	" ORDER BY TimeStamp LIMIT 100";

$ok = mysql_query ( $stmt );
        
if($ok)
    while ( $row = mysql_fetch_object ( $ok ) )
        $data_last[$row->m]    = round((float)$row->ETotal,3) ;
        
/*********************************************************************/
// Soll-Werte ermitteln
        
$stmt 	=	"SELECT date_format( date, '%c' )-1 AS m, sum( wert ) AS ETotal "
        .	"  FROM solardata_soll "
//		.	" WHERE date_format(date,'%Y') = $curr_year"
        .	" GROUP BY date_format( date, '%c' ) "
        .	" ORDER BY date LIMIT 100";

$ok = mysql_query ( $stmt );
    
if($ok)
    while ( $row = mysql_fetch_object ( $ok ) )
        $data_soll[$row->m]    = round((float)$row->ETotal,3) ;
        
if	(	max( $data_curr ) == 0 && 
        max( $data_last ) == 0 &&
        max( $data_soll ) == 0
    )
{
    echo 0;
    exit;
}
    

$tooltip = new tooltip();
$tooltip->set_hover();

$title = new title ( "\nJahresueberblick" );
$title->set_style( '{font-size: 20px; color: #778877}' );	

$bar_last = new bar_glass();
$bar_last->key( "Voriges Jahr $last_year" , 12 );
$bar_last->colour( '#546656' );
$bar_last->set_tooltip( '#val# kWh' );
$data = array();
for( $i = 0 ; $i < count($data_last) ; $i++ )
{
    $bval = new bar_value($data_last[$i]);
    $bval->{"on-click"}="load_chart('month',{$ltime_axis[$i]})";
    $data[] = $bval;
}
$bar_last->set_values( $data );

$bar_curr = new bar_glass();
$bar_curr->key( "Aktuelles Jahr $curr_year" , 12 );
$bar_curr->colour( '#EFC01D' );
$bar_curr->set_alpha( 0.8 );
$bar_curr->set_tooltip( '#val# kWh' );
$data = array();
for( $i = 0 ; $i < count($data_curr) ; $i++ )
{
    $bval = new bar_value($data_curr[$i]);
    $bval->{"on-click"}="load_chart('month',{$ttime_axis[$i]})";
    $data[] = $bval;
}
$bar_curr->set_values( $data );

$lin_soll = new Line();
$lin_soll->set_key( "Sollwert $curr_year" , 11 );
$lin_soll->set_colour( '#BFA447' );
$lin_soll->set_width( 1 );
$lin_soll->set_values( $data_soll );

$max = max( max($data_last) , max($data_curr) , max($data_soll) ) * 1.15;

$y = new y_axis();
$y->set_range( 0, $max, round($max*0.1,-1) );

$x_labels = new x_axis_labels();
$x_labels->set_colour( '#333333' );
$x_labels->set_labels( $time_axis );

$x = new x_axis();
$x->set_colour( '#333333' );
$x->set_grid_colour( '#CCCCCC' );
$x->set_steps(1);
$x->set_labels( $x_labels );
                
$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->set_bg_colour( '#ffffff' );
$chart->set_tooltip( $tooltip );
$chart->add_element( $bar_last );
$chart->add_element( $bar_curr );
$chart->add_element( $lin_soll );
$chart->set_y_axis( $y );
$chart->set_x_axis( $x );
    
    
echo $chart->toString();		
        
?>