<?php
function getFileNameFromPath($path) {
    $partsURL = pathinfo($path);
    return $partsURL["filename"].".".preg_replace("/\?.+$/", "", $partsURL["extension"]);
}

function remote_file_size($url){
    # Get all header information
    $data = get_headers($url, true);
    # Look up validity
    if (isset($data['Content-Length']))
        # Return file size
        return (int) $data['Content-Length'];
}

$rmtUrl = $_REQUEST["url"];
$filesize = remote_file_size($rmtUrl);
$dnFname = getFileNameFromPath($rmtUrl);

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

$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0); //Set curl to return the data instead of printing it to the browser.
curl_setopt($ch, CURLOPT_URL, $rmtUrl);

$data = curl_exec($ch);
curl_close($ch);
?>