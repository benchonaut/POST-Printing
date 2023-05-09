<?php
function securityheaders() {

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");
}
$requestBody=file_get_contents('php://input');
/* may be late but he can help others.
it's not my code, I get it from :
https://gist.github.com/magnetikonline/650e30e485c0f91f2f40
*/

            class DumpHTTPRequestToFile {
                public function execute($targetFile) {
                    $data = sprintf(
                        "%s %s %s\n\nHTTP headers:\n",
                        $_SERVER['REQUEST_METHOD'],
                        $_SERVER['REQUEST_URI'],
                        $_SERVER['SERVER_PROTOCOL']
                    );
                    foreach ($this->getHeaderList() as $name => $value) {
                        $data .= $name . ': ' . $value . "\n";
                    }
                    $data .= "\nRequest body:\n";
                    file_put_contents(
                        $targetFile,
                        $data . $requestBody . "\n"
                    );
                  //  echo("Done!\n\n");
                }
                private function getHeaderList() {
                    $headerList = [];
                    foreach ($_SERVER as $name => $value) {
                        if (preg_match('/^HTTP_/',$name)) {
                            // convert HTTP_HEADER_NAME to Header-Name
                            $name = strtr(substr($name,5),'_',' ');
                            $name = ucwords(strtolower($name));
                            $name = strtr($name,' ','-');
                            // add to list
                            $headerList[$name] = $value;
                        }
                    }
                    return $headerList;
                }
            }
//we saved the body above..
(new DumpHTTPRequestToFile)->execute('/tmp/dumprequest.txt');
							// add this line at the end to create a file for each request with timestamp
//$date = new DateTime();
//rename("dumprequest.txt", "dumprequest" . $date->format('Y-m-d H:i:sP') . ".txt");


$imagick = new Imagick();
if(isset($_POST) AND !empty($_POST) AND $_SERVER['CONTENT_TYPE'] != "application/json")
	{

	if ($_POST['type'] == 'card' ) {
				file_put_contents('/tmp/post.pdf', base64_decode($_POST['file'])) ;
				exec('/usr/bin/convert "/tmp/post.pdf" -alpha off -flatten -density 600  -quality 100       -sharpen 0x1.0    "/tmp/post_converted.png"', $output, $response);
				exec('/usr/bin/convert /tmp/post_converted-*.png  -alpha off -flatten -density 600  -quality 100       -sharpen 0x1.0 -append "/tmp/post_converted.png"', $output, $response);
				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				//echo $response ? "PDF converted to JPEG!!" : 'PDF to JPEG Conversion failed';
//				$imagick->readImageBlob(base64_decode($_POST['file']));
//				$imagick->writeImage('/tmp/post_converted.png');
//				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
                securityheaders();header("HTTP/1.1 200 OK");
				echo '<html><head></head><body><img src="'.$src.'"></body></html>';
				}
		elseif ($_POST['type'] == 'label') {
				file_put_contents('/tmp/post.pdf', base64_decode($_POST['file'])) ;
				exec('/usr/bin/convert "/tmp/post.pdf" -alpha off -flatten -density 600  -quality 100       -sharpen 0x1.0    "/tmp/post_converted.png"', $output, $response);
				exec('/usr/bin/convert /tmp/post_converted-*.png  -alpha off -flatten -density 600  -quality 100       -sharpen 0x1.0 -append "/tmp/post_converted.png"', $output, $response);
				$src = 'data: '.mime_content_type('/tmp/post_converted.png').'; base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				//echo $response ? "PDF converted to JPEG!!" : 'PDF to JPEG Conversion failed';
//				$imagick->readImageBlob(base64_decode($_POST['file']));
//				$imagick->writeImage('/tmp/post_converted.png');
//				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				securityheaders();header("HTTP/1.1 200 OK");
				echo '<html><head></head><body><img src="'.$src.'"></body></html>';

      }
	}

	if($_SERVER['CONTENT_TYPE'] == "application/json") {
	//echo "JSON";
	if(!empty($_POST['file']))
	{
			file_put_contents('/tmp/post.json', $_POST['file']) ;
		securityheaders();header("Status: 200");echo 'OK';
	}
	else {
		file_put_contents('/tmp/post.json', $requestBody ) ;

		securityheaders();header("Status: 200");echo 'OK';
			}
	}


//  header("Status: 200");
