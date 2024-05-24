<?php
include 'session.php';
// Ensure Guzzle is loaded via Composer's autoload
require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

/**
 * Fetches landlord data from the specified API endpoint using GuzzleHttp.
 *
 * This function sends a GET request to the API. It expects the API to return
 * a JSON formatted string of landlords under the "users" key.
 *
 * @return array An associative array of landlords or an error message as an array.
 */
function fetchLandlords()
{
    $userId = $_SESSION['id'] ?? null;

    // Define the API endpoint URL
    $apiUrl = 'http://localhost/api/bids/get-bids.php?user_id=' . $userId;

    // Initialize GuzzleHttp client
    $client = new Client();

    try {
        // Send a GET request to the API endpoint
        $response = $client->request('GET', $apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 10, // Timeout in seconds
        ]);

        // Get the body and decode it as JSON
        $data = json_decode($response->getBody(), true);

        // Check if the "users" key exists in the data
        if (!isset($data['bids']) || !is_array($data['bids'])) {
            throw new Exception('Invalid data structure: Missing "users" key.');
        }

        return $data['bids'];

    } catch (ClientException $e) {
        // Handle client exceptions (4xx responses)
        return ['error' => 'Client error: ' . $e->getResponse()->getBody()];
    } catch (RequestException $e) {
        // Handle network errors (connection timeout, DNS errors, etc.)
        return ['error' => 'Request error: ' . $e->getMessage()];
    } catch (Exception $e) {
        // Handle other errors
        return ['error' => 'General error: ' . $e->getMessage()];
    }
}

// Fetch the landlords using the fetchLandlords function
$bids = fetchLandlords();

// Check for errors in the returned data
if (isset($bids['error'])) {
    echo "Error: " . $bids['error'];
    exit;
}

// Proceed to use $bids data as needed
?>


<!doctype html>
<html lang="en">

<head>
    <title>Bids | Homy Real Estate</title>
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
    <!-- NAVBAR -->
    <?php include 'navbar.php'; ?>

    <!-- END NAVBAR -->
    <!-- LEFT SIDEBAR -->
    <div id="sidebar-nav" class="sidebar">
        <div class="sidebar-scroll">
            <nav>
                <ul class="nav">
                    <li><a href="index.php"><i class="lnr lnr-pie-chart"></i> <span>Dashboard</span></a></li>
                    <li style="display: <?php echo $is_admin ? 'block' : 'none' ?>"><a href="landlords.php"><i
                                    class="lnr lnr-mustache"></i>
                            <span>Landlords</span></a></li>
                    <li><a href="properties.php" class=""><i class="lnr lnr-apartment"></i> <span>Properties</span></a>
                    </li>
                    <li><a href="bids.php" class="active"><i class="lnr lnr-book"></i> <span>Bids</span></a></li>
                    <li style="display: <?php echo $is_admin ? 'block' : 'none' ?>"><a href="users.php" class=""><i
                                    class="lnr lnr-users"></i> <span>Users</span></a></li>
                </ul>
            </nav>
        </div>
    </div>
    <!-- END LEFT SIDEBAR -->
    <!-- MAIN -->
    <div class="main">
        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="page-title">Bids</h3>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-body">
                                <table class="table table-hover" id="bidsTable">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Bid Amount</th>
                                        <th>Message</th>
                                        <th>Placed By</th>
                                        <th>Property Title</th>
                                        <th>Created At</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($bids as $bid): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($bid['id']) ?></td>
                                            <td>$<?= htmlspecialchars($bid['bid_amount']) ?></td>
                                            <td><?= htmlspecialchars($bid['message']) ?></td>
                                            <td><?= htmlspecialchars($bid['by']['username']) ?>
                                                (<?= htmlspecialchars($bid['by']['email']) ?>)
                                            </td>
                                            <td><?= htmlspecialchars($bid['property']['title']) ?></td>
                                            <td><?= htmlspecialchars($bid['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- END TABLE HOVER -->
                    </div>
                </div>


            </div>
        </div>
        <!-- END MAIN CONTENT -->
    </div>
    <!-- END MAIN -->
    <div class="clearfix"></div>
    <footer>
        <div class="container-fluid">
            <p class="copyright">&copy; 2024 <a href="" target="_blank">Homy Real Estate</a>. All Rights Reserved.</p>
        </div>
    </footer>
</div>
<!-- END WRAPPER -->
<!-- Javascript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.0/jquery.slimscroll.min.js"></script>
<script src="assets/scripts/homy-common.js"></script>

</body>

</html>
