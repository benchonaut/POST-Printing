<?php
function url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}
   
file_put_contents("/tmp/.debug_mode_active","TRUE");
print('<html><body><head>
 <meta http-equiv="refresh" content="1; URL='.url_origin( $_SERVER, true ) .'/setup/show-debug.php">
<style>
body {
background: radial-gradient(ellipse at center, #f5f5f5 0%,#ddd 100%);
}
</style>
</head></he><center><h1>Debug Mode activated for 30 minutes</h1></center></body></html>');
print("</body></html>");
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');
