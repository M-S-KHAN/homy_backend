<?php
session_start(); // Start a new session or resume the existing one

// Include Guzzle for HTTP requests (make sure Guzzle is installed via Composer)
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? ''; // Collect email from POST data, default to empty string if not set
    $password = $_POST['password'] ?? ''; // Collect password from POST data, default to empty string if not set

    if (!empty($email) && !empty($password)) {
        // Initialize HTTP client
        $client = new Client();

        try {
            // Send a POST request to the API endpoint with email and password
            $response = $client->request('POST', 'http://localhost/api/auth/login.php', [
                'json' => ['email' => $email, 'password' => $password]
            ]);

            $data = json_decode($response->getBody(), true); // Decode JSON response

            // Check if login is successful
            if ($response->getStatusCode() == 200 && isset($data['user'])) {
                if ($data['user']['role'] == 'admin' || $data['user']['role'] == 'agent') {
                    $_SESSION['id'] = $data['user']['id']; // Set user_id in session
                    $_SESSION['email'] = $email; // Set email in session
                    $_SESSION['username'] = $data['user']['username']; // Set username in session
                    $_SESSION['role'] = $data['user']['role']; // Set role in session
                    header("Location: index.php"); // Redirect to selection form page
                    exit; // Terminate script execution
                } else {
                    $login_error = 'Unauthorized: You do not have permission to access this page.';
                }
            } else {
                $login_error = 'Invalid email or password.'; // Set error message for invalid credentials
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $data = json_decode($response->getBody()->getContents(), true);

                // Handle common error statuses
                if ($statusCode == 401) {
                    $login_error = $data['message'] ?? 'Unauthorized: Invalid email or password.';
                } else {
                    $login_error = $data['message'] ?? 'Error occurred: ' . $statusCode;
                }
            } else {
                $login_error = 'Login request failed: ' . $e->getMessage();
            }
        }
    } else {
        $login_error = 'Please enter email and password.'; // Set error message if email or password is not provided
    }
}
?>
<!doctype html>
<html lang="en" class="fullscreen-bg">

<head>
    <title>Login | Homy Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <!-- VENDOR CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <!-- MAIN CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
    <!-- ICONS -->
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">
</head>

<body>
<!-- WRAPPER -->
<div id="wrapper">
    <div class="vertical-align-wrap">
        <div class="vertical-align-middle">
            <div class="auth-box">
                <div class="left">
                    <div class="content">
                        <div class="header">
                            <div class="logo text-center logo-small"><img src="assets/img/logo-dark.svg"
                                                                          alt="Homy Logo"></div>
                            <p class="lead">Admin Login</p>
                        </div>
                        <?php if (!empty($login_error)) {
                            echo '<div class="alert alert-danger" role="alert">' . $login_error . '</div>';
                        } ?>
                        <form class="form-auth-small" method="POST">
                            <div class="form-group">
                                <label for="email" class="control-label sr-only">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="" placeholder="Email">
                            </div>
                            <div class="form-group">
                                <label for="password" class="control-label sr-only">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       value="" placeholder="Password">
                            </div>
                            <div class="form-group clearfix">
                                <label class="fancy-checkbox element-left">
                                    <input type="checkbox">
                                    <span>Remember me</span>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block">LOGIN</button>
<!--                            <div class="bottom">-->
<!--                                <span class="helper-text"><i class="fa fa-lock"></i> <a-->
<!--                                            href="#">Forgot password?</a></span>-->
<!--                            </div>-->
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- END WRAPPER -->
</body>

</html>
