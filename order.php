<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: signin.php"); exit(); }

$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);
$user_data = $_SESSION['user'];
$user_email = $user_data['email'];

// Fetch Cart Items
$sql = "SELECT c.product_id, p.name, p.price, p.image_url, COUNT(c.product_id) as qty 
        FROM cart c JOIN products p ON c.product_id = p.id 
        WHERE c.user_email = ? GROUP BY c.product_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Costs Logic
$delivery_fee = 60.00; 
$vat_rate = 0.05;      
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Niaz De Coder Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000; color: white; }
        input, select, textarea { background: #111 !important; border: 1px solid #222 !important; color: white !important; }
        .qty-btn { @apply w-8 h-8 flex items-center justify-center bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors; }
    </style>
</head>
<body class="p-4 md:p-10">

<div class="max-w-7xl mx-auto">
    <header class="mb-12">
        <h1 class="text-4xl font-black tracking-tighter">CHECKOUT</h1>
        <p class="text-gray-500 uppercase text-xs tracking-[0.2em] mt-2">Finalize your order and details</p>
    </header>

    <form action="process_order.php" method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <div class="lg:col-span-2 space-y-10">
                <section>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                        <span class="w-6 h-6 rounded-full bg-white text-black flex items-center justify-center text-[10px] mr-3">01</span>
                        Delivery Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="full_name" value="<?php echo $user_data['full_name']; ?>" placeholder="Full Name" required class="p-4 rounded-2xl outline-none">
                        <input type="text" name="phone" value="<?php echo $user_data['phone_number']; ?>" placeholder="Phone Number" required class="p-4 rounded-2xl outline-none">
                        <textarea name="address" placeholder="Complete Delivery Address" required class="p-4 rounded-2xl outline-none md:col-span-2 h-32"><?php echo $user_data['address']; ?></textarea>
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                        <span class="w-6 h-6 rounded-full bg-white text-black flex items-center justify-center text-[10px] mr-3">02</span>
                        Payment Method
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="COD" checked class="hidden peer">
                            <div class="p-4 border-2 border-gray-800 rounded-2xl peer-checked:border-white peer-checked:bg-white peer-checked:text-black transition text-center font-bold text-sm">
                                Cash on Delivery
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="bKash" class="hidden peer">
                            <div class="p-4 border-2 border-gray-800 rounded-2xl peer-checked:border-white peer-checked:bg-white peer-checked:text-black transition text-center font-bold text-sm">
                                bKash / Nagad
                            </div>
                        </label>
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                        <span class="w-6 h-6 rounded-full bg-white text-black flex items-center justify-center text-[10px] mr-3">03</span>
                        Review Items
                    </h2>
                    <div class="space-y-4" id="cart-items-container">
                        <?php 
                        $subtotal = 0;
                        while($item = $result->fetch_assoc()): 
                            $item_price = (float)$item['price'];
                            $item_qty = (int)$item['qty'];
                            $item_total = $item_price * $item_qty;
                            $subtotal += $item_total;
                        ?>
                        <div class="flex items-center justify-between bg-white/5 p-4 rounded-2xl cart-item" data-price="<?php echo $item_price; ?>">
                            <div class="flex items-center space-x-4">
                                <img src="<?php echo $item['image_url']; ?>" class="w-16 h-16 rounded-xl object-cover">
                                <div>
                                    <h4 class="font-bold text-sm"><?php echo $item['name']; ?></h4>
                                    <p class="text-xs text-gray-500">$<?php echo number_format($item_price, 2); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center space-x-3 bg-black/40 p-1 rounded-xl border border-white/10">
                                    <button type="button" onclick="updateQty(this, -1)" class="qty-btn text-lg">-</button>
                                    <input type="number" name="prod_qtys[]" value="<?php echo $item_qty; ?>" readonly 
                                           class="qty-input w-8 text-center bg-transparent border-none font-bold text-sm focus:ring-0">
                                    <button type="button" onclick="updateQty(this, 1)" class="qty-btn text-lg">+</button>
                                </div>
                                <span class="font-black text-sm w-20 text-right item-row-total">
                                    $<?php echo number_format($item_total, 2); ?>
                                </span>
                            </div>

                            <input type="hidden" name="prod_ids[]" value="<?php echo $item['product_id']; ?>">
                            <input type="hidden" name="prod_prices[]" value="<?php echo $item_price; ?>">
                        </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-10 bg-white/5 p-8 rounded-3xl border border-white/10">
                    <h3 class="text-lg font-black mb-8">Order Summary</h3>
                    
                    <div class="space-y-4 text-sm mb-8">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span id="display-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Delivery Fee</span>
                            <span>$<?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>VAT (5%)</span>
                            <span id="display-vat">$<?php $vat = $subtotal * $vat_rate; echo number_format($vat, 2); ?></span>
                        </div>
                        <hr class="border-white/10">
                        <div class="flex justify-between text-xl font-black">
                            <span>Total</span>
                            <span id="display-total">$<?php $total = $subtotal + $delivery_fee + $vat; echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="total_price" id="hidden-total" value="<?php echo $total; ?>">
                    <input type="hidden" name="vat" id="hidden-vat" value="<?php echo $vat; ?>">
                    <input type="hidden" name="delivery_price" value="<?php echo $delivery_fee; ?>">

                    <button type="submit" class="w-full bg-white text-black py-4 rounded-2xl font-black hover:bg-gray-200 transition-all active:scale-95">
                        CONFIRM ORDER
                    </button>
                    <p class="text-[9px] text-center text-gray-600 mt-6 uppercase tracking-widest font-bold">Secure Gateway Integration</p>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
const VAT_RATE = 0.05;
const DELIVERY_FEE = 60.00;

function updateQty(btn, change) {
    const row = btn.closest('.cart-item');
    const input = row.querySelector('.qty-input');
    const price = parseFloat(row.dataset.price);
    const rowTotalDisplay = row.querySelector('.item-row-total');
    
    let currentQty = parseInt(input.value);
    let newQty = currentQty + change;
    
    if (newQty < 1) return; // Minimum 1 item
    
    input.value = newQty;
    
    // Update individual row total
    const newRowTotal = price * newQty;
    rowTotalDisplay.textContent = '$' + newRowTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    recalculateGrandTotals();
}

function recalculateGrandTotals() {
    let newSubtotal = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
        const price = parseFloat(item.dataset.price);
        const qty = parseInt(item.querySelector('.qty-input').value);
        newSubtotal += price * qty;
    });

    const newVat = newSubtotal * VAT_RATE;
    const newTotal = newSubtotal + newVat + DELIVERY_FEE;

    // Update Text Displays
    document.getElementById('display-subtotal').textContent = '$' + newSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('display-vat').textContent = '$' + newVat.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('display-total').textContent = '$' + newTotal.toLocaleString(undefined, {minimumFractionDigits: 2});

    // Update Form Inputs
    document.getElementById('hidden-total').value = newTotal.toFixed(2);
    document.getElementById('hidden-vat').value = newVat.toFixed(2);
}
</script>

</body>
</html>