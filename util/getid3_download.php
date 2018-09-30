<?php
	ini_set('upload_max_filesize', '200M');
	ini_set('post_max_size', '250M');
	ini_set('memory_limit', '-1');
	ini_set('mbstring.http_input', 'pass');
	ini_set('mbstring.http_output', 'pass');
	/*
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	mb_http_input('UTF-8');
	mb_language('uni');
	mb_regex_encoding('UTF-8');
	ob_start('mb_output_handler');
	header('Content-Type:text/html; charset=UTF-8');
	*/
	set_time_limit(180);

	function download_remotefile($surl, $sfname) {
		// read the file from remote location
		$current = file_get_contents($surl);
		// Write the contents back to the file
		file_put_contents($sfname, $current);
	}
	function remote_file_size($url){
		# Get all header information
		$data = get_headers($url, true);
		# Look up validity
		if (isset($data['Content-Length']))
			# Return file size
			return (int) $data['Content-Length'];
	}

	function download_file($url, $path) {
		ini_set('user_agent','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36');
		ob_flush();
		$newfilename = $path;
		$file = fopen ($url, "r");
		if ($file) {
		$newfile = fopen($newfilename, "w+b");
			if ($newfile)
				while(!feof($file)) {
				  fwrite($newfile, fread($file, 1024 * 8 ), 1024 * 8 );
				}
		}
		if ($file) {
			fclose($file);
		}
		if ($newfile) {
			fclose($newfile);
		}
	}

	 function stream_copy($src, $dest)
    {
		$context = stream_context_create(array('ssl'=>array(
			'verify_peer' => true,
			'cafile' => '/path/to/ca-bundle.crt'
		)));
        $fsrc = fopen($src, "rb", false, $context);
        $fdest = fopen($dest,'w+b');
        $len = stream_copy_to_stream($fsrc,$fdest);
        fclose($fsrc);
        fclose($fdest);
        return $len;
    }

	ini_set("display_errors", 1);
	error_reporting(-1);
	$root = realpath(dirname(__FILE__));
	if(DIRECTORY_SEPARATOR == "/") {
		require_once($root."/getid3/getid3.php");
		require_once($root."/getid3/write.php");
	} else {
		require_once($root."\\getid3\\getid3.php");
		require_once($root."\\getid3\\write.php");
	}
	
	$arrContextOptions=array(
		"ssl"=>array(
			"verify_peer"=>false,
			"verify_peer_name"=>false
		)
	);  
	$ctx = stream_context_create($arrContextOptions);


	//print_r($_REQUEST);
	$itunesURL = "http://www.itunes.com/dtds/podcast-1.0.dtd";
	//$feedXml = "http://pod.ssenhosting.com/rss/sisatong04/sisatong04.xml";
	$rmtFile = $_REQUEST["lnk"];
	$imgUrl = $_REQUEST["img"];
	$artist = $_REQUEST["artist"];

	$rmtPath = preg_replace("/[?].+$/", "", $rmtFile);
	$tmpDir = $root.DIRECTORY_SEPARATOR."temp";

	foreach (glob($tmpDir.DIRECTORY_SEPARATOR."*.{mp3,tmp}", GLOB_BRACE) as $file) {
		if (filemtime($file) < time() - 600) {
			unlink($file);
		}
	}

	$partsURL = pathinfo($rmtPath);

	if($partsURL["filename"] == "download" && strtolower(substr($partsURL["filename"], -3)) == "mp3") {
		$fname = $_REQUEST["title"];
		$fname .= ".".$partsURL["extension"];
		$lclFile = tempnam($tmpDir, $partsURL["filename"]);
	} else {
		$fname = $partsURL["filename"].".".$partsURL["extension"];
		$lclFile = $tmpDir.DIRECTORY_SEPARATOR.$fname;
	}
	//print_r($partsURL);
	$imgFile = md5($imgUrl);
    $imgPaths = parse_url($imgUrl, PHP_URL_PATH);
    $ext = pathinfo($imgPaths, PATHINFO_EXTENSION);
	$imgFile = $tmpDir.DIRECTORY_SEPARATOR.$imgFile.".".$ext;
	if(!file_exists($imgFile) || filesize($imgFile) == 0) {
		//$fh = fopen($imgFile, "wb+");
		//$imgBytes = file_get_contents($imgUrl, $ctx);
		//fwrite($fh, $imgBytes);
		//fclose($fh);
		copy($imgUrl, $imgFile, $ctx);
	} else {
		$imgBytes = file_get_contents($imgFile);
	}


	//$xdoc = new DOMDocument("1.0", "utf-8");
	//$xdoc->load($feedXml);

	/*
	exec("wget $rmtFile -O $lclFile");
	echo "wget $rmtFile -O $lclFile";
	exit;

	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_URL, $rmtFile);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	curl_close($ch);
	print_r($data);
	$fp = fopen($lclFile, 'w+');
	fwrite($fp, $data);
	fclose($fp);
	*/
	//stream_copy($rmtFile, $lclFile);
	copy($rmtFile, $lclFile, $ctx);
	$gid3 = new getID3;
	$gid3->setOption(array('encoding'=>'UTF-8'));
	//$gid3->tag_encoding = 'UTF-8';
	$finfo = $gid3->analyze($lclFile);
	//$tgtFile = $tmpDir."/".$fname;
	//rename($lclFile, $tgtFile);
	/*
	if($fp_remote = fopen($rmtFile, "rb")) {
		$flsize = remote_file_size($rmtFile);
		$lclFile = tempnam($tmpDir, "getID3");
		if($fp_local = fopen($lclFile, "w+b")) {
			/*
			while (!feof($fp_remote)) {
				if(fwrite($fp_local, fread($fp_remote, 8192)) === FALSE) {
					return false;
				}
				flush();
			}

			//$dat = file_get_contents($rmtFile);
			$dat = fread($fp_remote, remote_file_size($rmtFile));
			print_r($dat);
			fwrite($fp_local, $dat);
			//fwrite($fp_local, stream_get_contents($fp_remote, -1, 0));
		}
		fclose($fp_remote);
		fclose($fp_local);
		$gid3 = new getID3;
		$gid3->setOption(array('encoding'=>'EUC-KR'));
		$finfo = $gid3->analyze($lclFile);
		//print_r($finfo);
		unlink($lclFile);
	} else {
		return;
	}
	*/


	if(!isset($finfo["title"])) {
		$finfo['title'] = array($_REQUEST["title"]);
	}
	if(!isset($finfo["artist"])) {
		$finfo['artist'] = array($_REQUEST["artist"]);
	}
	if(!isset($finfo["album"])) {
		$finfo["album"] = array($_REQUEST["ttl"]);
	}
	/*
	if(!isset($finfo["comment"])) {
		$finfo["comment"] = array($_REQUEST["summary"]); //iconv("UTF-8", "EUC-KR", $_REQUEST["ttl"]);
	}
	*/
	//$finfo["comments"] = array("unsynchronised_lyric" => array($_REQUEST["summary"]));
	//$finfo['USLT'][0]['unsynchronised_lyrics'] = array($_REQUEST["summary"]);
	if(!isset($_REQUEST["summary"])) {
		$finfo['unsynchronised_lyrics'][0] = $_REQUEST["summary"];
	}
	$finfo += array("attached_picture" => array(0 => array(
				"picturetypeid" => 2,
				"picturetype" => "Cover (front)",
				"description" => "cover",
				"mime" => "image/jpeg",
				"data" => $imgBytes,
				"encoding" => "UTF-8",
				"datalength" => count($imgBytes))
			));
	$id3Writer = new getid3_writetags;
	$id3Writer->tag_encding = "UTF-8";
	$id3Writer->filename = $lclFile;
	$id3Writer->tagformats = array("id3v2.3");
	$id3Writer->overwrite_tags = true;
	$id3Writer->remove_other_tags = false;
	$id3Writer->tag_data = $finfo;
	$id3Writer->WriteTags();

	//unlink($lclFile);
	$filesize = filesize($lclFile);
	//$partsURL = pathinfo($tgtFile);
	//$fname = $partsURL["filename"].".".$partsURL["extension"];
	$dnFname = $filename = $fname; //iconv("UTF-8", "EUC-KR", $fname);
	header('Content-Description: File Transfer');
	header("Expires: 0");
	header("Content-Type: application/octet-stream");
	//header('Content-Type: application/vnd.android.package-archive');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header("Content-Disposition: attachment; filename=\"".$dnFname."\"");
	header("Pragma: public");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: $filesize");

	ob_clean();
	flush();

	//ignore_user_abort(true);
	readfile($lclFile); //, false, $context);
	//ignore_user_abort(true);
	//unlink($tgtFile);
	exit;
	/*
	$context = stream_context_create();
	$fl = fopen($tgtFile, 'rb', FALSE, $context);
	while(!feof($fl))
	{
		echo stream_get_contents($fl, 2048);
	}
	fclose($fl);
	flush();
	if (file_exists($tgtFile)) {
		//unlink( $tgtFile );
	}
	//readfile($tgtFile, false, $context);
	//ignore_user_abort(true);
	//unlink($tgtFile);
	exit;

	//header("Location: ".$tmpFolder."/".$fname);
	*/
?>
