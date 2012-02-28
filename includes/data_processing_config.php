<?php

/*****************************************************************************/
function ErrorHandler($errno, $errstr, $errfile, $errline)
/*****************************************************************************/
{
    switch ($errno) {
        case E_USER_ERROR   :   $type = "PVDZ_ERROR";       break;
        case E_USER_WARNING :   $type = "PVDZ_WARNING";     break;
        case E_USER_NOTICE  :   $type = "PVDZ_NOTICE";      break;
        case E_ERROR        :   $type = "ERROR";            break;
        case E_WARNING      :   $type = "WARNING";          break;
        case E_NOTICE       :   $type = "NOTICE";           break;
        default             :   $type = "UNDEFINED ERROR";  break;
    }
    $output["error"] = $errno;
    $output["message"] = "<b><accronym title=\"[$errno]: $errfile line $errline\">$type</acronym></b>:<br>$errstr";
    
    print_r( json_encode( $output ));
    
    /* no further processing */
    exit(1);
    
    return true;
}

/*****************************************************************************/
function unzip ( $file )
/*****************************************************************************/
{
    $extracted_files = array();
    $infZipPattern = '/inflating: (.*[.](zip|xml))/';
    $dir = dirname( $file );
    
    if( function_exists( "zip_open" ) )
    {
        $zip = zip_open( $file );
        
        if( !is_resource($zip) )
        {
            trigger_error( "Unable to zip-open file '{$file}'" , E_WARNING );
        }

        
        while( $zip_entry = zip_read( $zip ) ) 
        {
            $zdir  = dirname( zip_entry_name($zip_entry) );
            $zname = zip_entry_name( $zip_entry );
            
            /*
            if( substr( $zname , 0 , 5 ) != "Mean." )
                continue;
            */
            
            if( !zip_entry_open( $zip , $zip_entry , "r" ) )
            {
                $e.="Unable to proccess file '{$zname}'";
                continue;
            }

            $zip_fs = zip_entry_filesize( $zip_entry );
            if( empty( $zip_fs ) )
                continue;

            $zz = zip_entry_read( $zip_entry , $zip_fs );

            $z = fopen( $zname , "w" );
            fwrite( $z , $zz );
            fclose( $z );
            zip_entry_close( $zip_entry );
            
            copy( $zname , $dir . '/' . $zname );
            unlink( $zname );
            
            array_push( $extracted_files , $dir . '/' . $zname );

        }
        zip_close( $zip );
        
		sort( $extracted_files , SORT_STRING );
		
        return $extracted_files;
    }
    else
    {
        //$out = shell_exec('unzip "' . $file . '" "Mean.*" -d "' . $dir . '"');
        $out = shell_exec('unzip "' . $file . '" -d "' . $dir . '"');
        preg_match_all ( $infZipPattern , $out , $matches , PREG_PATTERN_ORDER );
		$extracted_files = $matches[1];
		sort( $extracted_files , SORT_STRING );
        return $extracted_files;
    }
    
    trigger_error( "Could not extract '$file'!" , E_USER_ERROR );
}


include_once ("../config/db_connect.php");
include_once ("../config/config.php");

/* Directory of the ZipFiles */
$DATA_DIRECTORY =  '.' . $DATA_DIRECTORY;

/* Sunny WebBox serial no. - (Prefix of delivered ZipFiles) */
$SWB_IDENT = array("WRTL1EB9:2100122532:","WRTL1EBA:2100122532:");

/* MySQL statement header */
$stmt_kopf = "INSERT INTO solardata ( Date, TimeStamp , AMsAmp , AMsVol , AMsWatt , BMsAmp , BMsVol , BMsWatt , Error , ETotal ) VALUES ";

$anz        = 0;
$anzInnen   = 0;
$_YmdHis    = "";
$_ETotal 	= 0;

?>