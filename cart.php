<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);
$user_email = $_SESSION['user']['email'];

// Fetch items joined with product details
$sql = "SELECT c.id as cart_id, p.* FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Niaz De Coder Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .cart-item { transition: all 0.3s ease; }
        .cart-item:hover { background-color: #fff; transform: scale(1.01); }
    </style>
</head>
<body>

<nav class="bg-white border-b border-gray-100 py-4 mb-10">
    <div class="container mx-auto px-6 flex justify-between items-center">
        <a href="main.php" class="text-gray-500 hover:text-black transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Shop
        </a>
        <h1 class="text-xl font-bold tracking-tighter italic">Niaz De Coder Shop CART</h1>
        <div class="w-20"></div> </div>
</nav>

<div class="container mx-auto px-6 max-w-4xl">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-100">
            <h2 class="text-2xl font-bold">Shopping Bag</h2>
        </div>

        <div class="p-0">
            <?php if ($result->num_rows > 0): ?>
                <ul class="divide-y divide-gray-100">
                    <?php while($item = $result->fetch_assoc()): 
                        $total_price += $item['price'];
                    ?>
                    <li class="flex items-center p-6 cart-item" id="item-<?php echo $item['cart_id']; ?>">
                        <img src="<?php echo $item['image_url']; ?>" class="w-20 h-20 object-cover rounded-xl shadow-sm">
                        <div class="ml-6 flex-grow">
                            <h3 class="font-bold text-lg text-gray-800"><?php echo $item['name']; ?></h3>
                            <p class="text-gray-400 text-sm"><?php echo $item['category']; ?></p>
                        </div>
                        <div class="text-right mr-8">
                            <span class="font-bold text-xl">$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <button onclick="removeFromCart(<?php echo $item['cart_id']; ?>)" class="text-red-400 hover:text-red-600 transition p-2">
                            <i class="fa-regular fa-trash-can text-lg"></i>
                        </button>
                    </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="p-20 text-center">
                    <i class="fa-solid fa-cart-ghost text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-400">Your cart is feeling a bit light.</p>
                    <a href="main.php" class="inline-block mt-4 text-black font-bold underline">Go find something!</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <div class="bg-gray-50 p-8 flex flex-col md:flex-row justify-between items-center border-t border-gray-100">
            <div class="mb-4 md:mb-0">
                <span class="text-gray-500 uppercase tracking-widest text-xs font-bold">Total Amount</span>
                <h3 class="text-3xl font-black text-black">$<?php echo number_format($total_price, 2); ?></h3>
            </div>
            <button onclick="window.location.href='order.php'" class="w-full md:w-auto bg-black text-white px-12 py-4 rounded-2xl font-bold hover:bg-gray-800 transition shadow-xl">
                Proceed to Checkout <i class="fa-solid fa-chevron-right ml-2 text-xs"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function removeFromCart(cartId) {
        if(!confirm("Remove this item from your bag?")) return;

        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `cart_id=${cartId}`
        })
        .then(res => res.text())
        .then(data => {
            if(data === "Success") {
                const element = document.getElementById(`item-${cartId}`);
                element.style.opacity = '0';
                setTimeout(() => {
                    element.remove();
                    // Optional: Recalculate total or refresh page
                    location.reload(); 
                }, 300);
            }
        });
    }
</script>

</body>
</html>