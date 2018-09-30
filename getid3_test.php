<?php
	header("Content-Type: text-plain; charset=UTF-8");
	ini_set("display_errors", 1);
	error_reporting(-1);
	$root = $_SERVER['DOCUMENT_ROOT'];
	require_once($root."/php/getid3/getid3.php");
	require_once($root."/php/getid3/write.php");

	$itunesURL = "http://www.itunes.com/dtds/podcast-1.0.dtd";
	$feedXml = "http://pod.ssenhosting.com/rss/sisatong04/sisatong04.xml";
	$rmtFile = "http://file.ssenhosting.com/data1/sisatong04/150513am.mp3";
	$partsURL = pathinfo($rmtFile);
	$fname = $partsURL["filename"].".".$partsURL["extension"];
	print_r($partsURL);
	$tmpDir = $root."/temp";

	$xdoc = new DOMDocument("1.0", "utf-8");
	$xdoc->load($feedXml);
	$imgElm = $xdoc->getElementsByTagNameNS($itunesURL, "image");
	$chnl = $xdoc->getElementsByTagName("channel");
	if($chnl->length > 0) {
		$chnl = $chnl->item(0);
		echo $chnl->getElementsByTagName("title")->item(0)->textContent."<br/>";
	}
	$imgUrl = null;
	if($imgElm->length > 0) {
		$imgUrl = $imgElm = $imgElm->item(0)->getAttribute("href");	
	}


	if($fp_remote = fopen($rmtFile, "rb")) {
		$lclFile = tempnam($tmpDir, "getID3");
		if($fp_local = fopen($lclFile, "wb")) {
			while ($buffer = fread($fp_remote, 8192)) {
				fwrite($fp_local, $buffer);
			}
		}
		fclose($fp_local);
		$gid3 = new getID3;
		$finfo = $gid3->analyze($lclFile);
		//unlink($lclFile);
	}

	$tgtFile = $tmpDir."/".$fname;
	print_r($imgElm);
	if(!isset($finfo["attached_picture"])) {
		$finfo["attached_picture"][] = array(
				"picturetypeid" => 2,
				"description" => "cover",
				"mime" => "image/jpeg",
				"data" => file_get_contents($imgUrl) 
			);
		$finfo['title'][] = $chnl->getElementsByTagName("title")->item(0)->textContent;
		$finfo['artist'][] = $chnl->getElementsByTagName("title")->item(0)->textContent;
		copy($lclFile, $tgtFile);
		$id3Writer = new getid3_writetags;
		$id3Writer->filename = $tgtFile;
		$id3Writer->tagformats = array("id3v2.3");
		$id3Writer->overwrite_tags = true;
		$id3Writer->remove_other_tags = false;
		$id3Writer->tag_encding = "UTF-8";
		$id3Writer->tag_data = $finfo;
		$id3Writer->WriteTags();
		print_r($id3Writer);
	}
	unlink($lclFile);
	
?>