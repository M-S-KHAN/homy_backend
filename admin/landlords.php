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
    $apiUrl = 'http://localhost/api/users/get-users.php?role=agent&user_id=' . $userId;

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
        if (!isset($data['users']) || !is_array($data['users'])) {
            throw new Exception('Invalid data structure: Missing "users" key.');
        }

        return $data['users'];

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
$landlords = fetchLandlords();

// Check for errors in the returned data
if (isset($landlords['error'])) {
    echo "Error: " . $landlords['error'];
    exit;
}

// Proceed to use $landlords data as needed
?>


<!doctype html>
<html lang="en">

<head>
    <title>Admin Home | Homy Real Estate</title>
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
                    <li><a href="landlords.php" class="active"><i class="lnr lnr-mustache"></i>
                            <span>Landlords</span></a></li>
                    <li><a href="properties.php" class=""><i class="lnr lnr-apartment"></i> <span>Properties</span></a>
                    </li>
                    <li><a href="bids.php" class=""><i class="lnr lnr-book"></i> <span>Bids</span></a></li>
                    <li><a href="users.php" class=""><i class="lnr lnr-users"></i> <span>Users</span></a></li>
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
                        <div class="col-md-3">
                            <h3 class="page-title">Landlords</h3>
                        </div>
                        <div class="col-md-9">
                            <button type="button" class="btn btn-success pull-right" data-toggle="modal"
                                    data-target="#addUserModal">Add New Landlord
                            </button>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-body">
                                <table class="table table-hover" id="landlordsTable">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Username</th>
                                        <th>Member Since</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($landlords as $index => $landlord): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($landlord['id']) ?></td>
                                            <td><?= htmlspecialchars($landlord['email']) ?></td>
                                            <td><?= htmlspecialchars($landlord['username']) ?></td>
                                            <td><?= htmlspecialchars($landlord['created_at']) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary edit-landlord"
                                                        data-id="<?= $landlord['id'] ?>" data-toggle="modal" name="edit-landlord"
                                                        data-target="#editUserModal">Edit
                                                </button>
                                                <button type="button" class="btn btn-danger delete-landlord"
                                                        data-id="<?= $landlord['id'] ?>">Delete
                                                </button>
                                            </td>
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

<!-- Add Modal -->
<div id="addUserModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add New User</h4>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select class="form-control" id="role" required>
                            <option value="agent">Landlord</option>
                            <option value="client">Client</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit User</h4>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" class="form-control" id="edit-id" name="id" required>
                    <div class="form-group">
                        <label for="edit-username">Username:</label>
                        <input type="text" class="form-control" id="edit-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email:</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-password">New Password (leave blank if unchanged):</label>
                        <input type="password" class="form-control" id="edit-password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit-role">Role:</label>
                        <select class="form-control" id="edit-role" name="role" required>
                            <option value="agent">Landlord</option>
                            <option value="client">Tenant</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<!-- END WRAPPER -->
<!-- Javascript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.0/jquery.slimscroll.min.js"></script>
<script src="assets/scripts/klorofil-common.js"></script>
<script>

    // function parseRole(role) {
    //     switch (role) {
    //         case 'admin':
    //             return 'admin';
    //         case 'agent':
    //             return 'landlord';
    //         case 'client':
    //             return 'client';
    //         default:
    //             return 'Unknown';
    //     }
    // }

    $(document).ready(function () {
        $('#addUserForm').on('submit', function (e) {
            e.preventDefault(); // Prevent the form from submitting via the browser.

            var userData = {
                username: $('#username').val(),
                email: $('#email').val(),
                password: $('#password').val(),
                role: $('#role').val(),
                admin_id: <?= $_SESSION['id'] ?>
            };

            $.ajax({
                url: 'http://localhost/api/users/add-user.php', // The API endpoint
                type: 'POST',
                data: JSON.stringify(userData),
                contentType: 'application/json',
                success: function (response) {
                    alert('User added successfully');
                    $('#addUserModal').modal('hide'); // Hide the modal after success
                    location.reload(); // Optionally reload the page or part of it to update the list
                },
                error: function () {
                    alert('Error adding user');
                }
            });
        });

        // Assuming you trigger the modal with specific user data
        $('.edit-landlord').on('click', function () {
            var userId = $(this).data('id'); // Get the user ID
            // Fetch user data for editing
            $.ajax({
                url: 'http://localhost/api/users/get-user.php?user_id=' + userId + '&admin_id=<?= $_SESSION['id'] ?>',
                type: 'GET',
                success: function (response) {
                    $('#edit-id').val(response.user.id);
                    $('#edit-username').val(response.user.username);
                    $('#edit-email').val(response.user.email);
                    $('#edit-role').val(response.user.role);
                    $('#editUserModal').modal('show');
                },
                error: function () {
                    alert('Failed to fetch user details.');
                }
            });
        });

        $('#editUserForm').on('submit', function (e) {
            e.preventDefault();

            console.log('Form submitted');
            var updatedUserData = {
                user_id: $('#edit-id').val(),
                admin_id: <?= $_SESSION['id'] ?>,
                username: $('#edit-username').val(),
                email: $('#edit-email').val(),
                password: $('#edit-password').val(),
                role: $('#edit-role').val()
            };

            // Send the updated user data to the server
            $.ajax({
                url: 'http://localhost/api/users/update-user.php',
                type: 'POST',
                data: JSON.stringify(updatedUserData),
                contentType: 'application/json',
                success: function (response) {
                    alert('User updated successfully');
                    $('#editUserModal').modal('hide');
                    location.reload(); // Optionally reload to update the displayed data
                },
                error: function (e) {
                    alert('Error updating user' + e.responseText);
                }
            });
        });
    });

    $(document).ready(function () {
        $('.delete-landlord').click(function () {
            var data = {
                user_id: $(this).data('id'),
                admin_id: <?= $_SESSION['id'] ?>
            };
            if (confirm('Are you sure you want to delete this landlord?')) {
                $.ajax({
                    url: 'http://localhost/api/users/delete-user.php',
                    type: 'DELETE',
                    data: JSON.stringify(data),
                    success: function (response) {
                        alert('Landlord deleted successfully');
                        window.location.reload();
                    },
                    error: function () {
                        alert('Error deleting landlord');
                    }
                });
            }
        });
    });
</script>

</body>

</html>
