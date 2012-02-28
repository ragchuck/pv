<? if( $lofi_version ): ?>
    <div id="lofi_nav">
        <h3>Diagramm-Auswahl</h3>
        <div>
            <ul id="chart_menu_main">
                <li><a href="lofi/?range=day" class="nav_link">Heute<span class="vis">&raquo;</span></a></li>
                <li><a href="lofi/?range=month" class="nav_link">Aktueller Monat<span class="vis">&raquo;</span></a></li>
                <li><a href="lofi/?range=year" class="nav_link">Dieses Jahr<span class="vis">&raquo;</span></a></li>
                <li><a href="lofi/?range=radar" class="nav_link">Gesamt<span class="vis">&raquo;</span></a></li>
                <li>&nbsp;</li>
                <li><a href="lofi/?asb" class="nav_link">Anlagensteckbrief<span class="vis">&raquo;</span></a></li>
            </ul>
        </div>
    </div>
<? else: ?>
    <div id="accordion">
        <h3><a href="#">Diagramm-Auswahl</a></h3>
        <div>
            <ul id="chart_menu_main">
                <li><a href="javascript:void(0)" class="nav_link" onclick="load_chart( 'day' );">Heute<span class="vis">&raquo;</span></a></li>
                <li><a href="javascript:void(0)" class="nav_link" onclick="load_chart( 'month' );">Aktueller Monat<span class="vis">&raquo;</span></a></li>
                <li><a href="javascript:void(0)" class="nav_link" onclick="load_chart( 'year' );">Dieses Jahr<span class="vis">&raquo;</span></a></li>
                <li><a href="javascript:void(0)" class="nav_link" onclick="load_chart( 'radar' );">Gesamt<span class="vis">&raquo;</span></a></li>
            </ul>
            <div id="outer_chart_date_picker">
                <span id="chart_date_picker_title">Direktwahl:</span>
                <input type="text" id="chart_date_picker" />
            </div>
        </div>
        <h3><a href="#">Wetter</a></h3>
        <div>
            <div class="wetterbox">
            <a href="http://www.wetter24.de/" target="_blank" title="Wetter24: Ihr Wetter-Dienst"><img src="http://www.wetter24.de/meteo/hptool/logo_wetter24.png" alt="" width="120" height="32" border="0"></a><br/><div style="background-color:#FEEEBD;color:#000000;font-family:Tahoma,Arial,Verdana,Times New Roman;font-weight:normal;font-size:10px;;width:120px;text-align:center"><a href="http://www.wetter24.de/de/home/wetter/weltwetter/ortewetter/stadt/49X7616/forstinning.html?cityID=49X7616" target="_blank" style="color:#000000;font-family:Tahoma,Arial,Verdana,Times New Roman;font-weight:normal;font-size:10px;" title="Ausführliche Wetter-Vorhersage für Forstinning">Wetter Forstinning</a></div><iframe  width="120" height="221" scrolling="no" frameborder="0" src="http://www.wetter24.de/meteo/hptool/index.php?cid=49X7616&cityName=Forstinning&l=de&style=11&v=de&ver=2&c1=000000&c2=FEEEBD&c3=000000&c4=FEEEBD&c5=000000&c6=FEEEBD&c7=FEEEBD&f1a=3&f1b=1&f2a=1&f2b=2&f3a=1&f3b=1&ct1=8&ct2=1&ct3=6&ct4=11&ct5=12&fcd=0"></iframe><div style="height:14px;width:120px;text-align:center;background-color:#f8b920"><a href="http://www.wetter24.de/" target="_blank" style="text-decoration: none;color:#000000;font-family:Verdana,Arial,Tahoma,Times New Roman;font-weight:normal;font-size:10px" title="Aktuelles Wetter und Vorhersagen für 2 Mio. Orte weltweit">Mehr Wetter</a></div>
            </div>
        </div>
        <h3><a href="#">Archiv</a></h3>
        <div>
        <ul id="chart_menu">
    <?php
        
        $sql =	"SELECT DISTINCT date_format( date, '%Y' ) AS year "
            .	"	, date_format( date, '%Y-%m' ) AS month "
            .	"	, date_format( date, '%Y-%m-%d' ) AS day "
            .	" FROM solardata "
            .	" WHERE art = 'STD' "
            .	" ORDER BY date DESC "
            .	" LIMIT 0 , 1095 "; // 3 Jahre		
            
        $ok = mysql_query ( $sql );
        
        if( $ok  )
        {
            $year = 0;
            $month = 0;
            $day = 0;
            while ( $row = mysql_fetch_object ( $ok ) )
            {
                if( $year != $row->year && $year != 0 )
                    echo '</ul></li>';
                    
                if( $month != $row->month && $month != 0 )
                    echo '</ul></li>';
                    
                if( $year != $row->year )
                {
                    echo	'<li>'
                        .	'<a id="A-' . $row->year . '" class="nav_link" href="javascript:void(0)" onclick="load_chart( \'year\' , ' . strtotime( '0000 ' . $row->year ) . ' );return false;">' 
                        .   '<span class="tog ui-icon ui-icon-triangle-1-e"></span>'
                        .   date ( 'Y' , strtotime( '0000 ' . $row->year ) )
                        .	'<span class="vis">&raquo;</span></a><ul id="UL-' .$row->year . '">';
                    $year = $row->year;
                }
                if( $month != $row->month )
                {
                    
                    echo	'<li>'
                        .	'<a id="A-' . $row->month . '" class="nav_link" href="javascript:void(0)" onclick="load_chart( \'month\' , ' . strtotime( '0000 ' . $row->month ) . ' );return false;">' 
                        .   '<span class="tog ui-icon ui-icon-triangle-1-e"></span>'
                        .   date ( 'F Y' , strtotime( '0000 ' . $row->month ) )
                        .	'<span class="vis">&raquo;</span></a><ul id="UL-' .$row->month . '">';
                    
                    $month = $row->month;
                    
                }					
                
                echo '<li id="LI_' . $row->day . '"><a href="javascript:void(0)" id="A-' . $row->day . '" class="nav_link" onclick="load_chart( \'day\' , ' . strtotime( '0000 ' . $row->day ) . ' );">' . date ( 'd.m.Y' , strtotime( '0000 ' . $row->day ) ) .'<span class="vis">&raquo;</span></a></li>';
                    
            }
        }
        else
        {
            echo '<li><strong>Datenbankfehler:</strong ' . mysql_error() . '</li>';
        }
        
    ?>

        </ul>
        </div>
    <?
        /*
        // Tag
        $stmt 	= "SELECT DISTINCT Date "
                . " FROM solardata "
                . " WHERE date != 0 "
                . " ORDER BY Date DESC LIMIT 100";
        
        $ok = @ mysql_query ( $stmt );
        
        if($ok)
        {
            echo	"<label for=\"sel_day\">Tag:</label><select name=\"sel_day\" id=\"sel_day\" "
                .	"onchange=\"change_chart(this,'day');\"><option value=\"0\" selected>--Bitte wählen--</option>";

            while ( $row = mysql_fetch_object ( $ok ) )
            {
                echo	" <option value=\"" . strtotime ( $row->Date ) . "\">" . date ( "Y-m-d" , strtotime ( $row->Date ) ) . "</option> ";
            }
            echo	"</select>";
        }
        
        // Monat
        $stmt 	= "SELECT DISTINCT date_format(Date,'%Y-%m-%d')  AS Date "
                . " FROM solardata "
                . " WHERE date != 0 "
                . " GROUP BY date_format(Date,'%Y-%m') " 
                . " ORDER BY Date DESC LIMIT 100";
        
        $ok = @ mysql_query ( $stmt );
        
        if($ok)
        {
            echo	"<label for=\"sel_month\">Monat:</label><select name=\"sel_month\" id=\"sel_month\" "
                .	"onchange=\"change_chart(this,'month')\"><option value=\"0\" selected>--Bitte wählen--</option>";

            while ( $row = mysql_fetch_object ( $ok ) )
            {
                echo	" <option value=\"" . strtotime ( $row->Date ) . "\">" . date ( "F Y" , strtotime ( $row->Date ) ) . "</option> ";
            }
            echo	"</select>";
        }
        
        echo	"";
        
    ?>	
        
        · <a href="javascript:void(0)" onclick="load_chart( 'radar' , getTime() );">Jahresüberblick (Radar)</a>
        · <a href="javascript:void(0)" onclick="load_chart( 'year'  , getTime() );">Jahresüberblick (Balken)</a>


    <?
        */
    ?>
    </div>
<? endif; ?>
