<?php
$conn = new mysqli("localhost", "root", "", "ecommerce");
$q = $_GET['q'] ?? '';

$sql = "SELECT * FROM products WHERE name LIKE '%$q%' OR description LIKE '%$q%' OR tags LIKE '%$q%'";
$result = $conn->query($sql);

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
?>