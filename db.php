<?php
$servername = "localhost";
$username = "root"; // Change if necessary
$password = "root";     // Change if necessary
$dbname = "recipes";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=recipes;charset=utf8', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;  // Stop further script execution if the connection fails
}
?>