<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin.php"); exit(); }

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

// Handle Status Updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_delivery_status = $_POST['delivery_status'];
    $new_payment_status = $_POST['payment_status'];

    // Fetch current order details to check if we need to revert stock
    $order_query = $conn->query("SELECT product_id, product_quantity, delivery_status FROM orders WHERE id = $order_id");
    $order_data = $order_query->fetch_assoc();

    // Logic: If status changes TO 'Cancelled' from something else, revert stock
    if ($new_delivery_status == 'Cancelled' && $order_data['delivery_status'] != 'Cancelled') {
        $p_id = $order_data['product_id'];
        $qty = $order_data['product_quantity'];
        
        // Subtract from sold, Add back to available
        $conn->query("UPDATE products SET sold_quantity = sold_quantity - $qty, quantity_available = quantity_available + $qty WHERE id = $p_id");
    }

    $sql = "UPDATE orders SET delivery_status = ?, payment_status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_delivery_status, $new_payment_status, $order_id);
    $stmt->execute();
    $message = "Order #$order_id updated successfully.";
}

$orders = $conn->query("SELECT o.*, p.name as product_name, p.image_url FROM orders o JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | Niaz De Coder Shop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .order-card { transition: all 0.3s ease; border-radius: 20px; }
        .badge-pending { @apply bg-orange-100 text-orange-600; }
        .badge-delivered { @apply bg-green-100 text-green-600; }
        .badge-cancelled { @apply bg-red-100 text-red-600; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col">
        <div class="p-8 text-white font-black text-2xl tracking-tighter border-b border-slate-800">Niaz De Coder Shop</div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
            <a href="admin_dash_orders.php" class="flex items-center space-x-3 p-3 rounded-xl bg-indigo-600 text-white"><i class="fa-solid fa-truck-fast"></i> <span>Orders</span></a>
            <a href="admin_dash_edit.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-boxes-stacked"></i> <span>Inventory</span></a>
        </nav>
    </aside>

    <main class="flex-grow p-8">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Order Management</h1>
                <p class="text-slate-500">Track shipments and update payment statuses.</p>
            </div>
            <button onclick="location.reload()" class="p-3 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                <i class="fa-solid fa-rotate text-slate-500"></i>
            </button>
        </header>

        <div class="space-y-4">
            <?php while($row = $orders->fetch_assoc()): ?>
            <div class="bg-white p-6 order-card border border-slate-100 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <div class="flex items-center space-x-4 flex-1">
                    <img src="<?php echo $row['image_url']; ?>" class="w-16 h-16 rounded-2xl object-cover shadow-sm">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-1">Order #<?php echo $row['id']; ?></div>
                        <h3 class="font-bold text-slate-800"><?php echo $row['product_name']; ?> (x<?php echo $row['product_quantity']; ?>)</h3>
                        <p class="text-sm text-slate-500"><i class="fa-regular fa-envelope mr-1"></i> <?php echo $row['user_email']; ?></p>
                    </div>
                </div>

                <div class="flex-1 border-l border-slate-50 pl-6 hidden xl:block">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Shipping To</p>
                    <p class="text-sm text-slate-600 truncate w-48"><?php echo $row['address']; ?></p>
                    <p class="text-xs text-slate-400 mt-1 font-semibold"><?php echo $row['phone_number']; ?></p>
                </div>

                <div class="flex-1 text-center lg:text-left">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Paid</p>
                    <span class="text-lg font-black text-slate-800">$<?php echo number_format($row['total_price'], 2); ?></span>
                    <p class="text-[10px] text-slate-400 font-bold"><?php echo $row['payment_method']; ?></p>
                </div>

                <form action="" method="POST" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                    
                    <select name="delivery_status" class="text-xs font-bold p-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option <?php if($row['delivery_status']=='Pending') echo 'selected'; ?>>Pending</option>
                        <option <?php if($row['delivery_status']=='Processing') echo 'selected'; ?>>Processing</option>
                        <option <?php if($row['delivery_status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                        <option <?php if($row['delivery_status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                        <option <?php if($row['delivery_status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>

                    <select name="payment_status" class="text-xs font-bold p-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option <?php if($row['payment_status']=='Unpaid') echo 'selected'; ?>>Unpaid</option>
                        <option <?php if($row['payment_status']=='Paid') echo 'selected'; ?>>Paid</option>
                        <option <?php if($row['payment_status']=='Refunded') echo 'selected'; ?>>Refunded</option>
                    </select>

                    <button type="submit" name="update_status" class="bg-slate-900 text-white p-2 px-4 rounded-lg text-xs font-bold hover:bg-indigo-600 transition">
                        Update
                    </button>
                </form>

            </div>
            <?php endwhile; ?>
        </div>
    </main>

</body>
</html>