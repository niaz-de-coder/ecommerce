<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin.php"); exit(); }

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

$message = "";

// Handle Adding to Special Table
if (isset($_POST['add_special'])) {
    $p_id = $_POST['product_id'];
    $status = $_POST['status'];

    // Check if product is already in special with that status
    $check = $conn->prepare("SELECT id FROM special WHERE product_id = ? AND status = ?");
    $check->bind_param("is", $p_id, $status);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $message = "<div class='bg-amber-100 text-amber-700 p-4 rounded-xl mb-6'>This product is already listed under $status.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO special (product_id, status) VALUES (?, ?)");
        $stmt->bind_param("is", $p_id, $status);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6'>Product promoted to Special successfully!</div>";
        }
    }
}

// Handle Removal from Special Table
if (isset($_GET['remove'])) {
    $s_id = $_GET['remove'];
    $conn->query("DELETE FROM special WHERE id = $s_id");
    header("Location: admin_dash_spec.php");
}

// Fetch all special products
$specials = $conn->query("SELECT s.id as spec_id, s.status, p.name, p.image_url, p.product_uid 
                          FROM special s JOIN products p ON s.product_id = p.id");

// Fetch all available products for the dropdown
$all_products = $conn->query("SELECT id, name, product_uid FROM products ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Special Collections | Niaz De Coder Shop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card { background: white; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .status-badge-trending { @apply bg-rose-100 text-rose-600; }
        .status-badge-upcoming { @apply bg-blue-100 text-blue-600; }
        .status-badge-discount { @apply bg-emerald-100 text-emerald-600; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col shadow-2xl">
        <div class="p-8 text-white font-black text-2xl tracking-tighter border-b border-slate-800">Niaz De Coder Shop</div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
            <a href="admin_dash_add.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-plus"></i> <span>Add Item</span></a>
            <a href="admin_dash_edit.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-pen"></i> <span>Edit Item</span></a>
            <a href="admin_dash_spec.php" class="flex items-center space-x-3 p-3 rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/20"><i class="fa-solid fa-star"></i> <span>Special Product</span></a>
        </nav>
    </aside>

    <main class="flex-grow p-8 lg:p-12">
        <div class="max-w-5xl mx-auto">
            <header class="mb-10 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Special Collections</h1>
                    <p class="text-slate-500">Highlight items as Trending, Upcoming, or Discounted.</p>
                </div>
            </header>

            <?php echo $message; ?>

            <div class="glass-card p-8 mb-10">
                <h2 class="text-sm font-black uppercase text-slate-400 tracking-widest mb-6">Promote a Product</h2>
                <form action="" method="POST" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-grow">
                        <select name="product_id" required class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-amber-500 appearance-none bg-slate-50">
                            <option value="" disabled selected>Select a product to promote...</option>
                            <?php while($p = $all_products->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?> (<?php echo $p['product_uid']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="w-full md:w-48">
                        <select name="status" class="w-full p-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50">
                            <option value="trending">Trending</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="discount">Discount</option>
                        </select>
                    </div>
                    <button type="submit" name="add_special" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-amber-600 transition shadow-lg">
                        Promote
                    </button>
                </form>
            </div>

            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="font-bold text-slate-700">Currently Featured</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if($specials->num_rows > 0): ?>
                        <?php while($row = $specials->fetch_assoc()): ?>
                        <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition">
                            <div class="flex items-center space-x-4">
                                <img src="<?php echo $row['image_url']; ?>" class="w-14 h-14 rounded-2xl object-cover shadow-sm">
                                <div>
                                    <h4 class="font-bold text-slate-800"><?php echo $row['name']; ?></h4>
                                    <p class="text-xs text-slate-400 font-medium"><?php echo $row['product_uid']; ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-6">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest status-badge-<?php echo $row['status']; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                                <a href="?remove=<?php echo $row['spec_id']; ?>" onclick="return confirm('Remove this from special collection?')" class="text-slate-300 hover:text-red-500 transition">
                                    <i class="fa-solid fa-circle-xmark text-xl"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-20 text-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-4xl text-slate-200 mb-4"></i>
                            <p class="text-slate-400 font-medium">No special products curated yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>