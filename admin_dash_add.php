<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_uid      = $_POST['product_uid'];
    $name       = $_POST['name'];
    $price      = $_POST['price'];
    $desc       = $_POST['description'];
    $category   = $_POST['category'];
    $tags       = $_POST['tags'];
    $qty        = $_POST['quantity_available'];

    // Handle Image Upload
    $target_dir = "uploads/";
    // Ensure directory exists
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
    $new_file_name = time() . "_" . $p_uid . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        $img_url = $target_file;
        
        $sql = "INSERT INTO products (product_uid, name, image_url, price, description, category, tags, quantity_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsssi", $p_uid, $name, $img_url, $price, $desc, $category, $tags, $qty);

        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center'><i class='fa-solid fa-circle-check mr-2'></i> Product & Image Added Successfully!</div>";
        } else {
            $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center'><i class='fa-solid fa-circle-xmark mr-2'></i> Database Error: Duplicate UID or Connection Issue.</div>";
        }
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center'><i class='fa-solid fa-image mr-2'></i> Failed to upload image to server.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Niaz De Coder Shop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .input-field { @apply w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-indigo-500 transition-all bg-white; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col">
        <div class="p-8 text-white font-black text-2xl tracking-tighter border-b border-slate-800">Niaz De Coder Shop</div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
            <a href="admin_dash_add.php" class="flex items-center space-x-3 p-3 rounded-xl bg-indigo-600 text-white"><i class="fa-solid fa-plus"></i> <span>Add Item</span></a>
            <a href="admin_dash_edit.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-pen"></i> <span>Edit Item</span></a>
        </nav>
    </aside>

    <main class="flex-grow p-8 lg:p-12">
        <div class="max-w-4xl mx-auto">
            <header class="mb-10 flex justify-between items-end">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Inventory Entry</h1>
                    <p class="text-slate-500">Upload an image and fill details to list a new product.</p>
                </div>
                <a href="admin_dashboard.php" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition">Cancel</a>
            </header>

            <?php echo $message; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-8 lg:p-10 rounded-3xl shadow-sm border border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Product UID</label>
                        <input type="text" name="product_uid" placeholder="e.g. ZNT-9901" required class="input-field">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Product Name</label>
                        <input type="text" name="name" placeholder="Item display name" required class="input-field">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Product Image</label>
                        <input type="file" name="product_image" accept="image/*" required class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Unit Price ($)</label>
                        <input type="number" step="0.01" name="price" placeholder="0.00" required class="input-field">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Category</label>
                        <select name="category" class="input-field">
                            <option>Fashion</option>
                            <option>Electronics</option>
                            <option>Accessories</option>
                            <option>Home Decor</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Initial Stock</label>
                        <input type="number" name="quantity_available" placeholder="0" required class="input-field">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Description</label>
                        <textarea name="description" rows="4" placeholder="Brief product overview..." class="input-field"></textarea>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase text-slate-400 tracking-widest">Tags (Comma Separated)</label>
                        <input type="text" name="tags" placeholder="modern, white, trending" class="input-field">
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-50">
                    <button type="submit" class="w-full md:w-auto px-10 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Confirm & Save Product
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>