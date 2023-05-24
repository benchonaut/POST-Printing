<?php
$targurl=file_get_contents('/var/www/.starturl');
header('Expires: Sun, 01 Jan 2000 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header("Location: ".$targurl);
print "<meta http-equiv='refresh' content='0;url=".$targurl."'>";
print('<meta http-equiv="expires" content="Sun, 01 Jan 2000 00:00:00 GMT"/>');
print('<meta http-equiv="pragma" content="no-cache" />');
exit();
?>
