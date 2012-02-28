<div id="diagramm">
	<div id="outer_diagramm">
        <div id="diagramm_chart">
            <? if( $lofi_version && isset($_REQUEST['range']) && !empty($_REQUEST['range'])): ?>
                
                <?                    
                    $range = $_REQUEST['range'];
                    
                    if ( isset($_REQUEST['t']) && !empty($_REQUEST['t']) )
                    {
                        $time = $_REQUEST['t'];
                    }
                    else
                    {
                        $time = time();
                    }
                    
                    $date_format = array	( 		
                                                'day'	=> '-Y-m-d'
                                            ,	'month' => '-Y-m'
                                            ,	'year'	=> '-Y'
                                            ,	'radar'	=> ''
                                            );
                    
                    $file = "./images/charts/chart-" . $range . date( $date_format[$range] , $time) . ".png";

                    echo "<img alt='$file' src='$file' />";
                ?>
                
                
                
            <? endif; ?>        
        </div>
    </div>
</div>