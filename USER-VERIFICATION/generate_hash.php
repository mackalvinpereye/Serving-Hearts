<?php
// Define the password you want to hash
$plainPassword = 'Admin2024'; 

// Hash the password
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

// Output the hashed password
echo "Hashed Password: " . $hashedPassword;
?>