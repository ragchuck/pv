// globals required for dataload
var files = [];
var oldFiles = [];
var anzFiles = 0;
var anzDaten = 0;
// Progressbar
var $progressbar = $('<div id="progressbar"></div').progressbar({value:0});
if(typeof(console)==="undefined") console = {log:function(e){}};
$(document).ready(function(){

    // Navigation
    $('#accordion').accordion({autoHeight:false});

    // Archiv Navigation 
    var char_nav = $('#chart_menu');
    char_nav.find("ul").hide();
    char_nav.find(".nav_link").click(function(){
        var self = $(this);
        self.parent().parent().find("ul:not(.highlight + ul)").slideUp("slow");
        self.parent().parent().find(".ui-icon-triangle-1-s").removeClass("ui-icon-triangle-1-s");
        
        self.parent().children("ul").slideToggle("slow");
        self.find(".tog").toggleClass("ui-icon-triangle-1-s");
    });
    
    // Tabs
    $("#tabs").tabs({
        selected:1,
        show: function(event, ui) {
            if( ui.index == 2 ) diagramm_debug();
        }
    });
    
    // Datepicker Direktwahl
    $.datepicker.setDefaults($.datepicker.regional['de']); 
    
    d = function(time){
        var date = time.split('.');
        load_chart( 'day' , $.datepicker.formatDate('@',new Date(date[2],date[1]-1,date[0]))/1000 ); 
    };
    
    var char_dp = $('#chart_date_picker').change(function(){
        d(this.value);
    }).datepicker({
        minDate: new Date(2009, 11-1, 4), // installation date: 4. NOV 2009
        maxDate: new Date(),
        onSelect: function(val) { d(val); this.blur();},
        dateFormat: 'dd.mm.yy'
    });
    
    $diagrammDiv    = $('#outer_diagramm')
            
	// embed Chart
	Control.OFC.init('diagramm_chart');
    
    // make chart risizable
    $('#diagramm').resizable();
    
    
    $('.chart-elements-modifier').live("click",function(e){Control.OFC.param_json()});

    // save images
    ofc_complete = function(){Control.OFC.post_image('includes/ofc_upload_image.php?filename='+Control.OFC.equivalent); };
    
    // check data regularly
    setInterval("check_data_load()", 300000); // 5 Minutenintervall 
    
    // init
	load_chart();
	check_data_load();
    
    $('#noscript').fadeOut('slow');

});

/*****************************************************************************/
load_chart = function( art , time )
/*****************************************************************************/
{
	// set default Values
	if ( art	== undefined ) art	= 'day';
	if ( time	== undefined ) time	= getTime();
	
	var uri = './includes/solardata_' +  art + '.php?t=' + time;		
	var uri2 = './includes/tabledata.php?range=' + art + '&t=' + time;
    
    Control.OFC.range = art;
    
    $('#diagramm_info').load( uri2 , function(){
        $('.fleft').button({icons: {primary: 'ui-icon-circle-arrow-w'},text: false});
        $('.fright').button({icons: {primary: 'ui-icon-circle-arrow-e'},text: false});
        $('.fright[rel=disabled]').button('disable');
    });
	
	// highlight nav-element
        $('#chart_date_picker').datepicker('setDate',(art == 'day') ? new Date(time*1000) : null);
    
	nav = $('#chart_menu');
	elements = $('#chart_menu a.highlight').removeClass('highlight');
		
    
	if( art != 'radar' )
	{
		var t = new Date(time*1000);
        var dateString = '';
		switch( art )
		{
			case 'day':     dateString = '-' + lpad(t.getDate(),2,"0")      + dateString;
			case 'month':   dateString = '-' + lpad((t.getMonth()+1),2,"0") + dateString;
			case 'year':    dateString = '-' + t.getFullYear()              + dateString;
		}
		
		$('#A'+dateString).addClass('highlight');
        
        Control.OFC.equivalent = "chart-" + Control.OFC.range + dateString + ".png";
        
	} else {
        Control.OFC.equivalent = "chart-radar.png";
    }
    
    if( $('#tabs').tabs('option', 'selected') == 2 )
    {
        diagramm_debug();
    }
    
    
    Control.OFC.load(uri);
}
/*****************************************************************************/
check_data_load = function( )
/*****************************************************************************/
{
	// Prüfen ob neue Daten vorhanden sind und die Funktion 'init_data_load' aufrufen.
	$.get('./includes/getFiles.php?'+(new Date()).getTime() , init_data_load);
}

/*****************************************************************************/
init_data_load = function( response )
/*****************************************************************************/
{
    var $infoBox = $('#data_load');
    var $outerInfoBox = $('#outer_data_load');
    
    //console.log(arguments);    
    //console.log("init_data_load IN:");
    //console.log(response);
    
    try{
        if(response)
            files = JSON.parse(response);
        else
            files = [];
    }catch(err){
        console.log(err);
        files = [];
    }
        
    //console.log("init_data_load AFTER PARSING:");
    //console.log(files);
        
    anzFiles = files.length;
    
    var jetzt = new Date();
    console.log(
            lpad(jetzt.getDate()        ,2,"0") +   "/"
        +   lpad((jetzt.getMonth()+1)   ,2,"0") +   "/"
        +   lpad(jetzt.getFullYear()    ,2,"0") +   " "
        +   lpad(jetzt.getHours()       ,2,"0") +   ":"
        +   lpad(jetzt.getMinutes()     ,2,"0") +   ":"
        +   lpad(jetzt.getSeconds()     ,2,"0") +   ": getFiles: " 
        +   anzFiles);

    // Ausgabe Anzahl Dateien
    $('#storage_anz_files').html(anzFiles);
    
    if( anzFiles > 0 )
    {        
        $outerInfoBox.show('slow');
        
        $infoBox
            .append('<p>Es sind noch <b id="anz_files">' + anzFiles + '</b> neue Dateien vorhanden. Daten werden verarbeitet...</p>')
            .append($progressbar);
        
        $.post( "includes/data_processing.php" , {file:files[0]} , data_load , "json");
    }
    else
    {
        $dl = $infoBox.html('keine neuen Daten vorhanden');
        setTimeout("$('#outer_data_load').hide(1500)",3000);
    }
}

/*****************************************************************************/
data_load = function( response )
/*****************************************************************************/
{    
    
    $('#outer_data_load').show('slow');
    
    if( response.error != 0 )
    {   // fehler ist aufgetreten
        $('#data_load').html(response.message).addClass('ui-state-error');        
        return false;
    }
    
    // verarbeitete Datei aus dem Array nehmen    
    oldFiles.push(files.shift());    
    
    var currentFile = files[0];
    
    if( response.result )
        anzDaten += response.result;
    
    var progress = 100 - Math.round(files.length/anzFiles*100);
    
    // Ausgabe aktualisieren
    $('#progressbar').progressbar('value' , progress); 
    $('#progressbar .ui-progressbar-value').html('<acronym title="'+currentFile+'"><small>'+progress+'%&nbsp;-&nbsp;('+anzDaten+')</small></acronym>');
    $('#anz_files').html(files.length);
    
    if( files.length > 0 )
    { // starte Datenladen
        $.post( "includes/data_processing.php" , {file:currentFile} , data_load , "json");
        return true;
    }
    else
    { // Datenladen abgeschlossen      
        $('#storage_anz_files').html(files.length);
        $('#data_load').fadeOut(
            1000, //time
            function(){ // callbackfunction
                $(this).html(
                        '<p><strong>Datenladen abgeschlossen.</strong>'
                    +   '<br /><b>'+anzFiles+'</b> Dateien mit <b>'+anzDaten+'</b> Datens&auml;tzen verarbeitet.</b><br />'
                    +   '<small>'+(new Date()).toLocaleString()+'</small></p>'
                ).fadeIn(1000);
            }
        );
                
        load_chart();
        setTimeout("$('#outer_data_load').hide(1500)",10000);
    }   
}
	
/*****************************************************************************/
function getCurrentData(elem)
/*****************************************************************************/
{
	var string = $('#diagramm_chart').children('param').attr('value')
	var start = string.indexOf(elem+'=')+elem.length+1;
	var length = string.indexOf('&',start)-start;
	if(length>0)
		string = string.substr(start,length);
	else
		string = string.substr(start);
	return string;
}

/*****************************************************************************/
function diagramm_info ( response )
/*****************************************************************************/
{
	$('#diagramm_info').html( decodeURI( response ));
}

/*****************************************************************************/
function diagramm_debug( )
/*****************************************************************************/
{
	$('#diagramm_debug').html(Control.OFC.get_json());
}

/*****************************************************************************/
function lpad(ContentToSize,PadLength,PadChar)
/*****************************************************************************/
{
    var PaddedString=ContentToSize.toString();
    for(i=PaddedString.length+1;i<=PadLength;i++)
    {
        PaddedString=PadChar+PaddedString;
    }
    return PaddedString;
}

/*****************************************************************************/
function getTime ()
/*****************************************************************************/
{
	return Date.parse(new Date())/1000;
}



