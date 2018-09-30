<?php
	// Create a curl handle
	header('Content-type:application/xml');
	$conx = file_get_contents($_GET['q']);
	echo $conx;
	// Execute
	//curl_exec($ch);

?>