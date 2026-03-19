<?php
session_start();
// Security Check: Ensure user is logged in
if (!isset($_SESSION['user'])) { 
    header("Location: signin.php"); 
    exit(); 
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email     = $_SESSION['user']['email'];
    $phone          = $_POST['phone'];
    $address        = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $delivery_price = $_POST['delivery_price'];
    $vat            = $_POST['vat'];
    
    // Arrays from the review section in order.php
    $product_ids    = $_POST['prod_ids']; 
    $product_qtys   = $_POST['prod_qtys'];
    $product_prices = $_POST['prod_prices'];

    // Start a Database Transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        foreach ($product_ids as $key => $prod_id) {
            $qty_ordered = $product_qtys[$key];
            $unit_price  = $product_prices[$key];

            // 1. STOCK VALIDATION: Check if enough stock exists before processing
            $check_stmt = $conn->prepare("SELECT quantity_available FROM products WHERE id = ? FOR UPDATE");
            $check_stmt->bind_param("i", $prod_id);
            $check_stmt->execute();
            $stock_result = $check_stmt->get_result()->fetch_assoc();

            if ($stock_result['quantity_available'] < $qty_ordered) {
                throw new Exception("Stock exhausted for one of your items. Please update your cart.");
            }

            // 2. LOGIC FIX: Calculate the specific total for THIS order row
            // We distribute a portion of VAT and Delivery across items to keep the total accurate per row
            $item_share_vat = $vat / count($product_ids);
            $item_share_del = $delivery_price / count($product_ids);
            $row_total = ($unit_price * $qty_ordered) + $item_share_vat + $item_share_del;

            // 3. INSERT ORDER: Record the purchase
            $sql_order = "INSERT INTO orders (user_email, phone_number, product_id, product_quantity, product_price, address, delivery_price, vat, payment_method, total_price, delivery_status, payment_status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid')";
            $stmt = $conn->prepare($sql_order);
            $stmt->bind_param("ssiidsddsd", $user_email, $phone, $prod_id, $qty_ordered, $unit_price, $address, $item_share_del, $item_share_vat, $payment_method, $row_total);
            $stmt->execute();

            // 4. INVENTORY SUBTRACTION: Update quantity_available and sold_quantity
            $sql_update = "UPDATE products SET sold_quantity = sold_quantity + ?, quantity_available = quantity_available - ? WHERE id = ?";
            $update_stmt = $conn->prepare($sql_update);
            $update_stmt->bind_param("iii", $qty_ordered, $qty_ordered, $prod_id);
            $update_stmt->execute();
        }

        // 5. CLEAR CART: Delete items for this user after successful purchase
        $conn->query("DELETE FROM cart WHERE user_email = '$user_email'");

        // Commit the transaction to save all changes
        $conn->commit();
        echo "<script>alert('Order Placed Successfully!'); window.location.href='main.php';</script>";

    } catch (Exception $e) {
        // If anything fails, undo every database change made during this request
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>