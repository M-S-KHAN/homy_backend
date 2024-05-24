<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (empty($data->property_id) || empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required information: property_id or user_id."));
    exit();
}

try {
    // First, verify if the user is an admin or the property owner
    $authQuery = "SELECT role, id FROM users WHERE id = :user_id";
    $authStmt = $pdo->prepare($authQuery);
    $authStmt->bindParam(':user_id', $data->user_id);
    $authStmt->execute();
    $user = $authStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(array("message" => "User not found."));
        exit();
    }

    // Check if the user is the property owner or an admin
    if ($user['role'] != 'admin') {
        $ownerCheckQuery = "SELECT id FROM properties WHERE id = :property_id AND owner_id = :user_id";
        $ownerCheckStmt = $pdo->prepare($ownerCheckQuery);
        $ownerCheckStmt->bindParam(':property_id', $data->property_id);
        $ownerCheckStmt->bindParam(':user_id', $data->user_id);
        $ownerCheckStmt->execute();
        if ($ownerCheckStmt->rowCount() == 0) {
            http_response_code(403);
            echo json_encode(array("message" => "Access denied. Only admins or the property owner can delete the property."));
            exit();
        }
    }

    // Delete associated images first
    $deleteImagesQuery = "DELETE FROM property_images WHERE property_id = :property_id";
    $deleteImagesStmt = $pdo->prepare($deleteImagesQuery);
    $deleteImagesStmt->bindParam(':property_id', $data->property_id);
    $deleteImagesStmt->execute();

    // Now delete the property
    $deletePropertyQuery = "DELETE FROM properties WHERE id = :property_id";
    $deletePropertyStmt = $pdo->prepare($deletePropertyQuery);
    $deletePropertyStmt->bindParam(':property_id', $data->property_id);

    if ($deletePropertyStmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Property successfully deleted."));
    } else {
        throw new Exception("Unable to delete property.");
    }
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

?>
