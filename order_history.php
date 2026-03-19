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

// Fetch Completed and Cancelled orders
$sql = "SELECT o.*, p.name, p.image_url 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.user_email = ? AND o.delivery_status IN ('Delivered', 'Cancelled')
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
    <title>Order History | Zenith</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .history-card { background: white; border: 1px solid #f1f5f9; border-radius: 1.5rem; }
        .status-delivered { @apply bg-emerald-50 text-emerald-600; }
        .status-cancelled { @apply bg-slate-100 text-slate-500; }
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
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Order History</h1>
                <p class="text-slate-500 text-sm mt-1">Review your past purchases and cancelled requests.</p>
            </div>
        </header>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($order = $result->fetch_assoc()): 
                    $is_delivered = ($order['delivery_status'] === 'Delivered');
                ?>
                    <div class="history-card p-6 flex flex-col <?php echo !$is_delivered ? 'opacity-75' : ''; ?>">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ref: #<?php echo $order['id']; ?></span>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?php echo $is_delivered ? 'status-delivered' : 'status-cancelled'; ?>">
                                <i class="fa-solid <?php echo $is_delivered ? 'fa-check-double' : 'fa-xmark'; ?> mr-1"></i>
                                <?php echo $order['delivery_status']; ?>
                            </span>
                        </div>

                        <div class="flex items-center space-x-4 mb-6">
                            <img src="<?php echo $order['image_url']; ?>" class="w-16 h-16 rounded-2xl object-cover bg-slate-50 border border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-800 leading-tight"><?php echo $order['name']; ?></h3>
                                <p class="text-xs text-slate-500 mt-1">Qty: <?php echo $order['product_quantity']; ?></p>
                            </div>
                        </div>

                        <div class="mt-auto pt-4 border-t border-slate-50">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Paid Via</p>
                                    <p class="text-xs font-bold text-slate-700"><?php echo $order['payment_method']; ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Total amount</p>
                                    <p class="text-lg font-black text-slate-900">$<?php echo number_format($order['total_price'], 2); ?></p>
                                </div>
                            </div>

                            <div class="flex space-x-2">
                                <button class="flex-grow py-3 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                                    View Invoice
                                </button>
                                <?php if($is_delivered): ?>
                                <a href="main.php" class="p-3 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24 bg-white rounded-[3rem] border border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-clock-rotate-left text-slate-300 text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">No history yet</h2>
                <p class="text-slate-500 text-sm mt-2 max-w-xs mx-auto">Your completed or cancelled orders will appear here once processed.</p>
                <a href="main.php" class="mt-8 inline-block px-10 py-4 bg-slate-900 text-white rounded-2xl font-bold text-sm shadow-xl shadow-slate-200">Go Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>