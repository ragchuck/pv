<?PHP
	
function fileFilterCSV ( $strEntry )
{
    if ( substr ( $strEntry, -4 ) == '.csv' ) 
    {
        return $strEntry;
    }
    else
    {
        return FALSE;
    }
}

function fileFilterZIP ( $strEntry )
{
    if ( substr ( $strEntry, 0, 5 ) != 'Mean.' && substr ( $strEntry, -4 ) == '.zip' ) 
    {
        return $strEntry;
    }
    else
    {
        return FALSE;
    }
}

function fileFilterMeanZip ( $strEntry )
{
    if ( substr ( $strEntry, 0, 5 ) == 'Mean.' && substr ( $strEntry, -4 ) == '.zip' ) 
    {
        return $DATA_DIRECTORY.$strEntry;
    }
    else
    {
        return FALSE;
    }
}

function flush_buffers()
{
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
} 
    
function fileFilter ( $strEntry )
{
    $type = substr ( $strEntry, -4 );
    if ( 	$type == '.xml' 
        ||	$type == '.zip'
        ||	$type == '.csv'
        ) 
    {
        return $strEntry;
    }
    else
    {
        return FALSE;
    }
}

function checkForFiles($dir)
{		
    $Files	= array_filter ( scandir( $dir ) , 'fileFilter' );	
    sort($Files);
    
    return ( count ( $Files ) > 0 );
}

function print_pre ( $string )
{
    echo '<pre>';
    print_r ($string);
    echo '</pre>';
}


function getDateFromFileName ( $filename )
{
    preg_match ( '/Mean.([0-9]{4})([0-9]{2})([0-9]{2})_([0-9]{2})([0-9]{2})([0-9]{2}).xml.zip/' , $filenameyy , $dateString  );
    //print_pre($dateString);
    $dat = mktime ( (int)$dateString[4], (int)$dateString[5], 0, (int)$dateString[2], (int)$dateString[3], (int)$dateString[1] );
    return $dat;
}				

function getXMLFileContent ( $xmlFile )
{
    $xml = simplexml_load_file( $xmlFile );
    $data = array();
    foreach ( $xml->MeanPublic AS $feld )
    {
        $data[str_replace("-","",str_replace(".","",str_replace("WRTL1EB9:2100122532:","",$feld->Key)))] = (float)$feld->Mean;
    }
    return " " . $data['AMsAmp'] . ", ". $data['AMsVol'] . ", ". $data['AMsWatt'] . ", ". $data['BMsAmp'] . ", ". $data['BMsVol'] . ", ". $data['BMsWatt'] . ", ". $data['Error'] . ", ". $data['ETotal'] . ")";
}

/**
 * returns the path intersection between the two given paths
 * path2 is moved from left to right until the pattern matches
 * 
 * @example
 * i = 0    :
 * path1cut : path1/path2/path3
 * path2cut : path2/path3/path4
 * result   : fail
 * 
 * i = 1    :
 * path1cut : path2/path3 [left path removed]
 * path2cut : path2/path3 [right path removed]
 * result   : hit
 * 
 * So the intersection between "path1/path2/path3" and
 * "path2/path3/path4" is "path2/path3".
 * 
 *
 * @param string $path1 left path (f.e. an absolute path)
 * @param string $path2 right path (f.e. a relative path)
 * @param char $outputSeparator the matched path intersection will be concatenated with this char
 * @return string|bool false in case of no intersection or empty path
 */
function getPathIntersection($path1, $path2, $outputSeparator = "/")
{
    if (DIRECTORY_SEPARATOR != "/") {
        $path1 = str_replace(DIRECTORY_SEPARATOR, "/", $path1);
        $path2 = str_replace(DIRECTORY_SEPARATOR, "/", $path2);
    }
    $path1 = trim($path1, "/");
    $path1Splitter = explode("/", $path1);
    
    $path2 = trim($path2, "/");
    $path2Splitter = explode("/", $path2);
    
    if (empty($path1) || empty($path2)) {
        return false;
    }
    
    for ($i = 0, $x = count($path1Splitter); $i < $x; ++$i) {
        $path1SplitterCut = array_slice($path1Splitter, $i);
        $path2SplitterCut = array_slice($path2Splitter, 0, $x - $i);
                
        if ($path1SplitterCut == $path2SplitterCut) {
            return implode($outputSeparator, $path1SplitterCut);
        }
    }
    
    return false;
}

/*
assert(getPathIntersection("", "") === false);
assert(getPathIntersection("path1", "") === false);
assert(getPathIntersection("", "path1") === false);
assert(getPathIntersection("path2", "path1/path1") === false);
assert(getPathIntersection("path1/path2/path3", "path1/path2") === false);
assert(getPathIntersection("path1", "path1") === "path1");
assert(getPathIntersection("path1/path2", "path2/path1") === "path2");
assert(getPathIntersection("path1/path2", "path1/path2") === "path1/path2");
assert(getPathIntersection("path1", "path1/path2") === "path1");
assert(getPathIntersection("path1/path2", "path2") === "path2");
assert(getPathIntersection("path1/path2", "path2/path3/path4") === "path2");
assert(getPathIntersection("path1/path1/path1/path2", "path1/path1/path2/path2/path3") === "path1/path1/path2");
assert(getPathIntersection("path1/path2/path3/path2/path3", "path2/path3/path2/path3/path4") === "path2/path3/path2/path3");
assert(getPathIntersection("path1/path2", "path1/path2/path3") === "path1/path2");
*/
	
?>