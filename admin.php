<?php
session_start();
$host = "localhost"; $user = "root"; $pass = ""; $db = "ecommerce";
$conn = new mysqli($host, $user, $pass, $db);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_user = $_POST['username'];
    $input_pass = $_POST['password'];

    // Using Prepared Statements for security
    $sql = "SELECT * FROM admin WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $input_user, $input_pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $input_user;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Access Denied: Invalid Administrative Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niaz De Coder Shop | Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at top right, #1a1a1a, #000000);
        }
        .admin-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .input-dark {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
        .input-dark:focus {
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="admin-card p-10 rounded-3xl w-full max-w-md shadow-2xl">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 mb-6 shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-shield-halved text-2xl text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tighter uppercase">Admin Access</h1>
            <p class="text-gray-500 text-sm mt-2">Internal Management System v1.0</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl mb-6 text-xs font-bold flex items-center">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">System Username</label>
                <div class="relative">
                    <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" name="username" required placeholder="Enter username"
                           class="input-dark w-full pl-12 pr-4 py-4 rounded-xl outline-none transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Security Key</label>
                <div class="relative">
                    <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="input-dark w-full pl-12 pr-4 py-4 rounded-xl outline-none transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold hover:bg-indigo-500 transition shadow-lg shadow-indigo-600/20 mt-4">
                Authenticate System
            </button>
        </form>

        <div class="mt-10 text-center">
            <a href="index.php" class="text-gray-500 hover:text-white text-xs transition underline underline-offset-4">
                Return to Public Shop
            </a>
        </div>
    </div>

</body>
</html>