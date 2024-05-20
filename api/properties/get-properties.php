<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($userId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing or invalid user ID."));
    exit();
}

try {
    // Check if user exists
    $userCheckQuery = "SELECT id FROM users WHERE id = :user_id";
    $userCheckStmt = $pdo->prepare($userCheckQuery);
    $userCheckStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $userCheckStmt->execute();

    if ($userCheckStmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(array("message" => "User not found."));
        exit();
    }

    // Fetch properties
    $query = "SELECT p.id, p.title, p.description, p.price, p.address, p.lat, p.lng, 
            p.created_at, u.id as owner_id, u.username, u.email, u.profile_image_url, 
            GROUP_CONCAT(pi.image_url) as images, 
            (CASE WHEN MAX(f.user_id) IS NOT NULL THEN 1 ELSE 0 END) as is_favorite
        FROM properties p 
        LEFT JOIN users u ON p.owner_id = u.id 
        LEFT JOIN property_images pi ON pi.property_id = p.id
        LEFT JOIN favorites f ON f.property_id = p.id AND f.user_id = :user_id
        GROUP BY p.id, p.title, p.description, p.price, p.address, p.lat, p.lng, 
       p.created_at, u.id, u.username, u.email, u.profile_image_url";


    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $properties = $stmt->fetchAll();

    if (empty($properties)) {
        http_response_code(404);
        echo json_encode(array("message" => "No properties found for the given user."));
    } else {
        $response = [];
        foreach ($properties as $property) {
            $images = $property['images'] ? explode(',', $property['images']) : [];
            $response[] = array(
                "id" => (int)$property['id'],
                "title" => $property['title'],
                "description" => $property['description'],
                "price" => (double)$property['price'],
                "address" => $property['address'],
                "lat" => (double)$property['lat'],
                "lng" => (double)$property['lng'],
                "owner" => array(
                    "id" => (int)$property['owner_id'],
                    "username" => $property['username'],
                    "email" => $property['email'],
                    "profile_image_url" => $property['profile_image_url']
                ),
                "images" => $images,
                "created_at" => $property['created_at'],
                "isFavorite" => (bool)$property['is_favorite']
            );
        }

        http_response_code(200);
        echo json_encode($response);
    }

} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}
