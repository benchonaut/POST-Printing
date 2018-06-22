<?php

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST');

header("Access-Control-Allow-Headers: X-Requested-With");



$imagick = new Imagick();
if(isset($_POST) AND !empty($_POST)) 
	{
	if ($_POST['type'] == 'card' ) {
				header("Access-Control-Allow-Origin: *");
				file_put_contents('/tmp/post.pdf', base64_decode($_POST['file'])) ;
				exec('/usr/bin/convert "/tmp/post.pdf" -density 600  -quality 100       -sharpen 0x1.0    "/tmp/post_converted.png"', $output, $response);
				exec('/usr/bin/convert /tmp/post_converted-*.png  -density 600  -quality 100       -sharpen 0x1.0 -append "/tmp/post_converted.png"', $output, $response);
				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				//echo $response ? "PDF converted to JPEG!!" : 'PDF to JPEG Conversion failed';
//				$imagick->readImageBlob(base64_decode($_POST['file']));
//				$imagick->writeImage('/tmp/post_converted.png');
//				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				echo '<html><head></head><body><img src="'.$src.'"></body></html>';

				header("HTTP/1.1 200 OK");
				} 
		elseif ($_POST['type'] == 'label') {	    
				header("Access-Control-Allow-Origin: *");
				file_put_contents('/tmp/post.pdf', base64_decode($_POST['file'])) ;
				exec('/usr/bin/convert "/tmp/post.pdf" -density 600  -quality 100       -sharpen 0x1.0    "/tmp/post_converted.png"', $output, $response);
				exec('/usr/bin/convert /tmp/post_converted-*.png  -density 600  -quality 100       -sharpen 0x1.0 -append "/tmp/post_converted.png"', $output, $response);
				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				//echo $response ? "PDF converted to JPEG!!" : 'PDF to JPEG Conversion failed';
//				$imagick->readImageBlob(base64_decode($_POST['file']));
//				$imagick->writeImage('/tmp/post_converted.png');
//				$src = 'data: '.mime_content_type('/tmp/post_converted.png').';base64,'.base64_encode(file_get_contents('/tmp/post_converted.png'));
				echo '<html><head></head><body><img src="'.$src.'"></body></html>';

				header("Access-Control-Allow-Origin: *");
				header("HTTP/1.1 200 OK");
				};			
	}

header("Status: 200");
