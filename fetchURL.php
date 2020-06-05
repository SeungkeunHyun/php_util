<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
    $uri = $_GET["uri"];
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri); 
		//curl_setopt($ch, CURLOPT_GET); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$content = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$hdr_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$hdr = substr($content, 0, $hdr_len);
		$body = substr($content, $hdr_len);
		header("Content-Type: $content_type");
		if($responseCode == 200) {
			echo $body;
		} else {
			//header('HTTP/1.1 500 Internal Server Error');
			echo $body;
		}
	} catch(Exception $ex) {
		print_r($ex);
	}
?>
