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
                    <li style="display: <?php echo $is_admin ? 'block' : 'none' ?>"><a href="landlords.php"><i
                                    class="lnr lnr-mustache"></i>
                            <span>Landlords</span></a></li>
                    <li><a href="properties.php" class="active"><i class="lnr lnr-apartment"></i>
                            <span>Properties</span></a>
                    </li>
                    <li><a href="bids.php" class=""><i class="lnr lnr-book"></i> <span>Bids</span></a></li>
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
                                                <button class="btn btn-primary" name="edit-property-button"
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
    <footer>
        <div class="container-fluid">
            <p class="copyright">&copy; 2024 <a href="" target="_blank">Homy Real Estate</a>. All Rights Reserved.</p>
        </div>
    </footer>
</div>
<!-- END MAIN -->

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


<!-- Edit Property Modal -->
<div id="editPropertyModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Property</h4>
            </div>
            <div class="modal-body">
                <form id="editPropertyForm" enctype="multipart/form-data">
                    <input type="hidden" id="edit-propertyId" name="propertyId">
                    <div class="form-group">
                        <label for="edit-title">Title:</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Description:</label>
                        <textarea class="form-control" id="edit-description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-address">Address:</label>
                        <input type="text" class="form-control" id="edit-address" name="address">
                    </div>
                    <div class="form-group">
                        <label for="edit-price">Price:</label>
                        <input type="number" class="form-control" id="edit-price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Current Images:</label>
                        <div id="edit-images-list" class="d-flex flex-wrap">
                            <!-- Images will be loaded here by JavaScript -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-newImages">Add New Images:</label>
                        <input type="file" class="form-control" id="edit-newImages" name="newImages[]" multiple>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Property</button>
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
<script src="assets/scripts/homy-common.js"></script>
<script>

    $(document).ready(function () {

        $('button[name="edit-property-button"]').on('click', function () {
            var propertyId = $(this).data('id');
            console.log(propertyId);
            loadPropertyDetails(propertyId);
            $('#editPropertyModal').modal('show');
        });

        $('#editPropertyForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('property_id', $('#edit-propertyId').val());
            formData.append('user_id', <?= $_SESSION['id'] ?>);
            $('.edit-image-checkbox:checked').each(function () {
                formData.append('deleteImages[]', $(this).val());  // Append images to delete
            });

            $.ajax({
                url: 'http://localhost/api/properties/edit-property.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    alert('Property updated successfully');
                    $('#editPropertyModal').modal('hide');
                    location.reload();  // Refresh to show updated data
                },
                error: function () {
                    alert('Error updating property');
                }
            });
        });


        $('#addPropertyForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('owner_id', <?= $_SESSION['id'] ?>);

            // extract images from form
            var images = document.getElementById('propertyImages').files;
            formData.delete('images[]')

            // convert to json
            var object = {};
            formData.forEach(function (value, key) {
                object[key] = value;
            });

            $.ajax({
                url: 'http://localhost/api/properties/add-property.php',
                type: 'POST',
                data: JSON.stringify(object),
                contentType: false,
                processData: false,
                beforeSend: function () {
                    updateProgress(0, 'Adding property...');
                },
                success: function (response) {
                    if (images.length === 0) {
                        completeUpload();
                        return;
                    }
                    var propertyId = response.property.id;  // Assume your API returns the newly created property ID
                    updateProgress(25, 'Property added. Uploading images...');
                    uploadPropertyImages(propertyId, document.getElementById('propertyImages').files);
                },
                error: function (e) {
                    alert('Error adding property' + e.responseText);
                }
            });
        });

        function uploadPropertyImages(propertyId, files) {
            var totalFiles = files.length;
            var uploadCount = 0;

            Array.from(files).forEach((file, index) => {
                var reader = new FileReader();

                reader.onload = function (e) {
                    var object = {
                        property_id: propertyId,
                        image_base64: e.target.result  // Set base64 encoded image
                    };

                    // Perform AJAX call inside the onload function
                    $.ajax({
                        url: 'http://localhost/api/properties/add-property-image.php',
                        type: 'POST',
                        data: JSON.stringify(object),
                        contentType: 'application/json',  // Set the appropriate content type for JSON
                        processData: false,  // Ensure processData is false when sending JSON
                        success: function (response) {
                            uploadCount++;
                            var progress = 25 + (75 * uploadCount / totalFiles); // Calculate progress
                            updateProgress(progress, 'Uploading image ' + (index + 1) + ' of ' + totalFiles);
                            if (uploadCount === totalFiles) {
                                completeUpload();  // Complete upload process once all files are uploaded
                            }
                        },
                        error: function () {
                            alert('Error uploading image ' + (index + 1));
                        }
                    });
                };

                reader.onerror = function (error) {
                    console.log('Error reading file: ', error);
                };

                reader.readAsDataURL(file);  // Convert image file to base64 string
            });
        }

        function updateProgress(percent, message) {
            $('.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent).text(message);
        }

        function completeUpload() {
            updateProgress(100, 'Upload complete!');
            setTimeout(function () {
                $('#addPropertyModal').modal('hide');
                location.reload();  // Reload the page to show all updates
            }, 2000);
        }

    });

    function loadPropertyDetails(propertyId) {
        $.ajax({
            url: 'http://localhost/api/properties/get-property.php?property_id=' + propertyId + '&user_id=<?= $_SESSION['id'] ?>',
            type: 'GET',
            success: function (data) {
                $('#edit-propertyId').val(data.id);
                $('#edit-title').val(data.title);
                $('#edit-description').val(data.description);
                $('#edit-address').val(data.address);
                $('#edit-price').val(data.price);
                var imagesHtml = '';
                data.images.forEach(function (image, index) {
                    imagesHtml += `<div class="edit-image-wrapper mr-2">
                                   <img src="${image.image_url}" alt="Property Image" class="img-fluid img-thumbnail">
                                   <div class="form-check">
                                       <input class="form-check-input edit-image-checkbox" type="checkbox" value="${image.id}" id="deleteImage${index}">
                                       <label class="form-check-label" for="deleteImage${index}">Delete</label>
                                   </div>
                               </div>`;
                });
                $('#edit-images-list').html(imagesHtml);
            }
        });
    }


    function deleteProperty(propertyId) {
        var data = {
            property_id: propertyId,
            user_id: <?= $_SESSION['id'] ?>
        };
        if (confirm('Are you sure you want to delete this property and images?')) {
            $.ajax({
                url: 'http://localhost/api/properties/delete-property.php',
                type: 'DELETE',
                data: JSON.stringify(data),
                success: function (response) {
                    alert('Property deleted successfully');
                    window.location.reload();
                },
                error: function () {
                    alert('Error deleting Property');
                }
            });
        }
    }
</script>


</body>

</html>

