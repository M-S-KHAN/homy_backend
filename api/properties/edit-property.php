<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$propertyId = isset($_POST['propertyId']) ? intval($_POST['propertyId']) : null;

if ($userId === null || $propertyId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required user or property ID."));
    exit();
}

try {
    // Verify the user's role or if they are the owner of the property
    $authQuery = "SELECT role FROM users WHERE id = :user_id AND (role = 'admin' OR id = (SELECT owner_id FROM properties WHERE id = :property_id))";
    $authStmt = $pdo->prepare($authQuery);
    $authStmt->bindParam(':user_id', $userId);
    $authStmt->bindParam(':property_id', $propertyId);
    $authStmt->execute();

    if ($authStmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. Only admins or the property owner can edit this property."));
        exit();
    }

    // Delete images if any are marked for deletion
    if (!empty($_POST['deleteImages'])) {
        $deleteImagesQuery = "DELETE FROM property_images WHERE id IN (" . implode(',', array_map('intval', $_POST['deleteImages'])) . ")";
        $pdo->exec($deleteImagesQuery);
    }

    // Update property details
    $updateQuery = "UPDATE properties SET address = :address, title = :title, description = :description, price = :price WHERE id = :property_id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':title', $_POST['title']);
    $updateStmt->bindParam(':description', $_POST['description']);
    $updateStmt->bindParam(':property_id', $propertyId);
    $updateStmt->bindParam(':price', $_POST['price']);
    $updateStmt->bindParam(':address', $_POST['address']);
    $updateStmt->execute();

    http_response_code(200);
    echo json_encode(array("message" => "Property updated successfully."));
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

?>
