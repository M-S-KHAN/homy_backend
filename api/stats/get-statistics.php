<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($userId === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing user ID."));
    exit();
}

try {
    // Determine the user's role
    $roleQuery = "SELECT role FROM users WHERE id = :user_id";
    $roleStmt = $pdo->prepare($roleQuery);
    $roleStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $roleStmt->execute();
    $userRole = $roleStmt->fetch(PDO::FETCH_ASSOC)['role'];

    if (!$userRole) {
        http_response_code(404);
        echo json_encode(array("message" => "User not found or role is undefined."));
        exit();
    }

    $stats = [];

    if ($userRole === 'admin') {
        // Gather admin-specific statistics
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_bids'] = $pdo->query("SELECT COUNT(*) FROM bids")->fetchColumn();
        $stats['bids_per_day'] = $pdo->query("SELECT DATE(created_at) as day, COUNT(*) as count FROM bids WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 DAY) GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_ASSOC);
        $stats['total_bid_amount'] = $pdo->query("SELECT SUM(bid_amount) FROM bids")->fetchColumn();
        $stats['total_properties'] = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
        $stats['top_properties'] = $pdo->query("SELECT p.id, p.title, COUNT(b.id) as total_bids FROM properties p LEFT JOIN bids b ON p.id = b.property_id GROUP BY p.id ORDER BY total_bids DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($userRole === 'agent') {
        // Gather landlord-specific statistics
        $stats['total_bids'] = $pdo->query("SELECT COUNT(*) FROM bids JOIN properties ON bids.property_id = properties.id WHERE properties.owner_id = $userId")->fetchColumn();
        $stats['bids_per_day'] = $pdo->query("SELECT DATE(bids.created_at) as day, COUNT(*) as count FROM bids JOIN properties ON bids.property_id = properties.id WHERE properties.owner_id = $userId AND bids.created_at >= DATE_SUB(CURDATE(), INTERVAL 5 DAY) GROUP BY DATE(bids.created_at)")->fetchAll(PDO::FETCH_ASSOC);
        $stats['total_bid_amount'] = $pdo->query("SELECT SUM(bid_amount) FROM bids JOIN properties ON bids.property_id = properties.id WHERE properties.owner_id = $userId")->fetchColumn();
        $stats['total_properties'] = $pdo->query("SELECT COUNT(*) FROM properties WHERE owner_id = $userId")->fetchColumn();
        $stats['top_properties'] = $pdo->query("SELECT p.id, p.title, COUNT(b.id) as total_bids FROM properties p LEFT JOIN bids b ON p.id = b.property_id WHERE p.owner_id = $userId GROUP BY p.id ORDER BY total_bids DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. Only admins and landlords can view these statistics."));
        exit();
    }

    http_response_code(200);
    echo json_encode($stats);

} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => $e->getMessage()));
}
