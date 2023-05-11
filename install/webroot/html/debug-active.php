<?php 

if(file_exists("/tmp/.debug_mode_active")) { 
print("DEBUG_ENABLED");
} else {
print("DEBUG_DISABLED");
}
exec('test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete');
