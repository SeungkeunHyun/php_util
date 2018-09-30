<?php
  header('Content-type: text/html; charset=utf-8');
  //mb_internal_encoding('UTF-8');
  $fpath = dirname(__FILE__)."/../repo/podcastlist.json";
  //$vpath = "/dakcho/repo";
  //echo $fpath.PHP_EOL;
  //echo  realpath($vpath);
  $rawData = file_get_contents("php://input");
  $content = json_decode(file_get_contents("php://input"), true);
  print_r($content);
  //echo $fpath."\r\n".$content;
  file_put_contents($fpath, json_encode($content, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
  /*
  $fpath = realpath("/dakcho/repo/podcastlist_temp.json");
  //file_put_contents(file_get_contents("php://input"));
  echo $fpath.PHP_EOL;
  return;
  $content = file_get_contents("php://input");
  echo $fpath."\r\n".$content;
  file_put_contents($fpath, $content);
  */
?>
