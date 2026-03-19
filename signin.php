<?php
session_start();
$conn = new mysqli("localhost", "root", "", "ecommerce");

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier']; // Email or Phone
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? OR phone_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // In production, use password_verify($password, $user['password'])
        if ($password === $user['password']) { 
            $_SESSION['user'] = $user;
            header("Location: main.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In | Niaz De Coder Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tighter">Welcome Back</h1>
            <p class="text-gray-500 mt-2">Enter your details to access your account</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold mb-2">Email or Phone Number</label>
                <input type="text" name="identifier" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-black outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-black outline-none transition">
            </div>
            <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition shadow-lg">Sign In</button>
        </form>

        <div class="mt-8 text-center border-t pt-6">
            <p class="text-gray-600">New to Niaz De Coder Shop?</p>
            <a href="signup.php" class="inline-block mt-2 text-black font-bold border-b-2 border-black pb-1 hover:text-gray-600 hover:border-gray-600 transition">Create New Account</a>
        </div>
    </div>

</body>
</html>