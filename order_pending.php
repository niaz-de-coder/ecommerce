<?php
session_start();

// Security Check
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);
$user_email = $_SESSION['user']['email'];

$message = "";

// Handle Delete Request
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // Safety Check: Double check if status is still 'Pending' before deleting
    $check = $conn->query("SELECT delivery_status FROM orders WHERE id = $order_id AND user_email = '$user_email'");
    $row = $check->fetch_assoc();
    
    if ($row && $row['delivery_status'] === 'Pending') {
        $del = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $del->bind_param("i", $order_id);
        if ($del->execute()) {
            $message = "<div class='bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-6 text-sm font-bold'>Order #$order_id has been cancelled successfully.</div>";
        }
    } else {
        $message = "<div class='bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 text-sm font-bold'>Cannot cancel. Order is already being processed.</div>";
    }
}

// Fetch Pending and Processing orders
$sql = "SELECT o.*, p.name, p.image_url 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.user_email = ? AND o.delivery_status IN ('Pending', 'Processing')
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Orders | Zenith</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .order-card { background: white; border: 1px solid #f1f5f9; border-radius: 1.5rem; transition: all 0.3s ease; }
        .order-card:hover { transform: translateY(-3px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.05); }
    </style>
</head>
<body>

<div class="min-h-screen p-6 md:p-12">
    <div class="max-w-6xl mx-auto">
        <header class="mb-10 flex items-center justify-between">
            <div>
                <a href="dashboard.php" class="text-indigo-600 font-bold text-sm flex items-center mb-2 hover:underline">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Active Trackings</h1>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm text-xs font-bold text-slate-500">
                TOTAL ACTIVE: <?php echo $result->num_rows; ?>
            </div>
        </header>

        <?php echo $message; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($order = $result->fetch_assoc()): ?>
                    <div class="order-card p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Order #<?php echo $order['id']; ?></span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo $order['delivery_status'] == 'Pending' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'; ?>">
                                    <?php echo $order['delivery_status']; ?>
                                </span>
                            </div>

                            <div class="flex items-center space-x-4 mb-6">
                                <img src="<?php echo $order['image_url']; ?>" class="w-16 h-16 rounded-2xl object-cover bg-slate-50">
                                <div>
                                    <h3 class="font-bold text-slate-800 leading-tight"><?php echo $order['name']; ?></h3>
                                    <p class="text-xs text-slate-500 mt-1">Qty: <?php echo $order['product_quantity']; ?> • $<?php echo number_format($order['product_price'], 2); ?></p>
                                </div>
                            </div>

                            <div class="space-y-2 border-t border-slate-50 pt-4 mb-6">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-400">Total Amount</span>
                                    <span class="font-bold text-slate-900">$<?php echo number_format($order['total_price'], 2); ?></span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-400">Date</span>
                                    <span class="text-slate-600"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <?php if($order['delivery_status'] === 'Pending'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="cancel_order" class="w-full py-3 bg-rose-50 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-600 hover:text-white transition">
                                        <i class="fa-solid fa-trash-can mr-2"></i> Cancel Order
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="w-full py-3 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black text-center uppercase tracking-widest cursor-not-allowed">
                                    Currently Processing
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-[3rem] border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-box-open text-slate-300 text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">No active orders found</h2>
                <p class="text-slate-500 text-sm mt-2">Looks like you haven't placed any orders recently.</p>
                <a href="main.php" class="mt-8 inline-block px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-100">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>