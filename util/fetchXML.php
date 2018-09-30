<?php
	// Create a curl handle
	$arrContextOptions=array(
		"ssl"=>array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
	);  
	header('Content-type:application/xml');
	$conx = file_get_contents($_GET['q'], false, stream_context_create($arrContextOptions));
	echo $conx;
	// Execute
	//curl_exec($ch);

?>