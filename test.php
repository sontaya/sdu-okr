<?php
echo 'Current timezone: ' . date_default_timezone_get() . '<br>';
echo 'Current server time: ' . date('Y-m-d H:i:s'). '<br>';

if (extension_loaded('oci8')) {
    echo "✅ OCI8 extension is loaded!<br>";
    echo "OCI8 version: " . phpversion('oci8') . "<br>";
} else {
    echo "❌ OCI8 extension is NOT loaded!";
}

phpinfo();