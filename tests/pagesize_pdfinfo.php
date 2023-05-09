<?php
// Debugging:
$automode='none';
$pdflivrelink="card-portrait.pdf";
$output = shell_exec("pdfinfo ".$pdflivrelink);
//echo "pdfinfo ".$pdflivrelink;

//var_dump($output);
// Dimension:
preg_match('~Page size: +([0-9\.]+) x ([0-9\.]+) pts~', $output, $matches);

//var_dump($matches);
if (!empty($matches[2]))	{  
	$width=intval($matches[1]);
	$height=intval($matches[2]);
	if ( $width > $height ) { $automode='landscape'; }
	elseif ( $height > $width ) { $automode='portrait'; }
	else { $automode='landscape'; }
} 
var_dump($automode);
// No of pages:
//preg_match('~Pages: +([0-9]+)~', $output, $matches);
//var_dump($matches);
