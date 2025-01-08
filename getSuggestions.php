<?php
include('db.php');

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    
    // Sanitize user input
    $query = '%' . $query . '%';
    
    // Prepare the SQL statement with wildcards
    $stmt = $conn->prepare("SELECT name FROM ingredients WHERE name LIKE ? LIMIT 10");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['name'];
    }

    echo json_encode($suggestions);
}
?>


