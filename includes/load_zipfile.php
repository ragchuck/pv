<?php

if( file_exists( $DATA_DIRECTORY . $ZipFile ))
{
    /* unzip File */
    $arInnerFiles = unzip( $DATA_DIRECTORY . $ZipFile );
    
    if( !is_array($arInnerFiles) )     
        trigger_error( "No array given: $arInnerFiles" , E_USER_ERROR );     
    
    if ( count ( $arInnerFiles ) > 0 )
    {				
        
        $stmt = $stmt_kopf;
        $first = true;
        
        foreach ( $arInnerFiles as $innerZip )
        {
            
            if ( file_exists ( $innerZip ) )
            {            
                /* get timestamp of data included in Zip */
                preg_match ( '/Mean.([0-9]{4})([0-9]{2})([0-9]{2})_([0-9]{2})([0-9]{2})([0-9]{2}).xml.zip/' , $innerZip , $dateString  );

                if( !is_array( $dateString) || count( $dateString ) != 7 )
                {   /* no Mean-File */
                    //trigger_error( "<b>Unrecognized MEAN ZipFile:</b> $innerZip" , E_USER_WARNING );
                    unlink( $innerZip );
                    continue;
                }
                
                $date = mktime ( (int)$dateString[4], (int)$dateString[5], 0, (int)$dateString[2], (int)$dateString[3], (int)$dateString[1] );
                
                $Ymd    = date ( 'Y-m-d' , $date );
                $YmdHis = date ( 'Y-m-d H:i:s' , $date ); 
                
                if( $_YmdHis === $YmdHis ) // prevent Duplicate entry
				{
                    unlink( $innerZip );
                    continue;
				}
                
                if ( !$first ) 
                    $stmt .= "," . chr(13);
                else
                    $first = false;
                    
                /* delete Data with same timestamp */
                $ok = mysql_query ( "delete from solardata where timestamp = '" . $YmdHis . "'" );
                if ( !$ok )
                { /* Error while deleting Data */
					$errno 		= mysql_errno();
					$message	= sprintf( "Fehler beim l&ouml;schen von Datens&auml;tzen: [%d] %s " , $errno, mysql_error() );
					if( $errno == 1064 ) $message .=  '<pre>' . $stmt . '</pre>'   ;
                    trigger_error( $message , E_USER_ERROR );     
                }
                
                
                
                /* unzip inner file */
                $files = unzip( $innerZip );
                
                /* expecting XML-file same name as ZIP */
                $xmlFile = substr ( $innerZip , 0 , -4 );
                if ( file_exists ( $xmlFile ) )
                {
                    /* get XML-Data */
                    $xml = simplexml_load_file( $xmlFile );
                    $data = array();
                    foreach ( $xml->MeanPublic AS $feld )
                    {
                        $data[str_replace(array("-","."),"",substr($feld->Key,20))] = (float)$feld->Mean;
                    }
                    
					/* Workaround to ensure data consistency */
					if( $data['ETotal'] < $_ETotal )
						$data['ETotal'] = $_ETotal;
					
					if( count( $data ) > 0 )
					{
						$stmt   .=  "("
								.   "   '{$Ymd}' "
								.   ",  '{$YmdHis}'  "
								.   ",   {$data['AMsAmp']} "
								.   ",   {$data['AMsVol']} "
								.   ",   {$data['AMsWatt']} "
								.   ",   {$data['BMsAmp']} "
								.   ",   {$data['BMsVol']} "
								.   ",   {$data['BMsWatt']} "
								.   ",   {$data['Error']} "
								.   ",   {$data['ETotal']} "
								.   ")";
						$anzInnen++;
						$_ETotal = $data['ETotal'];
					}
					else
					{
						trigger_error( 'Keine MEAN-Daten in <i>' . $xmlFile . '</i> gefunden!' , E_USER_WARNING );
					}
					unlink ( $xmlFile );
                }
                else
                {   /* File not found */
                    trigger_error( 'Die Datei <i>' . $xmlFile . '</i> wurde nicht gefunden!' , E_USER_WARNING );
                }
                unlink ( $innerZip );
                
                /* last timestamp */
                $_YmdHis = $YmdHis;
            }
            else
            {   /* File not found */
                trigger_error( 'Die Datei <i>' . $innerZip . '</i> wurde nicht gefunden!' , E_USER_WARNING );
            }
        }
        
        if( $anzInnen > 0 )
        {
            /* Inserting Data */
            $ok = mysql_query($stmt);
            
            if ( !$ok )
            {   /* Error while data-insert */
                $message    =   'Fehler beim einf&uuml;gen von Datens&auml;tzen:'
                            .   '[' . mysql_errno() . '] ' 
                            .   mysql_error();
                $message   .=   (mysql_errno() == 1064) ? '<pre>' . $stmt . '</pre>' : '';            
                trigger_error( $message , E_USER_ERROR );                
            }
            else
            {   
                $anz += mysql_affected_rows();            
            }
        }

        /* copy ZipFile for BackUp */
        copy ( $DATA_DIRECTORY . $ZipFile , $DATA_DIRECTORY . 'verarbeitet/' . $ZipFile );
        unlink ( $DATA_DIRECTORY . $ZipFile );	
    }
}
else
{   /* File not found */
    trigger_error( "Datei <i>$ZipFile</i> nicht gefunden." , E_USER_WARNING );
}

if ( $anz > 0 )
    mysql_query ( "update parameter set wert=now() where feld='lmd'" );

$put["error"]   = 0;
$put["message"] = 'insg. ' . $anz . ' Datens&auml;tze eingef&uuml;gt.';
$put["result"]  = $anz;

?>