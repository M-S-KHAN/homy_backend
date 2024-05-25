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
    // First, check the role of the user
    $roleCheckQuery = "SELECT role FROM users WHERE id = :user_id";
    $roleCheckStmt = $pdo->prepare($roleCheckQuery);
    $roleCheckStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $roleCheckStmt->execute();
    $userRole = $roleCheckStmt->fetchColumn();

    if (!$userRole) {
        http_response_code(404);
        echo json_encode(array("message" => "User not found."));
        exit();
    }

    // Adjust the query based on the user's role
    if ($userRole === 'admin' || $userRole === 'client') {
        $query = "SELECT p.id, p.title, p.description, p.price, p.address, p.lat, p.lng,
                  p.created_at, u.id as owner_id, u.username, u.email, u.profile_image_url,
                  GROUP_CONCAT(pi.image_url) as images,
                  (CASE WHEN MAX(f.user_id) IS NOT NULL THEN 1 ELSE 0 END) as is_favorite
              FROM properties p
              LEFT JOIN users u ON p.owner_id = u.id
              LEFT JOIN property_images pi ON pi.property_id = p.id
              LEFT JOIN favorites f ON f.property_id = p.id AND f.user_id = :user_id
              GROUP BY p.id";
        $stmt = $pdo->prepare($query);
    } elseif ($userRole === 'agent') {
        $query = "SELECT p.id, p.title, p.description, p.price, p.address, p.lat, p.lng,
              p.created_at, u.id as owner_id, u.username, u.email, u.profile_image_url,
              GROUP_CONCAT(pi.image_url) as images,
              (CASE WHEN MAX(f.user_id) IS NOT NULL THEN 1 ELSE 0 END) as is_favorite
          FROM properties p
          LEFT JOIN users u ON p.owner_id = u.id
          LEFT JOIN property_images pi ON pi.property_id = p.id
          LEFT JOIN favorites f ON f.property_id = p.id AND f.user_id = :user_id
          WHERE u.id = :owner_user_id
          GROUP BY p.id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':owner_user_id', $userId, PDO::PARAM_INT);
    } else {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. Only admins and agents can view properties."));
        exit();
    }

    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($properties)) {
        http_response_code(200);
        // Return an empty array if no properties are found
        echo json_encode([]);
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
?>
