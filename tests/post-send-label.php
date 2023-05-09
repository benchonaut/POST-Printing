<?php

echo 'usage '.basename(__FILE__).' [https://print.url/print.php [clientnum [file]]]'."\n" ;

$client=1;
$url = 'http://printserver.local/print.php';
$filename='label.pdf';
if (isset($argv[3])) { $filename = $argv[3]; } ;
if (isset($argv[2])) { $client = $argv[2]; } ;
if (isset($argv[1])) { $url = $argv[1]; } ;

$imagedata = file_get_contents($filename);
$base64 = base64_encode($imagedata);
var_dump($client);
$data = array('client' => $client, 'type' => 'label', 'file' => $base64);

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
if ($result === FALSE) { /* Handle error */ }

//var_dump($result);
echo $result
?>
