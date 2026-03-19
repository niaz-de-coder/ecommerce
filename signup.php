<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ecommerce");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password']; // Note: Use password_hash() for real security

    $sql = "INSERT INTO users (full_name, email, phone_number, address, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $address, $password);

    if ($stmt->execute()) {
        // Carry data to session and redirect
        $last_id = $conn->insert_id;
        $res = $conn->query("SELECT * FROM users WHERE id = $last_id");
        $_SESSION['user'] = $res->fetch_assoc();
        header("Location: main.php");
    } else {
        echo "<script>alert('Registration failed. Email or Phone may already exist.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account | Niaz De Coder Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10">

    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-lg border border-gray-100">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tighter">Join Niaz De Coder Shop</h1>
            <p class="text-gray-500 mt-2">Start your premium shopping journey today.</p>
        </div>

        <form id="signupForm" action="" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Full Name</label>
                    <input type="text" name="full_name" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-black transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-black transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">BD Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="01XXXXXXXXX" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-black transition">
                <p id="phoneError" class="text-red-500 text-xs mt-1 hidden">Please enter a valid BD number (11 digits starting with 01).</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Address</label>
                <textarea name="address" rows="2" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-black transition"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none focus:border-black transition">
            </div>

            <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition mt-4">Create Account</button>
        </form>
        
        <p class="text-center mt-6 text-sm text-gray-500">
            Already have an account? <a href="signin.php" class="text-black font-bold underline">Log In</a>
        </p>
    </div>

    <script>
        document.getElementById('signupForm').onsubmit = function(e) {
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^(?:\+88|88)?(01[3-9]\d{8})$/; // Bangladeshi regex
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                document.getElementById('phoneError').classList.remove('hidden');
                return false;
            }
        };
    </script>
</body>
</html>