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
                MAX(CASE WHEN f.user_id IS NOT NULL THEN true ELSE false END) as isFavorite
              FROM properties p 
              LEFT JOIN users u ON p.owner_id = u.id 
              LEFT JOIN favorites f ON f.property_id = p.id AND f.user_id = :user_id
              WHERE p.id = :property_id
              GROUP BY p.id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        http_response_code(404);
        echo json_encode(array("message" => "Property not found."));
    } else {
        // Query to fetch images separately
        $imagesQuery = "SELECT id, image_url FROM property_images WHERE property_id = :property_id";
        $imagesStmt = $pdo->prepare($imagesQuery);
        $imagesStmt->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
        $imagesStmt->execute();
        $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

        // has_bidded
        $bidQuery = "SELECT * FROM bids WHERE user_id = :user_id AND property_id = :property_id";
        $bidStmt = $pdo->prepare($bidQuery);
        $bidStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $bidStmt->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
        $bidStmt->execute();

        $property['has_bidded'] = $bidStmt->fetch(PDO::FETCH_ASSOC) ? true : false;

        $property['images'] = $images;
        $property['isFavorite'] = (bool)$property['isFavorite'];
        $property['price'] = (int)$property['price']; // Ensure price is an integer

        http_response_code(200);

        echo json_encode(
            array(
                "id" => (int)$property['id'],
                "title" => $property['title'],
                "description" => $property['description'],
                "price" => (int)$property['price'],
                "address" => $property['address'],
                "lat" => (double)$property['lat'],
                "lng" => (double)$property['lng'],
                "owner" => array(
                    "id" => (int)$property['owner_id'],
                    "username" => $property['username'],
                    "email" => $property['email'],
                    "profile_image_url" => $property['profile_image_url']
                ),
                "images" => $property['images'],
                "created_at" => $property['created_at'],
                "is_favorite" => $property['isFavorite'],
                "has_bidded" => $property['has_bidded']
            )
        );

    }

} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}
