<?php
session_start();
// Redirect to signin if no session exists
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$user_data = $_SESSION['user'];
$user_email = $user_data['email'];

// Database Connection
$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

function getSpecialProducts($conn, $status) {
    $sql = "SELECT p.* FROM products p JOIN special s ON p.id = s.product_id WHERE s.status = '$status'";
    return $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niaz De Coder Shop | Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        .product-card { transition: transform 0.3s ease; border: none; border-radius: 15px; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="glass-nav sticky top-0 z-50 border-b border-gray-100">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-bag-shopping text-2xl text-indigo-600"></i>
            <span class="text-xl font-bold tracking-tighter">Niaz De Coder Shop</span>
        </div>
        <div class="hidden md:flex space-x-8 font-medium">
            <a href="#" class="hover:text-indigo-600">About</a>
            <a href="#" class="hover:text-indigo-600">Contact</a>
        </div>
        <div class="flex items-center space-x-5 text-lg">
            <a href="cart.php" class="relative">
                <i class="fa-solid fa-cart-shopping hover:text-indigo-600"></i>
            </a>
            <a href="dashboard.php" class="flex items-center space-x-2">
                <i class="fa-regular fa-user hover:text-indigo-600"></i>
                <span class="text-xs font-bold hidden md:block"><?php echo $user_data['full_name']; ?></span>
            </a>
        </div>
    </div>
</nav>

<section class="bg-white py-12 border-b border-gray-100">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-3xl font-bold mb-6">Hello, <?php echo explode(' ', $user_data['full_name'])[0]; ?>.</h1>
        <div class="max-w-2xl mx-auto flex shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <input type="text" id="searchInput" placeholder="Search products..." class="w-full px-5 py-3 outline-none">
            <button onclick="searchProducts()" class="bg-black text-white px-8 py-3 font-semibold">Search</button>
        </div>
    </div>
</section>

<main class="container mx-auto px-6 py-10">
    <div id="searchResults" class="hidden mb-10">
        <h2 class="text-2xl font-bold mb-6">Search Results</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8" id="searchGrid"></div>
    </div>

    <?php 
    $sections = ['trending' => 'Trending Now', 'upcoming' => 'Coming Soon', 'discount' => 'Flash Sale'];
    foreach($sections as $status => $title): 
        $result = getSpecialProducts($conn, $status);
    ?>
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-8 flex items-center">
            <span class="w-2 h-8 bg-indigo-600 rounded mr-3"></span> <?php echo $title; ?>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white p-4 product-card border border-gray-100 shadow-sm">
                <img src="<?php echo $row['image_url']; ?>" class="h-48 w-full object-cover rounded-lg mb-4">
                <h3 class="font-bold text-lg"><?php echo $row['name']; ?></h3>
                <div class="flex justify-between items-center mt-3">
                    <span class="font-bold text-indigo-600">BDT <?php echo $row['price']; ?></span>
                    <button onclick='showDetails(<?php echo json_encode($row); ?>)' class="text-sm font-semibold underline">Details</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endforeach; ?>
</main>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2xl border-none p-4">
            <div id="modalBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    async function searchProducts() {
        const query = document.getElementById('searchInput').value;
        const response = await fetch(`search_api.php?q=${query}`);
        const products = await response.json();
        const grid = document.getElementById('searchGrid');
        const resultsDiv = document.getElementById('searchResults');
        grid.innerHTML = '';
        if(products.length > 0) {
            resultsDiv.classList.remove('hidden');
            products.forEach(p => {
                grid.innerHTML += `
                <div class="bg-white p-4 product-card border border-gray-100 shadow-sm">
                    <img src="${p.image_url}" class="h-48 w-full object-cover rounded-lg mb-4">
                    <h3 class="font-bold text-lg">${p.name}</h3>
                    <div class="flex justify-between items-center mt-4">
                        <span class="font-bold text-indigo-600">$${p.price}</span>
                        <button onclick='showDetails(${JSON.stringify(p)})' class="bg-black text-white px-4 py-2 rounded-lg text-xs">View</button>
                    </div>
                </div>`;
            });
        }
    }

    function showDetails(product) {
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = `
            <div class="flex flex-col items-center text-center">
                <img src="${product.image_url}" class="w-full h-64 object-cover rounded-xl mb-6">
                <h2 class="text-2xl font-bold mb-2">${product.name}</h2>
                <p class="text-gray-600 mb-6 text-sm">${product.description}</p>
                <button onclick="addToCart(${product.id})" class="w-full py-4 bg-black text-white font-bold rounded-xl hover:bg-gray-800 transition">
                    Cart this item
                </button>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('productModal')).show();
    }

    function addToCart(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}`
        })
        .then(res => res.text())
        .then(data => {
            alert("Product added to cart!");
        });
    }
</script>
</body>
</html>