<?php

ini_set('upload_max_filesize', '200M');
ini_set('post_max_size', '250M');
ini_set('memory_limit', '-1');
ini_set('mbstring.http_input', 'pass');
ini_set('mbstring.http_output', 'pass');
$root = realpath(dirname(__FILE__));

function download_remotefile($surl, $sfname) {
    // read the file from remote location
    $timeout = 0;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    /**
    * Set the URL of the page or file to download.
    */
    curl_setopt($ch, CURLOPT_URL, $surl);

    $fp = fopen($sfname, 'w+');
    /**
    * Ask cURL to write the contents to a file
    */
    curl_setopt($ch, CURLOPT_FILE, $fp);

    curl_exec ($ch);

    curl_close ($ch);
    fclose($fp);
}

function getFileNameFromPath($path) {
    $partsURL = pathinfo($path);
    return $partsURL["filename"].".".preg_replace("/\?.+$/", "", $partsURL["extension"]);
}

$tmpDir = dirname(__FILE__).DIRECTORY_SEPARATOR."temp";
if (!file_exists($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}
foreach (glob($tmpDir.DIRECTORY_SEPARATOR."*.{mp3,tmp}", GLOB_BRACE) as $file) {
    if (filemtime($file) < time() - 600) {
        unlink($file);
    }
}

if(DIRECTORY_SEPARATOR == "/") {
    require_once($root."/getid3/getid3.php");
    require_once($root."/getid3/write.php");
} else {
    require_once($root."\\getid3\\getid3.php");
    require_once($root."\\getid3\\write.php");
}


$rmtFile = $_REQUEST["lnk"];
$fname = getFileNameFromPath($rmtFile);
$lclFile = $tmpDir.DIRECTORY_SEPARATOR.$fname;
if(!file_exists($lclFile)) {
    download_remotefile($rmtFile, $lclFile);
}


$tagwriter = new getid3_writetags;
$tagwriter->filename = $lclFile;
$tagwriter->tag_encoding  = "UTF-8"; 
$tagwriter->tagformats = array('id3v1', 'id3v2.3');

$gid3 = new getID3;
$gid3->setOption(array('encoding'=>'UTF-8'));
	//$gid3->tag_encoding = 'UTF-8';
//$finfo = $gid3->analyze($lclFile);

$finfo['title'] = array($_REQUEST["title"]);
$finfo['artist'] = array($_REQUEST["artist"]);
$finfo["album"] = array($_REQUEST["ttl"]);
$imgUrl = $_REQUEST["img"];

$imgFile = $tmpDir.DIRECTORY_SEPARATOR.getFileNameFromPath($imgUrl);
if(!file_exists($imgFile)){
    download_remotefile($imgUrl, $imgFile);
}
$imgBytes = file_get_contents($imgFile);


if(isset($_REQUEST["summary"])) {
    $finfo["comment"] = array($_REQUEST["summary"]);
}

$finfo += array("attached_picture" => array(0 => array(
    "picturetypeid" => 2,
    "picturetype" => "Cover (front)",
    "description" => "cover",
    "mime" => "image/jpeg",
    "data" => $imgBytes,
    "encoding" => "UTF-8",
    "datalength" => filesize($imgFile))
));
$tagwriter->tag_data = $finfo;
copy($lclFile, $lclFile.".bak");
if($tagwriter->WriteTags()) {

} else {
    //echo 'Failed to write tags!<br>'.implode('<br><br>', $tagwriter->errors);
    print_r($tagwriter);
    copy($lclFile.".bak", $lclFile);
}
unlink($lclFile.".bak");

$filesize = filesize($lclFile);
	//$partsURL = pathinfo($tgtFile);
	//$fname = $partsURL["filename"].".".$partsURL["extension"];
$dnFname = $fname;
header('Content-Description: File Transfer');
header("Expires: 0");
header("Content-Type: application/octet-stream");
//header('Content-Type: application/vnd.android.package-archive');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header("Content-Disposition: attachment; filename=\"".$dnFname."\"");
header("Pragma: public");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");

flush();

//ignore_user_abort(true);
readfile($lclFile);
/*
$gid3 = new getID3;
$gid3->setOption(array('encoding'=>'UTF-8'));
//$gid3->tag_encoding = 'UTF-8';
$finfo = $gid3->analyze($lclFile);
print_r($finfo);
*/
?>