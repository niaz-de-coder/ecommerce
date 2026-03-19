<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin.php"); exit(); }

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

$message = "";

// Handle Update Request
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $qty = $_POST['quantity_available'];
    $cat = $_POST['category'];
    $desc = $_POST['description'];

    // Handle Optional Image Re-upload
    if (!empty($_FILES["product_image"]["name"])) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
        $new_file_name = time() . "_edit_" . $id . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $conn->query("UPDATE products SET image_url = '$target_file' WHERE id = $id");
        }
    }

    $sql = "UPDATE products SET name=?, price=?, quantity_available=?, category=?, description=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdissi", $name, $price, $qty, $cat, $desc, $id);
    
    if ($stmt->execute()) {
        $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6'>Product updated successfully!</div>";
    }
}

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Optional: Delete physical file from uploads/ here if needed
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin_dash_edit.php");
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Inventory | Niaz De Coder Shop Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .table-card { border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col">
        <div class="p-8 text-white font-black text-2xl tracking-tighter">Niaz De Coder Shop</div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
            <a href="admin_dash_add.php" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-800 transition"><i class="fa-solid fa-plus"></i> <span>Add Item</span></a>
            <a href="admin_dash_edit.php" class="flex items-center space-x-3 p-3 rounded-xl bg-indigo-600 text-white"><i class="fa-solid fa-pen"></i> <span>Edit Inventory</span></a>
        </nav>
    </aside>

    <main class="flex-grow p-8">
        <div class="max-w-6xl mx-auto">
            <header class="mb-10">
                <h1 class="text-3xl font-black text-slate-800">Inventory Management</h1>
                <p class="text-slate-500">Update stock levels, pricing, and product details.</p>
            </header>

            <?php echo $message; ?>

            <div class="bg-white table-card">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">Product</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">UID</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">Category</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">Price</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">Stock</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($row = $products->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4">
                                <div class="flex items-center space-x-3">
                                    <img src="<?php echo $row['image_url']; ?>" class="w-10 h-10 rounded-lg object-cover">
                                    <span class="font-semibold text-slate-700"><?php echo $row['name']; ?></span>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500 text-sm"><?php echo $row['product_uid']; ?></td>
                            <td class="p-4"><span class="px-2 py-1 bg-slate-100 rounded text-xs"><?php echo $row['category']; ?></span></td>
                            <td class="p-4 font-bold text-slate-700">BDT <?php echo $row['price']; ?></td>
                            <td class="p-4 <?php echo ($row['quantity_available'] < 5) ? 'text-red-500 font-bold' : 'text-slate-500'; ?>">
                                <?php echo $row['quantity_available']; ?>
                            </td>
                            <td class="p-4">
                                <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fa-solid fa-pen-to-square"></i></button>
                                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')" class="text-red-400 hover:text-red-600"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3xl border-none p-4">
                <div class="modal-header border-none">
                    <h5 class="font-black text-xl">Edit Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" class="modal-body space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <label class="text-xs font-bold uppercase text-slate-400 block mb-2">Change Image (Leave empty to keep current)</label>
                        <input type="file" name="product_image" accept="image/*" class="text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Product Name</label>
                            <input type="text" name="name" id="edit_name" class="w-full p-3 bg-slate-50 rounded-xl border-none outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Category</label>
                            <input type="text" name="category" id="edit_category" class="w-full p-3 bg-slate-50 rounded-xl border-none outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Price (BDT )</label>
                            <input type="number" step="0.01" name="price" id="edit_price" class="w-full p-3 bg-slate-50 rounded-xl border-none outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-400">Available Stock</label>
                            <input type="number" name="quantity_available" id="edit_qty" class="w-full p-3 bg-slate-50 rounded-xl border-none outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-slate-400">Description</label>
                        <textarea name="description" id="edit_desc" rows="3" class="w-full p-3 bg-slate-50 rounded-xl border-none outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <button type="submit" name="update_product" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold mt-4 shadow-lg shadow-indigo-200">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_qty').value = product.quantity_available;
            document.getElementById('edit_desc').value = product.description;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>