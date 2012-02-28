<?php
$filename = "chart.png";
if(isset($_GET['filename']))
    $filename = $_GET['filename'];
$filename = '../images/charts/' . $filename;




if(isset($_POST['image_binary']))
{ 
     
    $im = imagecreatefromstring(base64_decode($_POST['image_binary']));
    if ($im !== false) {    
    /*
        $w = isset($_GET['width'])  ? $_GET['width']  : 0;
        $h = isset($_GET['height']) ? $_GET['height'] : 0;
    
		$W = imagesx($im);
		$H = imagesy($im);
        
		if($w == 0)
			$w = $W * ($h / $H);
			
		if($h == 0)
			$h = $H * ($w / $W);            
		
		$w2 = $w;
		$h2 = $h;
		
		if ($w2 && ($W < $H)) 
			$w2  = ($h / $H) * $W;
		else 
			$h2 = ($w / $W) * $H;
    
        if( $W < $H )
            imagecopyresampled($im,$im,0,0,0,0,$w,$h2,$W,$H);
        else
            imagecopyresampled($im,$im,0,0,0,0,$w2,$h,$W,$H);
      */   
        imagestring($im,2,0,0," (c) 2010 by Martin Zoellner / Charts powered by OFC2 / ".date('r',time()),imagecolorallocate($im,0,0,0));
        imagepng($im,$filename);
    }
    else {
        echo 'An error occurred.';
    }
}
if ( isset($_GET['download']))
{
    if(file_exists($filename))
    {
        header("Content-Type: image/png");
        header("Content-Disposition: attachment; filename=\"".basename($filename,".png")."_".date('YmdHis',time()).".png\"");
        readfile($filename);
    }
    else
    {        
        header("HTTP/1.0 404 Not Found");
    }
}
?>