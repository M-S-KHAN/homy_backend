<?php
include 'session.php';  // Ensure the session is started
require '../vendor/autoload.php';  // Include Guzzle for HTTP requests

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

// Fetch properties using an API call
function fetchProperties()
{
    $client = new Client();
    $apiUrl = 'http://localhost/api/properties/get-properties.php?user_id=' . $_SESSION['id'];
    try {
        $response = $client->request('GET', $apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 10,
        ]);
        $data = json_decode($response->getBody(), true);
        return $data ?? [];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

$properties = fetchProperties();

?>


<!doctype html>
<html lang="en">

<head>
    <title>Properties | Homy Real Estate</title>
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
                    <li><a href="landlords.php"><i class="lnr lnr-mustache"></i>
                            <span>Landlords</span></a></li>
                    <li><a href="properties.php" class="active"><i class="lnr lnr-apartment"></i>
                            <span>Properties</span></a>
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
        <div class="main-content">
            <div class="container-fluid">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <h3 class="page-title">Properties</h3>
                        </div>
                        <div class="col-md-9">
                            <button type="button" class="btn btn-success pull-right" data-toggle="modal"
                                    data-target="#addPropertyModal">Add New Property
                            </button>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-body">
                                <table class="table table-hover" id="propertiesTable">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Address</th>
                                        <th>Images</th>
                                        <th>Price</th>
                                        <th>Location</th>
                                        <th>Posted By</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td><?= $property['id'] ?></td>
                                            <td><?= htmlspecialchars($property['title']) ?></td>
                                            <td><?= htmlspecialchars($property['description']) ?></td>
                                            <td><?= htmlspecialchars($property['address']) ?></td>
                                            <td>
                                                <?php if (!empty($property['images'])): ?>
                                                    <?php foreach ($property['images'] as $image): ?>
                                                        <img src="<?= $image ?>" alt="Property Image"
                                                             style="width: 50px; height: auto; margin: 5px;">
                                                    <?php endforeach; ?>

                                                <?php else: ?>
                                                    No image available
                                                <?php endif; ?>
                                            </td>
                                            <td>$<?= number_format($property['price'], 2) ?></td>
                                            <td><?= $property['lat'] ?>, <?= $property['lng'] ?></td>
                                            <td><?= htmlspecialchars($property['owner']['username']) ?></td>
                                            <td>
                                                <button class="btn btn-primary" data-toggle="modal"
                                                        data-target="#editPropertyModal"
                                                        data-id="<?= $property['id'] ?>">Edit
                                                </button>
                                                <button class="btn btn-danger"
                                                        onclick="deleteProperty(<?= $property['id'] ?>);">Delete
                                                </button>
                                            </td>
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

<!-- Add Property Modal -->
<div id="addPropertyModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add New Property</h4>
            </div>
            <div class="modal-body">
                <form id="addPropertyForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>
                    <div class="form-group">
                        <label for="lat">Latitude:</label>
                        <input type="number" step=".00000001" class="form-control" id="lat" name="lat">
                    </div>
                    <div class="form-group">
                        <label for="lng">Longitude:</label>
                        <input type="number" step=".00000001" class="form-control" id="lng" name="lng">
                    </div>
                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="propertyImages">Images:</label>
                        <input type="file" class="form-control" id="propertyImages" name="images[]" multiple>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" id="uploadProgress"></div>
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

<!-- END WRAPPER -->
<!-- Javascript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.0/jquery.slimscroll.min.js"></script>
<script src="assets/scripts/klorofil-common.js"></script>
<script>
    $(document).ready(function () {
        $('#addPropertyForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('owner_id', <?= $_SESSION['id'] ?>);
            // remove images
            formData.delete('images[]');

            $.ajax({
                url: 'http://localhost/api/properties/add-property.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    updateProgress(0, 'Adding property...');
                },
                success: function (response) {
                    var propertyId = response.property_id;  // Assume your API returns the newly created property ID
                    updateProgress(25, 'Property added. Uploading images...');
                    uploadPropertyImages(propertyId, document.getElementById('propertyImages').files);
                },
                error: function () {
                    alert('Error adding property');
                }
            });
        });

        function uploadPropertyImages(propertyId, files) {
            var totalFiles = files.length;
            var uploadCount = 0;

            Array.from(files).forEach((file, index) => {
                var imageData = new FormData();
                imageData.append('property_id', propertyId);
                imageData.append('image', file);

                $.ajax({
                    url: 'http://localhost/api/properties/add-property-image.php',
                    type: 'POST',
                    data: imageData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        uploadCount++;
                        var progress = 25 + (75 * uploadCount / totalFiles); // Calculate progress
                        updateProgress(progress, 'Uploading image ' + (index + 1) + ' of ' + totalFiles);
                        if (uploadCount === totalFiles) {
                            completeUpload();
                        }
                    },
                    error: function () {
                        alert('Error uploading image ' + (index + 1));
                    }
                });
            });
        }

        function updateProgress(percent, message) {
            $('#uploadProgress').css('width', percent + '%').attr('aria-valuenow', percent).text(message);
        }

        function completeUpload() {
            updateProgress(100, 'Upload complete!');
            setTimeout(function () {
                $('#addPropertyModal').modal('hide');
                location.reload();
            }, 2000);
        }
    });
</script>


</body>

</html>

