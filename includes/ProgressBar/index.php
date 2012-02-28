<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<title>ProgressBar 1.2</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	</head>
<body>
<h1>ProgressBar 1.2</h1>
<?php

 require_once 'ProgressBar.class.php';
 $bar = new ProgressBar();
 $bar->setMessage('loading ...');
 $bar->setAutohide(true);
 $bar->setSleepOnFinish(1);
 //$bar->setForegroundColor('#ff0000');

 $elements = 4; //total number of elements to process
 $bar->initialize($elements); //print the empty bar

 for($i=0;$i<$elements;$i++){

 	sleep(1); // simulate a time consuming process

 	$bar->increase(); //calls the bar with every processed element

 	if($i==1){
 		$bar->setMessage('loading - this is a simulation ...');
 		$bar->setForegroundColor('#3F41FF');
 	}
 }
?>
<p>
ProgressBar.class.php is an easy to use solution for time consuming operations and loops in PHP.
</p>
<p>
The class increases the timelimit for script-execution (if safe-mode is turned off), prevents a browser-timeout by sending pieces auf the progressbar to the browser and gives the user live-feedback on the progress of the running operation.
</p>
<p>
As of version 1.2 you can use setter-methods to change the appeareance of the bar at runtime. Have a look at the source of this demo to see how it works!
</p>

<?php

 $bar1 = new ProgressBar('End of simulation: 0%');
 $bar1->setForegroundColor('#FFA200');

 $elements1 = 3; //total number of elements to process
 $bar1->initialize($elements1); //print the empty bar

 for($i=0;$i<$elements1;$i++){

 	sleep(1); // simulate a time consuming process

 	$bar1->increase(); //calls the bar with every processed element
 	$bar1->setMessage('End of simulation: '.(($i+1)*33).'%');
 }
 $bar1->setMessage('End of simulation: 100%');
?>
</body>
</html>
