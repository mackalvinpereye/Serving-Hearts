<?php
$file = '../USER-VERIFICATION/vendor/autoload.php';

if (file_exists($file)) {
    require_once($file);
    echo "Autoload file loaded successfully.";
} else {
    echo "Autoload file not found at: " . realpath($file);
}