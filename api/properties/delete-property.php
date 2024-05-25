<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

// Get query parameters
$propertyId = isset($_GET['property_id']) ? $_GET['property_id'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (empty($propertyId) || empty($userId)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required information: property_id or user_id."));
    exit();
}

try {
    $pdo->beginTransaction(); // Start transaction

    // Verify if the user is an admin or the property owner
    $authQuery = "SELECT role FROM users WHERE id = :user_id";
    $authStmt = $pdo->prepare($authQuery);
    $authStmt->bindParam(':user_id', $userId);
    $authStmt->execute();
    $userRole = $authStmt->fetchColumn();

    if (!$userRole) {
        $pdo->rollBack(); // Rollback transaction
        http_response_code(404);
        echo json_encode(array("message" => "User not found."));
        exit();
    }

    // Check if the user is the property owner or an admin
    if ($userRole !== 'admin') {
        $ownerCheckQuery = "SELECT id FROM properties WHERE id = :property_id AND owner_id = :user_id";
        $ownerCheckStmt = $pdo->prepare($ownerCheckQuery);
        $ownerCheckStmt->bindParam(':property_id', $propertyId);
        $ownerCheckStmt->bindParam(':user_id', $userId);
        $ownerCheckStmt->execute();
        if ($ownerCheckStmt->rowCount() == 0) {
            $pdo->rollBack(); // Rollback transaction
            http_response_code(403);
            echo json_encode(array("message" => "Access denied. Only admins or the property owner can delete the property."));
            exit();
        }
    }

    // Delete associated bids first
    $deleteBidsQuery = "DELETE FROM bids WHERE property_id = :property_id";
    $deleteBidsStmt = $pdo->prepare($deleteBidsQuery);
    $deleteBidsStmt->bindParam(':property_id', $propertyId);
    $deleteBidsStmt->execute();

    // Delete associated images
    $deleteImagesQuery = "DELETE FROM property_images WHERE property_id = :property_id";
    $deleteImagesStmt = $pdo->prepare($deleteImagesQuery);
    $deleteImagesStmt->bindParam(':property_id', $propertyId);
    $deleteImagesStmt->execute();

    // Delete the property
    $deletePropertyQuery = "DELETE FROM properties WHERE id = :property_id";
    $deletePropertyStmt = $pdo->prepare($deletePropertyQuery);
    $deletePropertyStmt->bindParam(':property_id', $propertyId);
    $deletePropertyStmt->execute();

    $pdo->commit(); // Commit transaction
    http_response_code(200);
    echo json_encode(array("message" => "Property and all associated records successfully deleted."));

} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback transaction on error
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred during deletion.", "error" => $e->getMessage()));
} catch (Exception $e) {
    $pdo->rollBack(); // Rollback transaction on error
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

?>
