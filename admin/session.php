<?php
session_start(); // Starts a new or resumes the current session

function isLoggedIn()
{
    return isset($_SESSION['id']); // Checks if the user_id is set in the session
}

if (!isLoggedIn()) {
    header('Location: login.php'); // Redirects to the login page if the user is not logged in (W3Schools)
    exit; // Exits the script
}

$is_admin = $_SESSION['role'] === 'admin';
