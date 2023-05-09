
<?php
if(isset($_GET['file'])) {
    $usedfile="unset";
    if($_GET['file']=='incominglabel.pdf') { 
        $usedfile="/tmp/.debug_out/incominglabel.pdf";
        //header("Content-type:application/pdf");
        //// It will be called downloaded.pdf
        //header("Content-Disposition:attachment;filename=\"incominglabel.pdf\"");
        //// The PDF source is in original.pdf
        //readfile('/tmp/.debug_out/incominglabel.pdf');
    }
    if($_GET['file']=='incomingcard.pdf') { 
        $usedfile="/tmp/.debug_out/incomingcard.pdf";
        //header("Content-type:application/pdf");
        //// It will be called downloaded.pdf
        //header("Content-Disposition:attachment;filename=\"incomingcard.pdf\"");
        //// The PDF source is in original.pdf
        //readfile('/tmp/.debug_out/incomingcard.pdf');
    }
    if($usedfile=='/tmp/.debug_out/incomingcard.pdf' || $usedfile=='/tmp/.debug_out/incominglabel.pdf' ) {
        print('<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<meta http-equiv="refresh" content="23; " />
<style>
  #pdf-viewer {
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.1);
    overflow: auto;
  }
  
  .pdf-page-canvas {
    display: block;
    margin: 3px auto;
    border: 1px solid rgba(0, 0, 0, 0.2);
  }
body {
background: #000;
}
#the-canvas {
  border: 1px solid black;
  direction: ltr;
}
</style>
<script src="/lib/pdf.js/build/pdf.js"></script>

<script>
// atob() is used to convert base64 encoded PDF to binary-like data.
// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/
// Base64_encoding_and_decoding.)
var pdfData = atob("'.base64_encode(file_get_contents($usedfile)).'");

// Loaded via <script> tag, create shortcut to access PDF.js exports.
var pdfjsLib = window["pdfjs-dist/build/pdf"];

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = "/lib/pdf.js/build/pdf.worker.js";

// Using DocumentInitParameters object to load binary data.
var loadingTask = pdfjsLib.getDocument({data: pdfData});

loadingTask.promise.then(function(pdf) {
  viewer = document.getElementById("pdf-viewer");
  console.log("PDF loaded");
  //// Fetch the first page
  var pageNumber = 1;
  pdf.getPage(pageNumber).then(function(page) {
  //var scale = Math.min((document.getElementById("pdf-viewer").height / unscaledViewport.height), (document.getElementById("pdf-viewer").width / unscaledViewport.width));
  //if(scale==0.0) {scale=0.9};
  for(page = 1; page <= pdf.numPages; page++) {
          console.log("rendering page "+page);
          canvas = document.createElement("canvas");    
          canvas.className = "pdf-page-canvas";         
          viewer.appendChild(canvas);          
            
          renderPage(pdf,page, canvas);
        }
  });

}, function (reason) {
  // PDF loading error
  console.error(reason);
});
    
function renderPage(pdf,pageNumber, canvas,scale) {
    pdf.getPage(pageNumber).then(function(page) {
        var unscaledViewport = page.getViewport({scale: 1.0});
        var scale = Math.min((window.innerHeight / unscaledViewport.height), ((window.innerWidth/2) / unscaledViewport.width));
        viewport = page.getViewport({ scale: scale });
        canvas.height = viewport.height;
        canvas.width = viewport.width;          
        page.render({canvasContext: canvas.getContext("2d"), viewport: viewport});
});
}
  //// Fetch the first page
  //var pageNumber = 1;
  //pdf.getPage(pageNumber).then(function(page) {
  //  console.log("Page loaded");
  //  
  //  //var scale = 1.0;
  //  //var viewport = page.getViewport({scale: scale});
  //  var unscaledViewport = page.getViewport({scale: 1.0});
  //  var scale = Math.min((document.getElementById("the-canvas").height / unscaledViewport.height), (document.getElementById("the-canvas").width / unscaledViewport.width));
  //  var viewport = page.getViewport({scale: scale});
  //  // Prepare canvas using PDF page dimensions
  //  var canvas = document.getElementById("the-canvas");
  //  var context = canvas.getContext("2d");
  //  canvas.height = viewport.height;
  //  canvas.width = viewport.width;
  //
  //  // Render PDF page into canvas context
  //  var renderContext = {
  //    canvasContext: context,
  //    viewport: viewport
  //  };
  //  var renderTask = page.render(renderContext);
  //  renderTask.promise.then(function () {
  //    console.log("Page rendered");
  //  });
  //});
  

</script>
</head><body>
<div id="wrapper" style="min-width: 300px;min-height: 300px" >
<div id="pdf-viewer" style="width: 100%;height: 100%;display:flex" ></div>
</div>
</body></html>
');
//<canvas id="the-canvas"></canvas>
    }
}
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');
