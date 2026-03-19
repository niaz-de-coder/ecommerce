<?php
session_start();
// Security Check: If not an admin, kick them out
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

// Quick Stats for the Dashboard
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(total_price) as sum FROM orders WHERE payment_status = 'Paid'")->fetch_assoc()['sum'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niaz De Coder Shop Admin | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar { background: #0f172a; transition: all 0.3s ease; }
        .stat-card { transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-3px); }
        .nav-link:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="sidebar w-64 text-slate-300 flex-col hidden md:flex">
        <div class="p-8 text-white font-black text-2xl tracking-tighter border-b border-slate-800">
            Niaz De Coder Shop <span class="text-indigo-500 text-xs block font-normal tracking-widest">CONTROL PANEL</span>
        </div>
        <nav class="flex-grow p-4 space-y-2 mt-4">
            <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-600/20">
                <i class="fa-solid fa-house-chimney w-5"></i> <span>Dashboard</span>
            </a>
            <a href="admin_dash_orders.php" class="nav-link flex items-center space-x-3 p-3 rounded-xl transition">
                <i class="fa-solid fa-truck-fast w-5"></i> <span>Orders</span>
            </a>
            <a href="admin_dash_edit.php" class="nav-link flex items-center space-x-3 p-3 rounded-xl transition">
                <i class="fa-solid fa-boxes-stacked w-5"></i> <span>Inventory</span>
            </a>
            <a href="admin_dash_report.php" class="nav-link flex items-center space-x-3 p-3 rounded-xl transition">
                <i class="fa-solid fa-chart-line w-5"></i> <span>Reports</span>
            </a>
        </nav>
        <div class="p-6 border-t border-slate-800">
            <a href="admin_logout.php" class="flex items-center space-x-3 text-red-400 hover:text-red-300 transition text-sm font-bold">
                <i class="fa-solid fa-power-off"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="flex-grow p-8">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-800">System Overview</h1>
                <p class="text-slate-500">Welcome back, <?php echo $_SESSION['admin_user']; ?>.</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-xs font-bold px-3 py-1 bg-green-100 text-green-700 rounded-full uppercase tracking-tighter">System Online</span>
                <div class="w-10 h-10 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center">
                    <i class="fa-solid fa-user-tie text-slate-600"></i>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm stat-card">
                <div class="text-slate-400 text-xs font-bold uppercase mb-2">Total Revenue</div>
                <div class="text-2xl font-black text-slate-800">$<?php echo number_format($revenue, 2); ?></div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm stat-card">
                <div class="text-slate-400 text-xs font-bold uppercase mb-2">Total Orders</div>
                <div class="text-2xl font-black text-slate-800"><?php echo $total_orders; ?></div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm stat-card">
                <div class="text-slate-400 text-xs font-bold uppercase mb-2">Inventory Items</div>
                <div class="text-2xl font-black text-slate-800"><?php echo $total_products; ?></div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm stat-card">
                <div class="text-slate-400 text-xs font-bold uppercase mb-2">Registered Users</div>
                <div class="text-2xl font-black text-slate-800"><?php echo $total_users; ?></div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-slate-800 mb-6">Management Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <a href="admin_dash_add.php" class="group bg-indigo-600 p-8 rounded-3xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-plus text-white text-xl"></i>
                </div>
                <h3 class="text-white font-bold text-lg">Add New Item</h3>
                <p class="text-indigo-100 text-xs mt-2 opacity-70">Expand your product catalog.</p>
            </a>

            <a href="admin_dash_edit.php" class="group bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-pen-to-square text-slate-600 text-xl"></i>
                </div>
                <h3 class="text-slate-800 font-bold text-lg">Edit Inventory</h3>
                <p class="text-slate-500 text-xs mt-2">Update prices, stock, or details.</p>
            </a>

            <a href="admin_dash_report.php" class="group bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-file-invoice-dollar text-slate-600 text-xl"></i>
                </div>
                <h3 class="text-slate-800 font-bold text-lg">View Reports</h3>
                <p class="text-slate-500 text-xs mt-2">Analyze sales and performance.</p>
            </a>

            <a href="admin_dash_orders.php" class="group bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-list-check text-slate-600 text-xl"></i>
                </div>
                <h3 class="text-slate-800 font-bold text-lg">Manage Orders</h3>
                <p class="text-slate-500 text-xs mt-2">Process pending deliveries.</p>
            </a>
            <a href="admin_dash_spec.php" class="group bg-gradient-to-br from-amber-500 to-orange-600 p-8 rounded-3xl shadow-xl shadow-orange-200 hover:scale-[1.02] transition-transform">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-star text-white text-xl"></i>
                </div>
                <h3 class="text-white font-bold text-lg">Special Product</h3>
                <p class="text-orange-100 text-xs mt-2 opacity-80">Manage featured or limited deals.</p>
            </a>

        </div>
    </main>

</body>
</html>