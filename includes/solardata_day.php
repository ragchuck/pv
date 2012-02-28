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

if(isset( $_GET['offset'] ) && !empty($_GET['offset']))
    $time = strtotime( $_GET['offset'] , $time );
    
$time_axis = array();
$data_watt = array();
//$data_max = array();
$data_tot = array();
/*
$stmt 	= "SELECT a.TimeStamp AS Time,  a.AMsWatt+a.BMsWatt AS Watt, b.AMsWatt+b.BMsWatt AS AVGWatt, a.ETotal FROM solardata a, solardata b "
        . " WHERE a.Date = CAST('" . date ( 'Y-n-j' , $time ) . "' AS DATE)"
        . "   AND b.Date = 0 "
        . "	  AND a.art = 'STD' "
        . "	  AND b.art = 'AVG' "
        . "   AND date_format(a.timestamp,'%T') = date_format(b.timestamp,'%T') "
        . "   AND date_format(a.timestamp,'%c') = date_format(b.timestamp,'%c') "
        . " ORDER BY a.TimeStamp LIMIT 200 ";
*//*
$stmt   = "SELECT a.TimeStamp AS Time, a.AMsWatt + a.BMsWatt AS Watt, max( b.AMsWatt + b.BMswatt) as maxWatt, a.ETotal "
        . "FROM solardata a, solardata b "
        . "WHERE a.Date = CAST( '" . date ( 'Y-n-j' , $time ) . "' AS DATE ) "
        . "and date_format(a.Date,'%c')=date_format(b.Date,'%c') "
        . "and date_format(a.TimeStamp,'%k%i')=date_format(b.TimeStamp,'%k%i') "
        . "AND a.art = 'STD' AND b.art = 'STD' "
        . "group by a.TimeStamp, a.AMsWatt + a.BMsWatt , a.ETotal "
        . "ORDER BY a.timeStamp";
*/
$stmt   = "SELECT TimeStamp AS Time, AMsWatt + BMsWatt AS Watt, ETotal "
        . "FROM solardata "
        . "WHERE date = '" . date ( 'Y-n-j' , $time ) . "'"
        . "AND art = 'STD' "
        . "ORDER BY timeStamp";
        
$ok = mysql_query ( $stmt );

if(!$ok)
{    
	echo '[' . mysql_errno(). ']' . PHP_EOL  . mysql_error() . PHP_EOL ;
	echo $ok . PHP_EOL . $stmt;
    exit;
}
else
{
    while ( $row = mysql_fetch_object ( $ok ) )
    {
        $time_axis[] = date ( "H:i" , strtotime ( $row->Time ) );
        $data_watt[]   = round((float)$row->Watt,3) ;
        //$data_max[]    = round((float)$row->maxWatt,3) ;
        $data_tot[]    = round((float)$row->ETotal,3) ;
    }
    
    $chart = new open_flash_chart();
    
    if ( count ( $time_axis ) == 0 )			
    {
        echo 0;
        exit;
    }
    
    
    $title = new title( "\n" . date('l dS F Y' , $time ) . " (". round(max($data_tot)-min($data_tot),2) . " kWh)" );
    $title->set_style( '{font-size: 20px; color: #778877;}' );	
        
    
    $tooltip = new tooltip();
    $tooltip->set_hover();
    
    $sline = new scatter_line( '#3D3D3D' , 3 );
    $def = new hollow_dot();
    $def->size(0)->halo_size(0);
    $sline->set_default_dot_style( $def );
    $sline->set_key( 'Leistung (W)' , 11 );
    
    $v = array();
    foreach ( $data_watt AS $key => $val )
    {
        $v[] = new scatter_value( $key   , $val );
        $v[] = new scatter_value( $key+1 , $val );
    }		
    $sline->set_values( $v );
    
    
     
    
    $bars_curr = new bar_glass();
    $bars_curr->set_key( 'Leistung (W)', 10 );
    $bars_curr->set_colour( '#EFC01D' );
    $bars_curr->set_alpha( 0.8 );
    $bars_curr->set_tooltip( '#val# W' );

    for( $i = 0 ; $i < count($data_watt) ; $i++ )
    {
        $bval = new bar_value($data_watt[$i]);
        if( $data_watt[$i] == max($data_watt))
        {
            $bval->set_tooltip( "Tages-Spitzenwert:<br>#val# W um {$time_axis[$i]} Uhr" );
            $bval->set_colour( '#ef4747' );
        }
        $bars_curr->append_value( $bval );
    }
    
    // PEAK
    $max_val = max ( $data_watt );
    $i = array_search ( $max_val , $data_watt );
    $s = new star( $data_watt[ $i ] );
    $s->tooltip( "Tages-Spitzenwert:<br>#val# W um #x_label# Uhr" );
    $data_watt[ $i ] = $s->size(6)->halo_size(3)->colour('#ff0000');
    
    /*		
    $line_max_default_dot = new dot();
    $line_max_default_dot->size(3)->halo_size(2)->colour('#3D5C56');
    */
    
	/*
    $line_max = new line();
    $line_max->set_default_dot_style($line_max_default_dot);
    $line_max->set_values( $data_max );
    $line_max->set_colour( '#FEE88F' );
    $line_max->set_width( 1 );	
    $line_max->set_key( 'Max (W)', 10 );
    $line_max->set_tooltip( "#val# W" );
	*/
    
    $line_watt_default_dot = new dot();
    $line_watt_default_dot->size(4)->colour('#f00000');

    $line_watt = new area();
    $line_watt->set_default_dot_style($line_watt_default_dot);
    $line_watt->set_values( $data_watt );
    $line_watt->set_colour( '#4D4D4D' );
    $line_watt->set_fill_colour( '#EFC01D' );
    $line_watt->set_fill_alpha( 0.75 );
    $line_watt->set_width( 2 );	
    $line_watt->set_key( 'Leistung (W)', 10 );
    $line_watt->set_tooltip( "#val# W" );
    
    
    $line_tot_default_dot = new dot();
    $line_tot_default_dot->size(4)->halo_size(2);

    $line_tot = new line();
    $line_tot->set_default_dot_style($line_tot_default_dot);
    $line_tot->set_values( $data_tot );
    $line_tot->set_colour( '#A0A000' );
    $line_tot->set_width( 2 );
    $line_tot->set_key( 'Gesamt (kWh)', 10 );
    $line_tot->set_tooltip( "#val# kWh" );
   

    
    
    $max = max( max($data_watt) , /*max($data_max) ,*/ $max_val ) * 1.15;
    
    $y = new y_axis();
    $y->set_range( 0, $max, round($max*0.1,-1) );
    
    
    
    $x_labels = new x_axis_labels();
    $x_labels->set_vertical();
    $x_labels->set_steps( 6 );
    $x_labels->set_colour( '#333333' );
    $x_labels->set_labels( $time_axis );

    $x = new x_axis();
    $x->set_colour( '#333333' );
    $x->set_grid_colour( '#ffffff' );
    $x->set_offset( false );
    $x->set_steps(3);
    // Add the X Axis Labels to the X Axis
    $x->set_labels( $x_labels );
    

    $chart = new open_flash_chart();
    $chart->set_tooltip( $tooltip );
    $chart->set_title( $title );
    //$chart->add_element( $line_max );
    $chart->add_element( $line_watt );
    $chart->add_element( $bars_curr );
    //$chart->add_element( $line_tot );
    //$chart->add_element( $sline );
    $chart->set_bg_colour( '#ffffff' );
    $chart->set_y_axis( $y );
    $chart->set_x_axis( $x );
    
    

    echo $chart->toString();

}

?>