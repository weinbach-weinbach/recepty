<?php
header('Content-Type: application/json');
include('db.php');

// Decode JSON data
$data = json_decode(file_get_contents('php://input'), true);
$recipeId = intval($data['recipeId']);
$stars = intval($data['stars']);
$ipAddress = $_SERVER['REMOTE_ADDR']; // Optional: track IP address for additional validation

// Fetch current rating data
$sql = "SELECT rating_count, rating_total FROM recipes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $recipeId);
$stmt->execute();
$stmt->bind_result($ratingCount, $ratingTotal);
$stmt->fetch();
$stmt->close();

// Update the rating data
$ratingCount += 1;
$ratingTotal += $stars;
$averageRating = $ratingTotal / $ratingCount;

// Update the recipe with the new rating data and average rating
$sql = "UPDATE recipes SET rating_count = ?, rating_total = ?, average_rating = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iidi', $ratingCount, $ratingTotal, $averageRating, $recipeId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save your rating']);
}

$stmt->close();
$conn->close();
?>
