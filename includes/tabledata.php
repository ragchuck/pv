<?php

if( realpath( __FILE__ ) === realpath( $_SERVER[ 'SCRIPT_FILENAME' ])) {
    include_once ("../config/db_connect.php");
}
else
{
    include_once ("./config/db_connect.php");
}

$range = $_REQUEST['range'];	


if ( $_REQUEST['t'] )
{
    $time = $_REQUEST['t'];
}
else
{
    $time = time();
}
                
$values = array	(
                    'AVGWatt'		=> array ( 0 , 0 ) 
                ,	'ETotal' 		=> array ( 0 , 0 ) 
                ,	'ETotal_Soll'	=> array ( 0 , 0 ) 
                );

$date_format = array	( 		
                            'day'	=> 'd.m.Y'
                        ,	'month' => 'F Y'
                        ,	'year'	=> 'Y'
                        ,	'radar'	=> ''
                        );

//echo '<h3>' . date( $date_format[$range] , $time ) .  '</h3>';

echo '<div class="diagramm_data">';

if ( $range != 'radar' )
{
    echo '<div class="diagramm_data_navi ui-corner-all ui-widget-header">';
    
    $prev = strtotime ( ' - 1 ' . $range , $time );
    
    if( $lofi_version )
        echo '<a class="pn fleft" href="lofi/?range='.$range.'&t='.$prev.'" title="' . date( $date_format[$range] , $prev ) . '">&laquo;</a>';
    else
        echo '<a class="pn fleft" href="javascript:void(0)" onclick="load_chart( \'' . $range . '\' , ' . $prev . ' );return false;" title="' . date( $date_format[$range] , $prev ) . '">&laquo;</a>';

    $next =  strtotime ( ' + 1 ' . $range , $time );
    $dis = ( date('Ymd',time()) < date('Ymd',$next) ) ? 'disabled' : '';
    if( $lofi_version )
        echo '<a class="pn fright" href="lofi/?range='.$range.'&t='.$next.'" title="' . date( $date_format[$range] , $next ) . '" rel="' . $dis . '">&raquo;</a>';
    else
        echo '<a class="pn fright" href="javascript:void(0)" onclick="' . (($dis!='')?'':'load_chart( \'' . $range . '\' , ' . $next . ' )') . ';return false;" title="' . date( $date_format[$range] , $next ) . '" rel="' . $dis . '">&raquo;</a>';
        
    $k = array_keys( $date_format );
    $up = $k[array_search( $range , $k )+1];
    if( $lofi_version )
        echo '<h4><a href="lofi/?range='.$up.'&t='.$time.'" title="' . date( $date_format[$up] , $time ) . '">' . date( $date_format[$range] , $time ) . '</a></h4></div>';
    else
        echo '<h4><a href="javascript:void(0)" onclick="load_chart(\'' . $up . '\', ' . $time . ' );return false;" title="' . date( $date_format[$up] , $time ) . '">' . date( $date_format[$range] , $time ) . '</a></h4></div>';
}

echo '<table class="diagramm_data_table" cellspacing="0" cellpadding="0">';	
echo '<tr><th>kWh</th><th class="right">Max</th><th class="right">Zeit</th><th class="right">Ist</th><th class="right">Soll</th><th class="right">&Delta;</th><th class="right">%</th></tr>';	

$sql =	" SELECT a.date date "
    .	"	, IFNULL( round( max( etotal_max ) - min( etotal_min ) , 1 ) , 'n/a' ) AS ETotal "
    .	"	, IFNULL( round( sum( etotal_soll ) , 1 ) , 'n/a' ) AS ETotal_Soll "
    .   "   , IFNULL( round( max( etotal_max ) - min( etotal_min ) -  sum( etotal_soll ) , 1 ) , 'n/a' ) AS ETotal_Diff "
    .	"	, IFNULL( round( ( max( etotal_max ) - min( etotal_min ) ) / sum( etotal_soll ) * 100 , 1 ) , 'n/a' ) AS Perc "
    .	" FROM	( SELECT date, max( ETotal ) etotal_max, min( ETotal ) etotal_min FROM solardata GROUP BY date ) AS a "
    .	" LEFT OUTER JOIN ( SELECT date, sum( wert ) etotal_soll FROM solardata_soll GROUP BY date ) AS b "
    .	" ON date_format(a.date,'%d/%m') = date_format(b.date,'%d/%m') ";
    
$m_stmt  = "SELECT a.date time, round( etotal, 2 ) AS value "
        .  " FROM ( SELECT date, max( ETotal ) - min( ETotal ) etotal FROM solardata GROUP BY date) AS a "
        .  " %s "
        .  " ORDER BY value DESC "
        .  " LIMIT 1 ";

switch ( $range )
{
    /*************************************************************************/
    case 'day':
    /*************************************************************************/
    
        $stmt 	= $sql
                . " WHERE a.date = CAST('" . date ( 'Y-m-j' , $time ) . "' AS DATE)"
                . " GROUP BY a.date";
                
        $stmt2 = "SELECT date_format(timestamp,'%H:%i') time, round(amswatt+bmswatt) value FROM solardata WHERE date = CAST('" . date ( 'Y-m-j' , $time ) . "' AS DATE) ORDER BY value DESC LIMIT 1";

        print_row( mysql_query ( $stmt ) , "Tagesleistung" , mysql_query ( $stmt2 ) );	
        
    /*************************************************************************/
    case 'month':
    /*************************************************************************/
    
        $where_clause = " WHERE date_format( a.date , '%Y%m' ) = " . date ( 'Ym' , $time );
    
        $stmt 	= $sql
                . $where_clause
                . " GROUP BY date_format( a.date , '%Y%m' ) ";

        $stmt2 = sprintf( $m_stmt , $where_clause );
                
        print_row( mysql_query ( $stmt ) , "Monatsleistung" , mysql_query ( $stmt2 ) );
        
    /*************************************************************************/
    case 'year':
    /*************************************************************************/
    
        $where_clause = " WHERE date_format( a.date , '%Y' ) = " . date ( 'Y' , $time );
        
        $stmt 	= $sql
                . $where_clause
                . " GROUP BY date_format( a.date , '%Y' ) ";
                
        $stmt2 = sprintf( $m_stmt , $where_clause );

        print_row( mysql_query ( $stmt ) , "Jahresleistung" , mysql_query ( $stmt2 ) );
        
    /*************************************************************************/
    default:
    /*************************************************************************/
    
        $stmt 	= $sql;
        
        $stmt2 = sprintf( $m_stmt , "" );

        print_row( mysql_query ( $stmt ) , "Gesamtleistung" ,  mysql_query ( $stmt2 ) );
        
}
    
echo '</table>';


$ok = mysql_query ( "select wert from parameter where feld='lmd'" );
$lmd = mysql_result ( $ok , 0 );
$ok = mysql_query ( "select max(timestamp) from solardata" );
$lnd = mysql_result ( $ok , 0 );

$flmd = format_date_gh( $lmd );
$flnd = format_date_gh( $lnd );

echo '<small>Letzte Daten vom: ' . $flnd . ' / LMD: ' .$flmd. '</small></div>';


function print_row( $result1 , $title , $result2 )
{
    
    $units = array	(
                        'ETotal'        => 'kWh'
                    ,	'ETotal_Soll'   => 'kWh'
                    ,	'ETotal_Diff'   => 'kWh'
                    , 	'Perc'          => '%'
                    );
    if(is_resource( $result1 ))
    {
        echo 	'<tr><th class="left">' . $title . '</th>';
        
        $col1 = "";
        $col2 = "";
        
        if(is_resource( $result2 ))
        {      
            $max_val = format( @ mysql_result( $result2 , 0 , "value" ));
            $max_dat = @ mysql_result( $result2, 0 , "time" );
            
            if( $title == 'Tagesleistung' )
            {
            
                $col1= $max_val.' (W)';
                $col2= $max_dat;
            }
            else
            {
                $col1= '<a href="javascript:void(0)" onclick="load_chart(\'day\','.strtotime($max_dat).')" alt="max value for '.$title.'">'.format($max_val).'</a>';
                $col2= date( 'd.m.Y' , strtotime( $max_dat ) );
            }
        }
        else
        {
            $col1 = 'Datenbankfehler:';
            $col2 = mysql_error();
        }
        
        echo '<td class="value">' . $col1 . '</td>';
        echo '<td class="value">' . $col2 . '</td>';
        
        foreach( $units AS $key => $unit )
            echo	'	<td class="value">' . format( @ mysql_result( $result1 , 0 , $key ) ) . '</td>';
            
        echo 	'</tr>';
    }
    else
    {
        echo '<tr><th>Datenbankfehler:</th><td colspan="' . count( $units ) . '">' . mysql_error() . '</td></tr>';
    }

}

function format( $input )
{
    return str_replace( "." , "," , $input);
}

function format_date_gh( $day )
{
    $f = date( 'd.m.Y' , strtotime( $day ) );
    if( date( 'd.m.Y' , time() ) ==  $f )
        $f = 'Heute';
    elseif( date( 'd.m.Y' , strtotime( '-1 day' , time() ) ) == $f )
        $f = 'Gestern';
    return $f . date( ' H:i' , strtotime( $day ) ) ;
}

if( !$lofi_version )
{
    $output=false;
    $opt   = array();
    $opt[] = '<div id="outer_chart_modifier">';
    $opt[] = '<fieldset><legend>Chart Optionen</legend>';
    $opt[] = '<ul>';

    switch( $range ) {
    case "day":
    
        /*$opt[] = '<li>
                    <input class="chart-elements-modifier" type="checkbox" name="chart-day-max" value="line" id="chart-day-max-line" checked />
                    <label for="chart-max-line">Max-Line</label>
                </li>';*/
                
        $opt[] = '<li>
                    <input class="chart-elements-modifier" type="radio" name="chart-day-type" value="area" id="chart-day-type-area" checked />
                    <label for="chart-type-area">Area</label>
                </li>';

        $opt[] = '<li>
                    <input class="chart-elements-modifier" type="radio" name="chart-day-type" value="bar_glass" id="chart-day-type-bar_glass" />
                    <label for="chart-type-bar_glass">Glass Bar</label>
                </li>';
        $output=true;
    break;
    }
    
    $opt[] = '</ul>';
    $opt[] = '</fieldset>';
    $opt[] = '</div>';
    
    if($output)
        print implode(PHP_EOL,$opt);
}
