<?php
$targurl=file_get_contents('/var/www/.starturl');
header("Location: ".$targurl);
print "<meta http-equiv='refresh' content='0;url=".$targurl."'>";
exit();
?>
