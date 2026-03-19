<?php
session_start();
if (!isset($_SESSION['user'])) { die("Unauthorized"); }

$conn = new mysqli("localhost", "root", "", "ecommerce");
$user_email = $_SESSION['user']['email'];
$product_id = $_POST['product_id'];

$sql = "INSERT INTO cart (user_email, product_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $user_email, $product_id);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error";
}
?>