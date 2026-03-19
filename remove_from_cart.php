<?php
session_start();
if (!isset($_SESSION['user'])) { die("Unauthorized"); }

$conn = new mysqli("localhost", "root", "", "ecommerce");
$cart_id = $_POST['cart_id'];
$user_email = $_SESSION['user']['email'];

// Ensure users can only delete their own items
$sql = "DELETE FROM cart WHERE id = ? AND user_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $cart_id, $user_email);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error";
}
?>