OFC = {
    name: "jQuery",                                     // depricated
    id: "",                                             // String indicating the ID of the replaced DOM-object
    range: "day",                                       // String indicating the type of the current chart
    obj: null,                                          // DOM FlashObj
    counter:0,
    source: null,                                       // JsonObj - chart data
    origSource: null,                                   // String - original chart data
    equivalent: "",                                     // equivalent picturename stored in images/charts
    init: function(id,cdata) {
    
        //cdata = cdata || { "data-file" : "includes/solardata_day.php&t="+getTime() };
        
        var sdata = (document.all) ? " " : "0";         // ie workaround
        
        
        cdata = cdata || { "data-string" : sdata };
    
        swfobject.embedSWF( 
            './includes/ofc/open-flash-chart.swf',      // swfUrlStr
            id,                                         // replaceElemIdStr
            '100%',                                     // widthStr
            '100%',                                     // heightStr
            '10.0.0',                                    // swfVersionStr
            './includes/swfobject/expressInstall.swf',  // xiSwfUrlStr
            cdata,                                      // flashvarsObj
            {},                                         // parObj
            {},                                         // attObj
            function(e) {
                
                // console.log(e)
                
                //
                //  e = Object { success , ref , id } 
                //
                
                if(e.success) {                         //- success, Boolean to indicate whether the embedding of a SWF was success or not 
                    Control.OFC.obj = $(e.ref).get(0);  //- ref, HTML object element reference (returns undefined when success=false) 
                    Control.OFC.id  = e.id;             //- id, String indicating the ID used in swfobject.registerObject
                }
            }                                           // callbackFn
        );
        
    },
    load: function(uri,data) {    
        
        if(!Control.OFC.obj || !uri) return;

        data = data || {};
        
        // show loading message
        if(typeof(Control.OFC.obj.show_loading_msg)==="function")
            Control.OFC.obj.show_loading_msg(); 
            
        // ajax - reqesting data and loading chart
        $.get(
            uri,
            data,
            function(obj,status) {
            
                //
                // console.log(status);
                
                try{
                    if(obj)
                        obj = JSON.parse(obj);
                    else
                        obj = false;
                }catch(err){
                    console.log("JSON.parse()",err.description);
                }
                
                
                
                if(obj&&status==="success"){
                    
                    //
                    // TODO: modify "obj" e.g. optional bars / lines ; with radio / checkboxes
                    //
                    
                    Control.OFC.origSource=JSON.stringify(obj);
                    
                    Control.OFC.param_json(obj);   
                    
                    Control.OFC.counter = 0;
                    
                } else {
                    // fallback
                    if(obj === 0) {
                        console.log("no data found",Control.OFC.counter++);
                        var time = parseInt(uri.substr(uri.indexOf('t=')+2))-86400;
                        if(time > 1257289200 && Control.OFC.counter <= 10)
                            load_chart(Control.OFC.range,time);
                        else
                            alert('no data found');
                    } else {
                        //alert(obj);
                        $('#'+Control.OFC.id).replaceWith(Control.OFC.image());
                    }
                }
            }
        );
    },
    param_json: function(obj) {
    
        obj = obj || JSON.parse(Control.OFC.origSource);
        
        if(typeof(obj)!=="object")
            return false;
    
        var mod = $(".chart-elements-modifier").get();
        
        for( var j=0 ; j<mod.length ; j++ ) {
            for( var i=0 ; i<obj.elements.length ; i++) {
            if(obj.elements[i].type==mod[j].value) {
                if(mod[j].checked==false)
                    obj.elements.splice(i,1);
                }
            }
        }
    
        Control.OFC.source=obj;
        
        //console.log(Control.OFC.source);
        
        var jsonString = Control.OFC.get_json();
                
        if(jsonString){        
            if(typeof(Control.OFC.obj.load)==="function") {
                Control.OFC.obj.load(jsonString);   // load JSON into flash (OFC-chart)
            } else if((Control.OFC.id)!=="") {
                Control.OFC.init(Control.OFC.id,{ "data-string" : jsonString });
            } else {
                // there is no instance of OFC
            }
        }else{
            console.log("invalid json:",jsonString);
        }
    },
    get_json: function() { return JSON.stringify(this.source) },
    version: function() { return Control.OFC.obj.get_version() },
    rasterize: function (dst) { $('#'+ dst).replaceWith(Control.OFC.image()) },
    post_image: function (url,clbck) { 
        $.post(url,{"image_binary":Control.OFC.obj.get_img_binary()},clbck);        
    },
    image: function() { 
    
        if(typeof(Control.OFC.obj.get_img_binary)==="function")
            return "<img id='"+Control.OFC.id+"' src='data:image/png;base64," + Control.OFC.obj.get_img_binary() + "' />";
        else // fallback
            return "<img id='"+Control.OFC.id+"' alt='"+Control.OFC.equivalent+"' src='images/charts/"+Control.OFC.equivalent+"' />";
    },
    popup: function() {
        var img_win = window.open('includes/ofc_upload_image.php?download&filename=custom-'+Control.OFC.equivalent, 'Chart: Export as Image');
    }
}
 

if(typeof(Control == "undefined")) {var Control = {"OFC":OFC}}

// By default, right-clicking on OFC and choosing "save image locally" calls this function.
// You are free to change the code in OFC and call my wrapper (Control.OFC.your_favorite_save_method)
function save_image() {
    Control.OFC.post_image('includes/ofc_upload_image.php?filename=custom-'+Control.OFC.equivalent,Control.OFC.popup); 
}
function ofc_ready() {
       
}
function ofc_complete() {
       
}

