<?php
function securityheaders() {

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");
}
$requestBody=file_get_contents('php://input');
$src = 'data: '.mime_content_type('/tmp/post_converted.png').'; base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				//echo $response ? "PDF converted to JPEG!!" : 'PDF to JPEG Conversion failed';
//				$imagick->readImageBlob(base64_decode($_POST['file']));
//				$imagick->writeImage('/tmp/post_converted.png');
//				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				securityheaders();header("HTTP/1.1 200 OK");
echo '<html><head></head><body><img src="'.$src.'"></body></html>';
