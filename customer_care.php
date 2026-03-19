<?php
session_start();

// Security Check
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$user_data = $_SESSION['user'];
$message_status = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // In a real scenario, you would insert this into a 'support_tickets' table
    // For now, we simulate a success response
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $message_status = "<div class='bg-emerald-50 text-emerald-600 p-6 rounded-2xl mb-8 border border-emerald-100 flex items-center shadow-sm'>
                        <i class='fa-solid fa-circle-check text-2xl mr-4'></i>
                        <div>
                            <p class='font-bold'>Message Received!</p>
                            <p class='text-xs opacity-80'>Our support team will contact you at {$user_data['email']} within 24 hours.</p>
                        </div>
                      </div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Care | Zenith</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .support-card { background: white; border: 1px solid #f1f5f9; border-radius: 2rem; transition: all 0.3s ease; }
        .input-field { @apply w-full p-4 bg-slate-50 rounded-2xl border border-slate-100 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all; }
    </style>
</head>
<body>

<div class="min-h-screen py-12 px-6">
    <div class="max-w-5xl mx-auto">
        
        <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <a href="dashboard.php" class="text-indigo-600 font-bold text-sm flex items-center mb-2 hover:underline">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">How can we help?</h1>
                <p class="text-slate-500 mt-2">We’re here to ensure your experience is seamless.</p>
            </div>
            <div class="flex -space-x-3">
                <img src="https://ui-avatars.com/api/?name=Support+1&background=6366f1&color=fff" class="w-12 h-12 rounded-full border-4 border-white shadow-sm">
                <img src="https://ui-avatars.com/api/?name=Support+2&background=f43f5e&color=fff" class="w-12 h-12 rounded-full border-4 border-white shadow-sm">
                <div class="w-12 h-12 rounded-full bg-slate-900 border-4 border-white flex items-center justify-center text-white text-[10px] font-bold shadow-sm">+5</div>
            </div>
        </header>

        <?php echo $message_status; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 support-card p-8 md:p-10 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-8">Send a Message</h2>
                
                <form action="customer_care.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Full Name</label>
                            <input type="text" value="<?php echo $user_data['full_name']; ?>" readonly class="input-field text-slate-500 cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Order ID (Optional)</label>
                            <input type="text" name="order_id" placeholder="#0000" class="input-field">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Inquiry Reason</label>
                        <select name="subject" required class="input-field appearance-none">
                            <option value="">Select a reason...</option>
                            <option value="Tracking">Order Tracking</option>
                            <option value="Refund">Return & Refund</option>
                            <option value="Payment">Payment Issue</option>
                            <option value="Feedback">Feedback / Suggestions</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Your Message</label>
                        <textarea name="message" rows="5" required placeholder="Describe your issue in detail..." class="input-field"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-sm hover:bg-indigo-600 transition-all shadow-xl shadow-slate-100 active:scale-[0.98]">
                        SEND TICKET <i class="fa-solid fa-paper-plane ml-2"></i>
                    </button>
                </form>
            </div>

            <div class="space-y-6">
                <div class="support-card p-8 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-6">Quick Contacts</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Email Support</p>
                                <p class="text-sm font-bold text-slate-700">support@zenith.com</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">WhatsApp Care</p>
                                <p class="text-sm font-bold text-slate-700">+880 1XXX-XXXXXX</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-100">
                    <h3 class="font-bold text-lg mb-2">Office Hours</h3>
                    <p class="text-indigo-100 text-xs leading-relaxed opacity-80 mb-6">Our team is available for real-time chat during these hours:</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="opacity-70">Sat - Thu</span>
                            <span class="font-bold">10:00 AM - 8:00 PM</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Friday</span>
                            <span class="font-bold">Closed</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 text-center border-2 border-dashed border-slate-200 rounded-[2rem]">
                    <p class="text-slate-400 text-xs font-bold mb-3">Looking for instant answers?</p>
                    <a href="#" class="text-indigo-600 font-black text-xs hover:underline uppercase tracking-widest">Visit FAQ Center</a>
                </div>
            </div>

        </div>

        <footer class="mt-16 text-center text-slate-400 text-[10px] font-bold uppercase tracking-[0.3em]">
            Zenith Protection & Support &copy; <?php echo date('Y'); ?>
        </footer>

    </div>
</div>

</body>
</html>