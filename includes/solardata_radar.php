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
		
	$time_axis  = array();
	
	
	for ( $i = 0 ; $i < 12 ; $i++ )
	{
		$time_axis[$i] = date ( 'F' , strtotime('+'.$i.' month' , 0 ) );
	}
	
		
	$data_w		= array_fill ( 0 , 12 , 0 );
	$data_t		= array_fill ( 0 , 12 , 0 );
	
	$stmt 	= "SELECT date_format(timestamp,'%c')-1 as m, avg(AMsWatt)+avg(BMsWatt) AS Watt, max(ETotal)-min(ETotal) ETotal FROM solardata "
			. " WHERE date <> 0"
			. " GROUP BY date_format(timestamp,'%c') "
			. " ORDER BY TimeStamp LIMIT 100";
	
	$ok = mysql_query ( $stmt );
		
	if($ok)
	{
		while ( $row = mysql_fetch_object ( $ok ) )
		{
			$data_w[$row->m]    = round((float)$row->Watt,3) ;
			$data_t[$row->m]    = round((float)$row->ETotal,3) ;
		}
		
		$title = new title ( "\nJahresueberblick Verteilung" );
		$title->set_style( '{font-size: 20px; color: #778877}' );	
		
		$vals = array();

		for( $i=0; $i<count($data_w); $i++ )
		{
			$tmp = new solid_dot($data_t[$i]);
			$tmp->colour('#4d4d4d')->tooltip("{$time_axis[$i]}<br>#val# kWh");
			$vals[] = $tmp;
		}
		
		$area_w = new area();
		$area_w->set_width( 1 );
		$area_w->set_default_dot_style( new hollow_dot('#EFC01D', 5) );
		$area_w->set_colour( '#EFC01D' );
		$area_w->set_fill_colour( '#EFC01D' );
		$area_w->set_fill_alpha( 0.4 );
		$area_w->set_loop();
		$area_w->set_values( $vals );
		
		$line_t = new line();
		$line_t->set_values ( $data_t );
		
		
		$max = max($data_t) * 1.15;
		
		$label = array();
		for ( $i = 0 ; $i < $max ; $i = $i + round($max*0.1,-1)) $label[] = $i;
		
		
		$r = new radar_axis( $max );

		$r->set_colour( '#EFD1EF' );
		$r->set_steps(round($max*0.1,-1));
		$r->set_grid_colour( '#EFD1EF' );

		$labels = new radar_axis_labels( $label );
		$labels->set_colour( '#9F819F' );
		$r->set_labels( $labels );
		
		$spoke_labels = new radar_spoke_labels( $time_axis );

		$spoke_labels->set_colour( '#9F819F' );
		$r->set_spoke_labels( $spoke_labels );
		
		
		$tooltip = new tooltip();
		$tooltip->set_hover();

		
		
		$chart = new open_flash_chart();
		$chart->set_title( $title );	
		$chart->set_tooltip( $tooltip );
		$chart->set_bg_colour( '#ffffff' );
		$chart->set_radar_axis( $r );
		$chart->add_element( $area_w );
		
		echo $chart->toString();
	}
?>