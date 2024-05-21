<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$propertyId = isset($_GET['property_id']) ? intval($_GET['property_id']) : null;
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($propertyId === null || $userId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing property ID or user ID."));
    exit();
}

try {
    $query = "SELECT p.id, p.title, p.description, p.price, p.address, p.lat, p.lng, 
                p.created_at, u.id as owner_id, u.username, u.email, u.profile_image_url, 
                GROUP_CONCAT(pi.image_url) as images, 
                MAX(CASE WHEN f.user_id IS NOT NULL THEN true ELSE false END) as isFavorite
            FROM properties p 
            LEFT JOIN users u ON p.owner_id = u.id 
            LEFT JOIN property_images pi ON pi.property_id = p.id
            LEFT JOIN favorites f ON f.property_id = p.id AND f.user_id = :user_id
            WHERE p.id = :property_id
            GROUP BY p.id, p.title, p.description, p.price, p.address, p.lat, p.lng, 
           p.created_at, u.id, u.username, u.email, u.profile_image_url";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        http_response_code(404);
        echo json_encode(array("message" => "Property not found."));
    } else {
        $property['images'] = $property['images'] ? explode(',', $property['images']) : [];
        $property['isFavorite'] = (bool)$property['isFavorite'];

        http_response_code(200);
        echo json_encode(array("property" => $property));
    }

} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}

