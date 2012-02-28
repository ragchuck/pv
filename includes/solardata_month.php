<?php

include './ofc/php-ofc-library/open-flash-chart.php';
include '../config/db_connect.php';

if ( $_REQUEST['t'] )
{
    $time = $_REQUEST['t'];
}
else
{
    $time = time();
}
    
$time_axis  = array();
$ttime_axis  = array();

$month = mktime( 0 , 0 , 0 , date( 'n' , $time ) , 1 , date( 'Y' , $time ) );

$daysinmonth = date( 't' , $month );

for ( $i = 0 ; $i < $daysinmonth ; $i++ )
{
    $time_axis[$i] = date ( "d.m.Y" , strtotime('+'.$i.' day' , $month ) );
    $ttime_axis[$i] = strtotime('+'.$i.' day' , $month );
}

$data_curr	= array_fill ( 0 , $daysinmonth , null );
$data_soll	= array_fill ( 0 , $daysinmonth , null );

$MaxETotal		= 0;
$MinETotal		= 99999999;

$stmt 	= "SELECT date_format(a.Date,'%e')-1 d, a.Date, min(a.ETotal) MinETotal, max(a.ETotal) MaxETotal, b.wert SollETotal "
        . "  FROM solardata a LEFT OUTER JOIN solardata_soll b ON date_format(a.Date,'%d/%m')=date_format(b.Date,'%d/%m')"
        . " WHERE date_format(a.Date,'%m/%Y') = date_format(CAST('" . date ( 'Y-n-j' , $time ) . "' AS DATE),'%m/%Y')"
        . " GROUP BY a.Date"
        . " ORDER BY a.TimeStamp LIMIT 100";
        
$ok = mysql_query ( $stmt );
//print($stmt);
if(!$ok)
{
    echo 0;
    exit;
}

$i=0;
while ( $row = mysql_fetch_object ( $ok ) )
{
    $data_curr[$row->d] = round((float)$row->MaxETotal-(float)$row->MinETotal,3) ;
    $data_soll[$row->d] = round((float)$row->SollETotal,3) ;
    
    $MaxETotal   = max( round((float)$row->MaxETotal,3) , $MaxETotal) ;
    $MinETotal   = min( round((float)$row->MinETotal,3) , $MinETotal) ;
    
    $i++;
}

if( defined( $_REQUEST['debug'] ) )
{
    print_r($data_curr);
    print_r($data_soll);
}

if ( $i == 0 )			
{
    echo 0;
    exit;
}

$title = new title( "\n" . date('F Y' , $time ) . " (". round($MaxETotal-$MinETotal,2) . " kWh)" );
$title->set_style( '{font-size: 20px; color: #778877}' );	

    
$tooltip = new tooltip();
$tooltip->set_hover();

$bars_curr = new bar_glass();
$bars_curr->set_key( 'Ist Tagesleistung (kWh)', 10 );
$bars_curr->set_colour( '#EFC01D' );
$bars_curr->set_alpha( 0.8 );
$bars_curr->set_tooltip( '#val# kWh' );

for( $i = 0 ; $i < count($data_curr) ; $i++ )
{
    $bval = new bar_value($data_curr[$i]);
    $bval->{"on-click"}="load_chart('day',{$ttime_axis[$i]})";
    if( $data_soll[$i] > 0 )
        $perc = round($data_curr[$i]/$data_soll[$i]*100,1);
    else
        $perc = 0;
    $bval->set_tooltip($data_curr[$i] . ' kWh - ' . $perc . ' %');
    if( $data_curr[$i] == max($data_curr))
        $bval->set_colour( '#ef4747' );
    $bars_curr->append_value( $bval );
}




$line_soll = new line();
$line_soll->set_values( $data_soll );
$line_soll->set_colour( '#BFA447' );
$line_soll->set_width( 2 );
$line_soll->set_key( 'Soll Tagesleistung (kWh)', 10 );
$line_soll->set_tooltip( "#val# kWh" );

$max = max( $data_curr ) * 1.15;

$y = new y_axis();
$y->set_range( 0, $max, round($max*0.1,0) );


$x_labels = new x_axis_labels();
$x_labels->set_vertical();
$x_labels->set_colour( '#333333' );
$x_labels->set_labels( $time_axis );

$x = new x_axis();
$x->set_colour( '#333333' );
$x->set_grid_colour( '#ffffff' );
$x->set_labels( $x_labels );


$chart = new open_flash_chart();
$chart->set_tooltip( $tooltip );
$chart->set_title( $title );	
if( max( $data_soll ) > 0 )
    $chart->add_element( $line_soll );	
$chart->add_element( $bars_curr );	
$chart->set_bg_colour( '#ffffff' );
$chart->set_y_axis( $y );
$chart->set_x_axis( $x );

echo $chart->toString();

?>