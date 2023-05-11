<?php
// (int)$_POST['client'];    // station number(not sanitized)
// $_POST['type'];             // card,label
// $_POST['file'];             //base64 encoded pdf
// https://gist.github.com/magnetikonline/650e30e485c0f91f2f40
require_once("/var/www/printserver-functions.php");
$configfile=getenv("HOME").'/.printroute.json';
$statusfile='/tmp/.status.json';
$config=array();
$status=array();

if (file_exists($configfile))  {                  $config=json_decode(file_get_contents($configfile),1); }
        else { initPrinterConfig($configfile);    $config=json_decode(file_get_contents($configfile),1); }


try {
       // perform potentially breakable parts
                                /* // DO NOT TRY AT HOME // very "talented" dev's managed it to just send a raw json string as post data instead of fields ..
                                 if (is_JSON(print_r($_POST)) && is_array(json_decode(print_r($_POST))) ) {
                                      $_POST=json_decode(print_r($_POST));
                                         } */
                 // ultra "talented" dev's managed it to just send a json string as post data instead of fields .. (dafuq)
                $fullin=file_get_contents('php://input');
                //file_put_contents("/dev/shm/incomingBODY.raw",$fullin);
                if (is_JSON($fullin) ) {
                     $_POST=json_decode($fullin, true);
                     //file_put_contents("/dev/shm/usedsource.log","BODY");
                         } else {
                            // file_put_contents("/dev/shm/usedsource.log","POST");
                              }

                if (file_exists("/tmp/.debug_mode_active"))  {
                   if (!file_exists('/tmp/.debug_out/')) {
                       mkdir('/tmp/.debug_out/', 0750, true);
                    }
                //file_put_contents("/tmp/.debug_out/incomingrawPOST.json",$_POST);
                what_did_they_really_send();
                }
             

} catch (Exception $ex) {
    // jump to this part
    // if an exception occurred
}

//catch OPTIONS
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: POST,GET, OPTIONS, DELETE");
    header("Access-Control-Max-Age: 86400");
    header("HTTP/1.0 204 No Content");
    exit(0);
}

//detect the client number from the last octet of the ip if not sent via POST
if($_SERVER['REQUEST_METHOD'] === 'POST' AND isset($_POST) AND !empty($_POST) )
    {
    $RealIP=explode(".",getRealIpAddr());
    $lastOctet=$RealIP[3];

    if ( is_numeric(intval(numbersOfString($_POST['client']))) || is_int(intval(numbersOfString($_POST['client']))) && 0 != intval(numbersOfString($_POST['client'])) ) {
       $_POST['client']=intval(numbersOfString($_POST['client']));
    }




     //if( isset($_POST['client'])) { print "cli_raw:"; print_r($_POST['client']) ; }

    if( isset($_POST['client'] ) ) {
        if ( is_numeric(intval($_POST['client'])) || is_int(intval($_POST['client'])) ) {
            //print " numeric_client: " ;print intval($_POST['client']) ;
                        if(intval($_POST['client']) == 0 ) { print("ERROR: client is zero , send numbers or GTFO") ; }
             $_POST['client']=intval($_POST['client']);

            }
         }

//if( isset($_POST['client']) && ( is_numeric(intval($_POST['client'])) || is_int(intval($_POST['client']))  ) ) { print "client_valid:".intval($_POST['client']);} else { print "client_invalid:".intval($_POST['client']) ; }
  if( isset($_POST['client']) && ( is_numeric(intval($_POST['client'])) || is_int(intval($_POST['client']))  ) ) {
        $client=$_POST['client'];
    } else {
        // 51..66 , 101..116 ,151..166 GET A STRAIGHT 1:1 MAPPING
        if (($lastOctet > 50) && ($lastOctet<200))
        {
             $client=$lastOctet % 10 ;
        }
    }

//if( isset($_POST['client'])) { print "cli_num_post:"; print_r($_POST['client']) ; }
//if( isset($_POST['client'])) { print "cli_num:";      print_r($client)          ;          }

//temporary file -> /tmp/groupid.processpid.pdf
    $filename='/tmp/'.getmygid().getmypid().'.pdf';
        if (!isset($_POST['type'])) {
        $_POST['type'] = 'card';
    }
    if (!isset($_POST['file'])) {
        header('{"status":"FAIL","message":"NO_FILE_NO_FUN"}', true, 422);
        exit(666);

    }
if (file_exists("/tmp/.debug_mode_active"))  {
    if (!file_exists('/tmp/.debug_out/')) {
        mkdir('/tmp/.debug_out/', 0750, true);
    }
//file_put_contents("/tmp/.debug_out/incomingPOST_processed.json",$_POST);
}

    if ($_POST['type'] == 'card' ) {
            $printer='CARD'.getCardNum($config,$client);
            file_put_contents($filename, base64_decode($_POST['file']));
            if (file_exists("/tmp/.debug_mode_active"))  {
               file_put_contents("/tmp/.debug_out/incomingcard.pdf",base64_decode($_POST['file']));
            }
            exec('lpadmin -p'.$printer.' -o GDuplexMode='.getCardMode($config,$client).' -o GRibbonType='.getCardRibbon($config,$client).' ;');
            $orientation='landscape'; //check for portrait parameter,otherwise autodetect , fallback to parameter defined here
            if (isset($_POST['portrait']) && !empty($_POST['portrait']))
            {
                //use paramater portrait [true|false] to print specific mode,
                if ($_POST['portrait'] == 'true' )      { exec('lpr -o portrait -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename);  $orientation='portrait' ;}
                elseif ($_POST['portrait'] == 'false' ) { exec('lpr -o landscape -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename); $orientation='landscape';}
                if (file_exists("/tmp/.debug_mode_active"))  {
                    if ($_POST['portrait'] == 'true' )      {
                       file_put_contents("/tmp/.debug_out/incomingcard.orientation","portrait");
                    } else {
                       file_put_contents("/tmp/.debug_out/incomingcard.orientation","landscape");
                    }
                }     
                // end portrait set           
            } else  {    
                 // portrait/landscape not set
                    $pdfinfoout = shell_exec("pdfinfo ".$filename);
                    preg_match('~Page size: +([0-9\.]+) x ([0-9\.]+) pts~', $pdfinfoout, $matches);
                    if (!empty($matches[2]))    {
                        $width=intval($matches[1]);
                        $height=intval($matches[2]);
                        if     ( $width > $height ) { $orientation='landscape'; }
                        elseif ( $height > $width ) { $orientation='portrait'; }
                        else                        { $orientation='landscape'; } // x=y , a square
                    }
                    exec('lpr -o '.$orientation.' -o fit-to-page -o media=Card -P'.$printer.' -r '.$filename);
                    exec('rm '.$filename);
                    if (file_exists("/tmp/.debug_mode_active"))  {
                           file_put_contents("/tmp/.debug_out/incomingcard.orientation",$orientation);
                    }
            }
            $arr = array('status' => 'queued', 'client' => $client,  'orientation' => $orientation, 'printer' => 'CARD'.getCardNum($config,$client));
            echo json_encode($arr, JSON_PRETTY_PRINT);
            header("Access-Control-Allow-Origin: *");
            header("HTTP/1.1 200 OK");
            }
    elseif ($_POST['type'] == 'label') {
            $printer='LABEL'.getLabelNum($config,$client);
            file_put_contents($filename, base64_decode($_POST['file']));
            if (file_exists("/tmp/.debug_mode_active"))  {
               file_put_contents("/tmp/.debug_out/incominglabel.pdf",base64_decode($_POST['file']));
            }
            if ( getLabelMode($config,$client) == 'WIRE_BLK') {
                exec('lpadmin -p'.$printer.' -o PageSize=62x100 ;');
//TODO REMOVE FORCED SCALING AFTER DEMO
//                $convertres=exec('convert '.$filename.' -resize 1465x '.$filename.'.pdf');
                exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
                exec('rm '.$filename.' '.$filename.'.pdf');
                }
            if ( getLabelMode($config,$client) == 'WIRE_29x90') {
                exec('lpadmin -p'.$printer.' -o PageSize=29x90 ;');
                exec('lpr -o fit-to-page -P'.$printer.' -r '.$filename);
                exec('rm '.$filename);
                }
// wifi modes to label printers are sent via python
            if ( getLabelMode($config,$client) == 'WIFI_RED') {
                $printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
                $convertres=exec('convert '.$filename.' -resize 696x '.$filename.'.jpg');
                putenv("HOME=/var/www/");putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
                $printres=shell_exec('sudo /usr/bin/python3 /usr/local/bin/brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 62 --red --lq '.$filename.'.jpg');
                if (file_exists("/tmp/.debug_mode_active"))  {
                   exec("cp ".$filename.'.jpg'."/tmp/.debug_out/outgoinglabel.jpg");
                }
                exec('rm '.$filename.'.jpg '.$filename);
                }

            if ( getLabelMode($config,$client) == 'WIFI_BLK') {
                $printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
                $convertres=exec('convert '.$filename.' -resize 696x '.$filename.'.jpg');
                putenv("HOME=/var/www/");putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
                $printres=shell_exec('sudo /usr/bin/python3 /usr/local/bin/brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 62 --lq '.$filename.'.jpg');
                if (file_exists("/tmp/.debug_mode_active"))  {
                   exec("cp ".$filename.'.jpg'."/tmp/.debug_out/outgoinglabel.jpg");
                }
                exec('rm '.$filename.'.jpg '.$filename);
                }

            if ( getLabelMode($config,$client) == 'WIFI_29x90') {
                $printerip=exec('lpoptions  -p '.$printer.' | awk \'{for (i=1; i<=NF; i++) {if ($i ~ /device-uri/) {print $i}}}\' |cut -d"/" -f3');
                $convertres=exec('convert '.$filename.' -resize 306x991 '.$filename.'.jpg');
                putenv("HOME=/var/www/");putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');
                $printres=shell_exec('sudo /usr/bin/python3 /usr/local/bin/brother_ql -p tcp://'.$printerip.':9100 -m QL-810W -b network print -l 29x90 --lq '.$filename.'.jpg');
                if (file_exists("/tmp/.debug_mode_active"))  {
                   exec("cp ".$filename.'.jpg'."/tmp/.debug_out/outgoinglabel.jpg");
                }
                exec('rm '.$filename.'.jpg '.$filename);
                }

            //header("HTTP/1.1 200 OK");
            http_response_code(200);
            $arr = array('status' => 'queued', 'client' => $client,  'orientation' => "NOT_IMPLEMENTED", 'printer' => 'LABEL'.getLabelNum($config,$client));
            echo json_encode($arr, JSON_PRETTY_PRINT);
            //echo '{"status":"queued","client":'.$client.',"orientation":"NOT_IMPLEMENTED","printer":"LABEL'.getLabelNum($config,$client).'"}';
            header("Access-Control-Allow-Origin: *");
            };
    }
else
    {

    //header("Access-Control-Allow-Origin: *");
    //header("HTTP/1.0 204 No Content");
    http_response_code(422);
    $arr = array('status' => 'error', 'message' => "FAIL:DID NOT FIND ENOUGH PARAMETERS");
    echo json_encode($arr, JSON_PRETTY_PRINT);
    //print('{"status":"error","message":"FAIL:DID NOT FIND ENOUGH PARAMS"}');
    }

exit
?>
