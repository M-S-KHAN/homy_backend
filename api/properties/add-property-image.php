<?php

include_once '../../config/database.php';
require '../../vendor/autoload.php';

use ImageKit\ImageKit;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->property_id) && !empty($data->image_base64)) {
    $imageKit = new ImageKit(
        "public_AZmnrhDIQ8iCcEFT3HRNZI93Pcw=",
        "private_EYWHOoJeYJtGslSbcjeaGCpwRD8=",
        "https://ik.imagekit.io/homy"
    );

    // Convert base64 to image
    $uploadFile = $imageKit->uploadFile([
        'file' => $data->image_base64,
        'fileName' => 'property_image_' . time()
    ]);

    error_log(json_encode($uploadFile));

    if (!empty($uploadFile->result)) {
        $image_url = $uploadFile->result->url;
        $query = "INSERT INTO property_images (property_id, image_url) VALUES (:property_id, :image_url)";
        $stmt = $pdo->prepare($query);
    
        $stmt->bindParam(':property_id', $data->property_id);
        $stmt->bindParam(':image_url', $image_url);
    
        try {
            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Image successfully added.",
                    "image" => array(
                        "property_id" => $data->property_id,
                        "url" => $image_url
                    )
                ));
            } else {
                throw new Exception("Unable to save the image to the database.");
            }
        } catch (PDOException $e) {
            http_response_code(503);
            echo json_encode(array("message" => "Database error occurred.", "error" => $e->getMessage()));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage()));
        }
    } elseif (!empty($uploadFile->error)) {
        http_response_code(400);
        echo json_encode(array(
            "message" => "Image upload failed.",
            "error" => $uploadFile->error->message
        ));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unknown error occurred during image upload."));
    }
    
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required data."));
}
