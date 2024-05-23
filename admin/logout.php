<?php
session_start(); // Start or resume the existing session
session_unset();  // Remove all session variables to clear the current session
session_destroy(); // Destroy the session completely, effectively logging out the user

header('Location: login.php'); // Redirect the user to the login page after logout
exit; // Ensure that the script stops running after redirect
