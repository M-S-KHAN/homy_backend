<?php
include 'session.php'; // Ensure the user is logged in
require '../vendor/autoload.php'; // Include Composer's autoload for Guzzle

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Function to fetch dashboard data
function fetchDashboardData()
{
    $client = new Client();
    $apiUrl = 'http://localhost/api/stats/get-statistics.php?user_id=' . $_SESSION['id'];

    try {
        $response = $client->request('GET', $apiUrl, [
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $data = json_decode($response->getBody(), true);
            return $data;
        } else {
            throw new Exception("Failed to fetch data. Status code: " . $statusCode);
        }
    } catch (RequestException $e) {
        return ['error' => 'Request failed: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['error' => 'Error: ' . $e->getMessage()];
    }
}

// Attempt to fetch data
$dashboardData = fetchDashboardData();

// Extracting individual variables needed for the page
$total_users = $dashboardData['total_users'] ?? 0;
$total_bids = $dashboardData['total_bids'] ?? 0;
$bids_per_day = $dashboardData['bids_per_day'] ?? [];
$total_landlords = $dashboardData['total_landlords'] ?? 0;
$total_bid_amount = $dashboardData['total_bid_amount'] ?? "0.00";
$total_properties = $dashboardData['total_properties'] ?? 0;
$top_properties = $dashboardData['top_properties'] ?? [];

// Extracting days and counts for the bids per day
$days = array_column($dashboardData['bids_per_day'], 'day');
$counts = array_column($dashboardData['bids_per_day'], 'count');

// Encode arrays for JavaScript
$days_json = json_encode($days);
$counts_json = json_encode($counts);

// Check for errors in fetching data
if (isset($dashboardData['error'])) {
    // Handle error appropriately
    die('Error retrieving data: ' . $dashboardData['error']);
}
?>


<!doctype html>
<html lang="en">
<head>
    <title>Dashboard | Real Estate Management</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <!-- VENDOR CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon.png">
</head>
<body>
<!-- WRAPPER -->
<div id="wrapper">
    <!-- NAVBAR -->
    <?php include 'navbar.php'; ?>
    <!-- LEFT SIDEBAR -->
    <div id="sidebar-nav" class="sidebar">
        <div class="sidebar-scroll">
            <nav>
                <ul class="nav">
                    <li><a href="index.php" class="active"><i class="lnr lnr-pie-chart"></i> <span>Dashboard</span></a>
                    </li>
                    <li style="display: <?php echo $is_admin ? 'block' : 'none' ?>"><a href="landlords.php"><i
                                    class="lnr lnr-mustache"></i>
                            <span>Landlords</span></a></li>
                    <li><a href="properties.php" class=""><i class="lnr lnr-apartment"></i> <span>Properties</span></a>
                    </li>
                    <li><a href="bids.php"><i class="lnr lnr-book"></i> <span>Bids</span></a></li>
                    <li style="display: <?php echo $is_admin ? 'block' : 'none' ?>"><a href="users.php" class=""><i
                                    class="lnr lnr-users"></i> <span>Users</span></a></li>
                </ul>
            </nav>
        </div>
    </div>
    <!-- END LEFT SIDEBAR -->
    <!-- MAIN -->
    <div class="main">
        <div class="main-content">
            <div class="container-fluid">
                <div class="panel panel-headline">
                    <div class="panel-heading">
                        <h3 class="panel-title">Statistics Overview</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <!-- Dynamic Metrics Here Based on PHP Variables -->
                            <?php

                            if ($is_admin) {
                                echo '
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-user"></i></span>
                                            <p>
                                                <span class="number">' . $total_users . '</span>
                                                <span class="title">Total Users</span>
                                            </p>
                                        </div>
                                    </div>';
                            }
                            ?>

                            <div class="col-md-3">
                                <div class="metric">
                                    <span class="icon"><i class="fa fa-home"></i></span>
                                    <p>
                                        <span class="number"><?php echo $total_properties; ?></span>
                                        <span class="title">Total Properties</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric">
                                    <span class="icon"><i class="fa fa-gavel"></i></span>
                                    <p>
                                        <span class="number"><?php echo $total_bids; ?></span>
                                        <span class="title">Total Bids</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric">
                                    <span class="icon"><i class="fa fa-dollar"></i></span>
                                    <p>
                                        <span class="number"><?php echo $total_bid_amount; ?></span>
                                        <span class="title">Total Bid Amount</span>
                                    </p>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Bids/day (Last 5)</h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="headline-chart" class="ct-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Top Properties</h3>
                                    </div>
                                    <div class="panel-body no-padding">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Total Bids</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($top_properties as $property): ?>
                                                <tr>
                                                    <td><?php echo $property['id']; ?></td>
                                                    <td><?php echo $property['title']; ?></td>
                                                    <td><?php echo $property['total_bids']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTENT -->
    <div class="clearfix"></div>
    <footer>
        <div class="container-fluid">
            <p class="copyright">&copy; 2024 <a href="" target="_blank">Homy Real Estate</a>. All Rights Reserved.</p>
        </div>
    </footer>
</div>
<!-- Javascript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.0/jquery.slimscroll.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/easy-pie-chart/2.1.6/jquery.easypiechart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.js"></script>
<script src="assets/scripts/homy-common.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var days = <?php echo $days_json; ?>;
        var counts = <?php echo $counts_json; ?>;

        new Chartist.Line('#headline-chart', {
            labels: days,
            series: [counts]
        }, {
            fullWidth: true,
            chartPadding: {
                right: 40
            },
            low: 0,
            high: Math.max(...counts) + 1, // Ensure the maximum value has a bit of padding above
        });
    });
</script>

</body>
</html>