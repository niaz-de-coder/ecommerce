<?php
session_start();

// Security Check: Redirect to signin if no session exists
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$user_data = $_SESSION['user'];
$full_name = $user_data['full_name'];
$email = $user_data['email'];
$phone = $user_data['phone_number'];
$address = $user_data['address'];
$joined = isset($user_data['created_at']) ? date('M Y', strtotime($user_data['created_at'])) : "N/A";

// Database Connection for dynamic stats
$conn = new mysqli("localhost", "root", "", "ecommerce");

// Fetch counts for the badges
$pending_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_email = '$email' AND delivery_status = 'Pending'")->fetch_assoc()['count'];
$history_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_email = '$email' AND delivery_status = 'Delivered'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Zenith Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .glass-card { background: white; border: 1px solid #f1f5f9; border-radius: 2rem; }
        .action-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .action-card:hover { transform: translateY(-5px); @apply shadow-xl shadow-indigo-100; }
        .profile-gradient { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    </style>
</head>
<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col">
        <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                <a href="main.php" class="text-2xl font-black tracking-tighter text-indigo-600">ZENITH.</a>
                <div class="flex items-center space-x-6">
                    <a href="main.php" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition">Shop</a>
                    <a href="logout.php" class="px-5 py-2.5 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition">
                        <i class="fa-solid fa-power-off mr-2"></i>Sign Out
                    </a>
                </div>
            </div>
        </nav>

        <main class="flex-grow max-w-5xl mx-auto w-full px-6 py-12">
            
            <div class="glass-card overflow-hidden mb-10 shadow-sm">
                <div class="profile-gradient h-32 w-full"></div>
                <div class="px-8 pb-8">
                    <div class="relative flex justify-between items-end -mt-12">
                        <div class="w-24 h-24 rounded-3xl bg-white p-1 shadow-lg">
                            <div class="w-full h-full rounded-2xl bg-slate-100 flex items-center justify-center text-3xl font-bold text-indigo-600 uppercase">
                                <?php echo substr($full_name, 0, 1); ?>
                            </div>
                        </div>
                        <div class="pb-2">
                            <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black uppercase tracking-widest">Verified Member</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h1 class="text-3xl font-extrabold text-slate-900"><?php echo $full_name; ?></h1>
                        <p class="text-slate-500 font-medium flex items-center mt-1">
                            <i class="fa-regular fa-envelope mr-2"></i> <?php echo $email; ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10 pt-10 border-t border-slate-50">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Phone Number</p>
                            <p class="font-bold text-slate-800"><?php echo $phone; ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Shipping Address</p>
                            <p class="font-bold text-slate-800"><?php echo $address; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-6 ml-2">Activity Center</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <a href="order_pending.php" class="glass-card p-8 action-card group no-underline">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                    </div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-slate-900 font-bold text-lg mb-1">Pending Orders</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Track items that are currently being processed.</p>
                        </div>
                        <?php if($pending_count > 0): ?>
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2.5 py-1 rounded-lg"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <a href="order_history.php" class="glass-card p-8 action-card group no-underline">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-slate-900 font-bold text-lg mb-1">Order History</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">View your past successful purchases and receipts.</p>
                        </div>
                    </div>
                </a>

                <a href="customer_care.php" class="glass-card p-8 action-card group no-underline md:col-span-1">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-headset text-xl"></i>
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg mb-1">Customer Care</h3>
                    <p class="text-slate-500 text-xs leading-relaxed">Need help? Chat with our support team 24/7.</p>
                </a>

            </div>

            <div class="mt-12 p-6 bg-indigo-600 rounded-3xl flex items-center justify-between shadow-lg shadow-indigo-100">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-lightbulb text-white"></i>
                    </div>
                    <p class="text-white text-sm font-medium">Keep your shipping address updated for faster deliveries!</p>
                </div>
                <button class="text-xs font-bold text-white uppercase tracking-widest border border-white/30 px-4 py-2 rounded-xl hover:bg-white hover:text-indigo-600 transition">Update Profile</button>
            </div>

        </main>
        
        <footer class="py-8 text-center text-slate-400 text-xs font-medium">
            &copy; <?php echo date('Y'); ?> Zenith E-commerce Solutions. All rights reserved.
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>