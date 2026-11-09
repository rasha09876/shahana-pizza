<?php
// داتا بەیسی SQLite بە یەک فایل (وەک کۆدی یەکەم)
$db_file = 'shahan_restaurant.db';
$db = new PDO('sqlite:' . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// دروستکردنی خشتەکان ئەگەر نەبوون
$db->exec("CREATE TABLE IF NOT EXISTS users (
    role TEXT PRIMARY KEY,
    username TEXT,
    password TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS menu (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category TEXT,
    name TEXT,
    price INTEGER,
    has_options INTEGER DEFAULT 0
)");

$db->exec("CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_no INTEGER,
    waiter TEXT,
    items TEXT,
    total INTEGER,
    status TEXT DEFAULT 'pending',
    sent_to_kitchen INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// پڕکردنەوەی سەرەتایی یوزەرەکان
$stmt = $db->query("SELECT COUNT(*) FROM users");
if ($stmt->fetchColumn() == 0) {
    $db->exec("INSERT INTO users VALUES ('admin', 'وەستا سەلام', '1234')");
    $db->exec("INSERT INTO users VALUES ('cashier', 'کاشێر', '1234')");
    $db->exec("INSERT INTO users VALUES ('waiter', 'ناوساڵە', '1234')");
}

// پڕکردنەوەی سەرەتایی مینۆ
$stmt = $db->query("SELECT COUNT(*) FROM menu");
if ($stmt->fetchColumn() == 0) {
    $initial_menu = [
        ['پیتزا', 'پیتزای بچوک', 3000, 1],
        ['پیتزا', 'پیتزای وەسەت', 4000, 1],
        ['پیتزا', 'پیتزای گەورە', 5000, 1],
        ['پیتزا', 'پیتزای ٧ هەزاری', 7000, 1],
        ['پیتزا', 'پیتزای داخراو', 4000, 1],
        ['پیتزا', 'پیتزای مارگریتا', 3500, 0],
        ['پیتزا', 'پیتزای پێپەڕۆنی', 4000, 0],
        ['پیتزا', 'پیتزای ١٠ هەزاری', 10000, 1],
        ['پیتزا', 'پیتزای ١٣ هەزاری', 13000, 1],
        ['پیتزا', 'پیتزای ١٥ هەزاری', 15000, 1],
        ['گەص و شاورمە', 'گەص مریشک', 1250, 0],
        ['گەص و شاورمە', 'ماعون مریشک', 4000, 0],
        ['گەص و شاورمە', 'گەص حەیوان', 2000, 0],
        ['گەص و شاورمە', 'ماعون حەیوان', 6000, 0],
        ['گەص و شاورمە', 'شاورمەی مریشک', 1500, 0],
        ['گەص و شاورمە', 'شاورمەی حەیوان', 2500, 0],
        ['سورکراوە', 'بەرگر حەیوان (خۆماڵی)', 3000, 0],
        ['سورکراوە', 'دەبڵ بەرگر (خۆماڵی)', 6000, 0],
        ['سورکراوە', 'چیز بەرگر (خۆماڵی)', 3500, 0],
        ['سورکراوە', 'بەرگر حەیوان', 2000, 0],
        ['سورکراوە', 'دەبڵ بەرگر حەیوان', 4000, 0],
        ['سورکراوە', 'چیز بەرگر حەیوان', 2500, 0],
        ['سورکراوە', 'بەرگر مریشک', 2000, 0],
        ['سورکراوە', 'چیز بەرگر مریشک', 2500, 0],
        ['سورکراوە', 'پەتاتە', 2000, 0],
        ['سورکراوە', 'کنتاکی + فینگەر', 5500, 0],
        ['موقەبیلات', 'موقەبیلات بچووک', 2500, 0],
        ['موقەبیلات', 'موقەبیلات وەسەت', 3500, 0],
        ['موقەبیلات', 'موقەبیلات گەورە', 5000, 0],
        ['کولێرەکان', 'کولێرە بەقیمە', 1250, 0],
        ['کولێرەکان', 'کولێرە بەقیمەی دەبڵ', 2000, 0],
        ['کولێرەکان', 'کولێرە بەسنگ', 1500, 0],
        ['کولێرەکان', 'کولێرە بەپەنیر', 1500, 0],
        ['کولێرەکان', 'کولێرە بەقیمە و پەنیر', 2000, 0],
        ['کولێرەکان', 'کولێرە بەسنگ و پەنیر', 2000, 0],
        ['کولێرەکان', 'کولێرە بەقیمە و سنگ', 2000, 0],
        ['کولێرەکان', 'کولێرەی موشەکەل', 2500, 0],
        ['کولێرەکان', 'کولێرە داخراو (سنگ+پەنیر)', 2500, 0],
        ['کولێرەکان', 'کولێرە داخراو (قیمە+پەنیر)', 3000, 0],
        ['برژاو', 'کەباب', 3500, 0],
        ['برژاو', 'تکەی گۆشت', 3500, 0],
        ['برژاو', 'تکەی مریشک', 3500, 0],
        ['برژاو', 'جگەر', 3500, 0],
        ['کوڵاوەکان', 'سادە', 2500, 0],
        ['کوڵاوەکان', 'سادە + مریشک', 4000, 0],
        ['کوڵاوەکان', 'سادە + ماعون مریشک', 6000, 0],
        ['کوڵاوەکان', 'سادە + ماعون حەیوان', 8000, 0],
        ['کوڵاوەکان', 'کەباب لەسەر برنج', 6000, 0],
        ['خواردنەوەکان', 'بیپسی فریش', 1000, 0],
        ['خواردنەوەکان', 'چای', 250, 0],
        ['خواردنەوەکان', 'بیپسی', 500, 0],
        ['خواردنەوەکان', 'پیپسی دەبە', 750, 0],
        ['خواردنەوەکان', 'دیۆ', 500, 0],
        ['خواردنەوەکان', 'دیۆی دەبە', 750, 0],
        ['خواردنەوەکان', 'میرندا', 500, 0],
        ['خواردنەوەکان', 'میرندای دەبە', 750, 0],
        ['خواردنەوەکان', 'دۆی خۆماڵی', 750, 0],
        ['خواردنەوەکان', 'دۆی گەورە خۆماڵی', 1250, 0],
        ['خواردنەوەکان', 'ماستاو', 500, 0],
        ['خواردنەوەکان', 'ئاو', 250, 0]
    ];
    
    $stmt = $db->prepare("INSERT INTO menu (category, name, price, has_options) VALUES (?, ?, ?, ?)");
    foreach ($initial_menu as $item) {
        $stmt->execute($item);
    }
}

// بەڕێوەبردنی داواکارییە ئەکشنەکان (AJAX) - وەک خۆی لە کۆدی یەکەم
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    if ($action == 'login') {
        $role = $_POST['role'];
        $pass = $_POST['password'];
        $stmt = $db->prepare("SELECT * FROM users WHERE role = ? AND password = ?");
        $stmt->execute([$role, $pass]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['status' => 'success', 'role' => $role, 'username' => $user['username']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ناوی بەکارهێنەر یان وشەی نهێنی هەڵەیە']);
        }
        exit;
    }
    
    if ($action == 'get_data') {
        $menu = $db->query("SELECT * FROM menu")->fetchAll(PDO::FETCH_ASSOC);
        $orders = $db->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $users = $db->query("SELECT role, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['menu' => $menu, 'orders' => $orders, 'users' => $users]);
        exit;
    }
    
    if ($action == 'save_order') {
        $table_no = $_POST['table_no'];
        $waiter = $_POST['waiter'];
        $items = $_POST['items'];
        $total = $_POST['total'];
        
        $stmt = $db->prepare("INSERT INTO orders (table_no, waiter, items, total, sent_to_kitchen) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$table_no, $waiter, $items, $total]);
        echo json_encode(['status' => 'success', 'order_id' => $db->lastInsertId()]);
        exit;
    }
    
    if ($action == 'mark_sent_kitchen') {
        $order_id = $_POST['order_id'];
        $stmt = $db->prepare("UPDATE orders SET sent_to_kitchen = 1 WHERE id = ?");
        $stmt->execute([$order_id]);
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action == 'update_menu') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stmt = $db->prepare("UPDATE menu SET name = ?, price = ? WHERE id = ?");
        $stmt->execute([$name, $price, $id]);
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action == 'add_menu') {
        $category = $_POST['category'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $has_options = isset($_POST['has_options']) ? 1 : 0;
        $stmt = $db->prepare("INSERT INTO menu (category, name, price, has_options) VALUES (?, ?, ?, ?)");
        $stmt->execute([$category, $name, $price, $has_options]);
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action == 'update_password') {
        $role = $_POST['role'];
        $new_pass = $_POST['new_pass'];
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE role = ?");
        $stmt->execute([$new_pass, $role]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full w-full overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>خواردنگە و پیتزای شاهانە - POS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Vazirmatn -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        royal: { 500: '#85142b', 600: '#670018', 700: '#4a0010' }
                    },
                    fontFamily: { sans: ['Vazirmatn', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; scrollbar-width: none; }
        *::-webkit-scrollbar { display: none; }
        html, body { height: 100vh; height: 100dvh; width: 100vw; margin: 0; padding: 0; overflow: hidden; font-family: 'Vazirmatn', sans-serif; user-select: none; -webkit-user-select: none; background-color: #050507; }
        .modal-scrollable { overflow-y: auto; overflow-x: hidden; scrollbar-width: thin; scrollbar-color: #d97706 #18181b; }
        .modal-scrollable::-webkit-scrollbar { display: block !important; width: 5px; }
        .modal-scrollable::-webkit-scrollbar-track { background: #18181b; border-radius: 4px; }
        .modal-scrollable::-webkit-scrollbar-thumb { background: #d97706; border-radius: 4px; }
        .btn-press { transition: transform 0.3s ease, background-color 0.3s ease; cursor: pointer; }
        .btn-press:active { transform: scale(0.95); }
        .vip-login-box { background: linear-gradient(145deg, rgba(18, 18, 24, 0.95) 0%, rgba(6, 6, 9, 0.98) 100%); border: 2px solid rgba(245, 158, 11, 0.4); box-shadow: 0 60px 120px -25px rgba(0, 0, 0, 0.98); backdrop-filter: blur(35px); }
        .pulse-green { animation: pulse-green 2s infinite; }
        @keyframes pulse-green { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
        .pulse-red { animation: pulse-red 2s infinite; }
        @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }
        @media print { body * { visibility: hidden; } #printArea, #printArea * { visibility: visible; } #printArea { position: absolute; left: 0; top: 0; width: 100%; } }
    </style>
</head>
<body class="text-zinc-100 flex flex-col h-[100dvh] w-screen overflow-hidden bg-black">

    <!-- شاشەی چوونەژوورەوە -->
    <div id="loginScreen" class="fixed inset-0 bg-black/95 backdrop-blur-3xl z-[100] flex items-center justify-center p-4">
        <div class="vip-login-box relative z-20 rounded-[3rem] w-full max-w-sm sm:max-w-md p-8 sm:p-10 flex flex-col items-center text-center">
            <h1 class="text-xl sm:text-2xl font-black text-amber-300 mb-7 tracking-tight">خواردنگە و پیتزای شاهانە</h1>
            <div class="w-full space-y-4 text-right mb-7">
                <div>
                    <label class="block text-[11px] text-amber-200/90 font-extrabold mb-1.5">جۆری بەکارهێنەر:</label>
                    <select id="login-role" class="w-full bg-black/90 border border-amber-500/40 rounded-2xl px-4 py-3.5 text-xs text-white focus:outline-none focus:border-amber-400 font-black">
                        <option value="waiter">ناوساڵە</option>
                        <option value="cashier">کاشێر</option>
                        <option value="admin">بەڕێوبەر (وەستا سەلام)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-amber-200/90 font-extrabold mb-1.5">وشەی نهێنی:</label>
                    <input type="password" id="login-password" placeholder="••••••••" class="w-full bg-black/90 border border-amber-500/40 rounded-2xl px-4 py-3.5 text-xs text-white focus:outline-none focus:border-amber-400 font-black tracking-widest">
                </div>
            </div>
            <button type="button" onclick="login()" class="btn-press w-full bg-gradient-to-r from-amber-600 via-amber-500 to-amber-400 text-zinc-950 py-4 rounded-2xl text-xs font-black shadow-lg">چوونەژوورەوە بۆ سیستەم</button>
        </div>
    </div>

    <!-- سەرۆک کۆنتڕۆڵ (Header) -->
    <header class="relative w-full text-white shadow-2xl flex-none z-30 border-b-2 border-amber-500/30 py-3 px-4 md:px-8 flex items-center justify-between gap-3 bg-zinc-950">
        <div class="flex items-center gap-2">
            <button onclick="connectBluetoothPrinter()" class="bg-black/60 border border-amber-500/30 text-amber-400 w-10 h-10 rounded-xl flex items-center justify-center relative cursor-pointer shadow">
                <span id="bt-led" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-red-500 pulse-red z-10"></span>
                <i class="fa-brands fa-bluetooth-b text-xs"></i>
            </button>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex flex-col items-end">
                <h1 class="font-black text-sm md:text-lg text-amber-300">خواردنگە و پیتزای شاهانە</h1>
                <div class="text-[10px] text-amber-400/95 font-bold flex items-center gap-1.5 mt-0.5">
                    <span id="current-user-name" class="bg-amber-500/20 border border-amber-500/40 text-amber-300 px-2 py-0.5 rounded font-black"></span>
                </div>
            </div>
            <button onclick="logout()" class="bg-zinc-900 hover:bg-zinc-800 text-red-400 border border-red-900/60 px-2.5 py-1 rounded-lg text-[10px] font-black flex items-center gap-1">
                <i class="fa-solid fa-right-from-bracket"></i> دەرچوون
            </button>
        </div>
    </header>

    <!-- شاشەی سەرەکی سیستم -->
    <div id="app-screen" class="flex-1 flex flex-col lg:flex-row overflow-hidden relative w-full h-full bg-black hidden">
        <div class="flex-1 flex flex-col h-full overflow-hidden border-l border-zinc-900 bg-black">
            
            <!-- ١٤ مێزەکە -->
            <div id="view-tables" class="bg-zinc-950 p-3 border-b border-zinc-900 shadow-lg flex-none">
                <h3 class="text-[11px] font-black text-amber-300 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-chair text-amber-500 text-xs"></i> ژووری خێزانی (١٤ مێز)
                </h3>
                <div class="grid grid-cols-7 sm:grid-cols-10 lg:grid-cols-14 gap-1.5 py-1" id="tables-container"></div>
            </div>

            <!-- بەشی بەڕێوبەر -->
            <div id="view-admin" class="flex-1 p-3 overflow-y-auto hidden bg-black">
                <div class="flex gap-2 mb-3">
                    <button class="tab-btn active bg-amber-500 text-zinc-950 px-3 py-1.5 rounded-xl text-xs font-black" onclick="switchAdminTab('menu')">دەستکاری مینۆ</button>
                    <button class="tab-btn bg-zinc-900 text-amber-400 px-3 py-1.5 rounded-xl text-xs font-black" onclick="switchAdminTab('sales')">فرۆشی ڕۆژانە</button>
                    <button class="tab-btn bg-zinc-900 text-amber-400 px-3 py-1.5 rounded-xl text-xs font-black" onclick="switchAdminTab('passwords')">گۆڕینی وشەی نهێنی</button>
                </div>
                
                <div id="admin-tab-menu">
                    <h4 class="text-xs font-black text-amber-300 mb-2">زیادکردنی خواردنی نوێ</h4>
                    <form id="add-menu-form" onsubmit="addMenuItem(event)" class="bg-zinc-900/90 p-3 rounded-2xl border border-zinc-800 mb-4 space-y-2">
                        <select id="new-cat" class="w-full bg-black border border-zinc-800 rounded-xl p-2 text-xs text-white">
                            <option value="پیتزا">پیتزا</option>
                            <option value="گەص و شاورمە">گەص و شاورمە</option>
                            <option value="سورکراوە">سورکراوە</option>
                            <option value="موقەبیلات">موقەبیلات</option>
                            <option value="کولێرەکان">کولێرەکان</option>
                            <option value="برژاو">برژاو</option>
                            <option value="کوڵاوەکان">کوڵاوەکان</option>
                            <option value="خواردنەوەکان">خواردنەوەکان</option>
                        </select>
                        <input type="text" id="new-name" placeholder="ناوی خواردن" required class="w-full bg-black border border-zinc-800 rounded-xl p-2 text-xs text-white">
                        <input type="number" id="new-price" placeholder="نرخ (دینار)" required class="w-full bg-black border border-zinc-800 rounded-xl p-2 text-xs text-white">
                        <div id="pizza-option-check" style="display:none;" class="text-xs text-amber-300">
                            <label><input type="checkbox" id="new-has-options"> هەڵبژاردەی (قیمە / سنگ) هبێت؟</label>
                        </div>
                        <button type="submit" class="w-full bg-amber-500 text-zinc-950 font-black py-2 rounded-xl text-xs">زیادکردن بۆ مینۆ</button>
                    </form>
                    <h4 class="text-xs font-black text-amber-300 mb-2">لیستی خواردنەکانی ئێستا</h4>
                    <div id="admin-menu-list"></div>
                </div>

                <div id="admin-tab-sales" style="display: none;">
                    <h4 class="text-xs font-black text-amber-300 mb-2">کۆی فرۆشی ڕۆژانە</h4>
                    <div id="sales-content" class="bg-zinc-900 p-4 rounded-2xl border border-zinc-800 text-xs"></div>
                </div>

                <div id="admin-tab-passwords" style="display: none;">
                    <h4 class="text-xs font-black text-amber-300 mb-2">گۆڕینی وشەی نهێنی ڕۆڵەکان</h4>
                    <div class="bg-zinc-900 p-4 rounded-2xl border border-zinc-800 space-y-2 text-xs">
                        <label class="text-zinc-400">ڕۆڵ:</label>
                        <select id="pwd-role" class="w-full bg-black border border-zinc-800 rounded-xl p-2 text-white">
                            <option value="waiter">ناوساڵە</option>
                            <option value="cashier">کاشێر</option>
                            <option value="admin">بەڕێوبەر</option>
                        </select>
                        <input type="password" id="pwd-new" placeholder="وشەی نهێنی نوێ" class="w-full bg-black border border-zinc-800 rounded-xl p-2 text-white">
                        <button onclick="updatePassword()" class="w-full bg-amber-500 text-zinc-950 font-black py-2 rounded-xl">نوێکردنەوەی وشەی نهێنی</button>
                    </div>
                </div>
            </div>

            <!-- بەشی کاشێر -->
            <div id="view-cashier" class="flex-1 p-3 overflow-y-auto hidden bg-black">
                <h3 class="text-xs font-black text-amber-300 mb-2">داواکارییە ئەکتیڤەکانی مێزەکان (کاشێر)</h3>
                <div id="cashier-orders-list" class="space-y-2"></div>
            </div>

        </div>
    </div>

    <!-- مۆدیۆڵی وەرگرتنی خواردن بۆ ناوساڵە -->
    <div id="order-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-950 border border-amber-500/30 rounded-3xl w-full max-w-xl p-4 shadow-2xl flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-900 mb-3">
                <h3 id="modal-table-title" class="text-sm font-black text-amber-300"></h3>
                <button onclick="closeOrderModal()" class="text-zinc-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <div class="modal-scrollable flex-1 space-y-3 pr-1 bg-black/40 p-3 rounded-2xl border border-zinc-900 overflow-y-auto max-h-[220px]" id="modal-menu-container"></div>

            <h4 class="text-xs font-black text-amber-300 mt-3 mb-1">خواردنە هەڵبژێراوەکان:</h4>
            <div id="cart-items-container" class="max-h-[140px] overflow-y-auto divide-y divide-zinc-900 text-xs"></div>
            
            <div class="flex justify-between items-center bg-amber-950/20 border border-amber-500/30 px-3 py-2 rounded-xl text-xs font-black text-white mt-3">
                <span>کۆی گشتی:</span>
                <span class="text-amber-400 text-sm"><span id="cart-total">0</span> دینار</span>
            </div>
            
            <button onclick="sendOrderToKitchen()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 rounded-xl text-xs font-black shadow-lg mt-3">ناردن بۆ چێشتخانە و پرێنتکردنی وەسڵ</button>
        </div>
    </div>

    <div class="hidden"><canvas id="thermalCanvas" width="384" height="600"></canvas></div>
    <div id="printArea" class="hidden"><img id="printableImage" src="" style="width: 100%; max-width: 58mm; display: block; margin: 0 auto;"></div>

    <script>
        let currentUser = null;
        let currentRole = null;
        let globalMenu = [];
        let globalOrders = [];
        let globalUsers = [];
        let selectedTable = null;
        let currentCart = [];
        let bluetoothDevice = null;
        let bluetoothCharacteristic = null;

        function login() {
            let role = document.getElementById('login-role').value;
            let password = document.getElementById('login-password').value;

            let formData = new FormData();
            formData.append('action', 'login');
            formData.append('role', role);
            formData.append('password', password);

            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    currentRole = data.role;
                    currentUser = data.username;
                    document.getElementById('loginScreen').style.display = 'none';
                    document.getElementById('app-screen').style.display = 'flex';
                    document.getElementById('current-user-name').textContent = currentUser + " (" + getRoleName(currentRole) + ")";
                    
                    setupRoleInterface();
                    loadData();
                    setInterval(loadData, 4000);
                } else {
                    alert(data.message);
                }
            });
        }

        function getRoleName(role) {
            if(role === 'admin') return 'بەڕێوبەر';
            if(role === 'cashier') return 'کاشێر';
            return 'ناوساڵە';
        }

        function logout() {
            location.reload();
        }

        function setupRoleInterface() {
            document.getElementById('view-tables').style.display = 'block';
            document.getElementById('view-admin').style.display = 'none';
            document.getElementById('view-cashier').style.display = 'none';

            if (currentRole === 'admin') {
                document.getElementById('view-admin').style.display = 'block';
            } else if (currentRole === 'cashier') {
                document.getElementById('view-cashier').style.display = 'block';
            }
        }

        function loadData() {
            let formData = new FormData();
            formData.append('action', 'get_data');
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                globalMenu = data.menu;
                globalOrders = data.orders;
                globalUsers = data.users;
                renderTables();
                if (currentRole === 'admin') {
                    renderAdminMenu();
                    renderSales();
                }
                if (currentRole === 'cashier') renderCashierOrders();
            });
        }

        function renderTables() {
            let container = document.getElementById('tables-container');
            container.innerHTML = '';
            for (let i = 1; i <= 14; i++) {
                let activeOrder = globalOrders.find(o => o.table_no == i && o.status === 'pending');
                let btn = document.createElement('button');
                let bgClass = activeOrder ? 'bg-emerald-950/90 text-emerald-200 border-emerald-500/60' : 'bg-zinc-900 text-zinc-300 border-zinc-800';
                btn.className = `py-2 px-1 rounded-xl text-center flex flex-col items-center justify-center transition border ${bgClass}`;
                btn.innerHTML = `
                    <span class="text-[9px] opacity-80 font-bold">مێزی</span>
                    <span class="text-sm font-black">${i}</span>
                `;
                btn.onclick = () => handleTableClick(i);
                container.append(btn);
            }
        }

        function handleTableClick(tableNo) {
            if (currentRole === 'admin') {
                alert('بەڕێوبەر دەسەڵاتی وەرگرتنی داواکاری لە مێزەکانی نییە.');
                return;
            }
            selectedTable = tableNo;
            currentCart = [];
            document.getElementById('modal-table-title').textContent = 'مێزی ' + tableNo + ' - وەرگرتنی خواردن';
            renderModalMenu();
            renderCart();
            document.getElementById('order-modal').style.display = 'flex';
        }

        function closeOrderModal() {
            document.getElementById('order-modal').style.display = 'none';
        }

        function renderModalMenu() {
            let container = document.getElementById('modal-menu-container');
            container.innerHTML = '';
            let categories = [...new Set(globalMenu.map(m => m.category))];
            
            categories.forEach(cat => {
                let catDiv = document.createElement('div');
                catDiv.innerHTML = `<h4 class="text-amber-400 font-black text-xs mb-1.5">${cat}</h4>`;
                let grid = document.createElement('div');
                grid.className = 'grid grid-cols-2 gap-1.5 mb-2';
                
                let items = globalMenu.filter(m => m.category === cat);
                items.forEach(item => {
                    let btn = document.createElement('button');
                    btn.className = 'bg-black border border-zinc-800 hover:border-amber-500/40 p-2 rounded-xl text-right text-xs font-bold text-zinc-200 transition';
                    btn.innerHTML = `${item.name} <br><span class="text-amber-400 text-[10px]">(${item.price} د)</span>`;
                    btn.onclick = () => addItemToCart(item);
                    grid.append(btn);
                });
                catDiv.append(grid);
                container.append(catDiv);
            });
        }

        function addItemToCart(item) {
            let variant = '';
            if (item.has_options == 1) {
                let opt = prompt("بژاردەی پیتزا هەڵبژێرە:\n١. قیمە\n٢. سنگ", "قیمە");
                if (!opt) return;
                if (opt.includes('قیمە') || opt == '1') variant = ' (قیمە)';
                else if (opt.includes('سنگ') || opt == '2') variant = ' (سنگ)';
                else variant = ' (' + opt + ')';
            }
            
            currentCart.push({
                id: item.id,
                name: item.name + variant,
                price: item.price,
                qty: 1,
                note: ''
            });
            renderCart();
        }

        function renderCart() {
            let container = document.getElementById('cart-items-container');
            container.innerHTML = '';
            let total = 0;
            
            currentCart.forEach((item, index) => {
                let itemSum = item.price * item.qty;
                total += itemSum;
                let div = document.createElement('div');
                div.className = 'py-2 flex flex-col gap-1';
                div.innerHTML = `
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-zinc-100">${item.name} - ${item.price} دینار</span>
                        <button onclick="removeFromCart(${index})" class="text-red-400 text-xs font-bold"><i class="fa-solid fa-trash"></i></button>
                    </div>
                    <input type="text" placeholder="تێبینی..." value="${item.note}" oninput="currentCart[${index}].note = this.value" class="w-full bg-black border border-zinc-800 rounded-lg p-1 text-[11px] text-zinc-300">
                `;
                container.append(div);
            });
            document.getElementById('cart-total').textContent = total;
        }

        function removeFromCart(index) {
            currentCart.splice(index, 1);
            renderCart();
        }

        function sendOrderToKitchen() {
            if (currentCart.length === 0) {
                alert('تکایە خواردن هەڵبژێرە');
                return;
            }
            let total = currentCart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            
            let formData = new FormData();
            formData.append('action', 'save_order');
            formData.append('table_no', selectedTable);
            formData.append('waiter', currentUser);
            formData.append('items', JSON.stringify(currentCart));
            formData.append('total', total);
            
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    printKitchenReceipt(selectedTable, currentUser, currentCart);
                    printCustomerReceipt(selectedTable, currentUser, currentCart, total);
                    alert('داواکارییەکە سەرکەوتووانە نێردرا!');
                    closeOrderModal();
                    loadData();
                    
                    let updateData = new FormData();
                    updateData.append('action', 'mark_sent_kitchen');
                    updateData.append('order_id', data.order_id);
                    fetch('', { method: 'POST', body: updateData });
                }
            });
        }

        function switchAdminTab(tab) {
            document.querySelectorAll('#view-admin .tab-btn').forEach(b => {
                b.classList.remove('bg-amber-500', 'text-zinc-950');
                b.classList.add('bg-zinc-900', 'text-amber-400');
            });
            event.target.classList.remove('bg-zinc-900', 'text-amber-400');
            event.target.classList.add('bg-amber-500', 'text-zinc-950');

            document.getElementById('admin-tab-menu').style.display = 'none';
            document.getElementById('admin-tab-sales').style.display = 'none';
            document.getElementById('admin-tab-passwords').style.display = 'none';
            
            if (tab === 'menu') document.getElementById('admin-tab-menu').style.display = 'block';
            else if (tab === 'sales') document.getElementById('admin-tab-sales').style.display = 'block';
            else if (tab === 'passwords') document.getElementById('admin-tab-passwords').style.display = 'block';
        }

        document.getElementById('new-cat').onchange = function() {
            let pizzaOpt = document.getElementById('pizza-option-check');
            pizzaOpt.style.display = (this.value === 'پیتزا') ? 'block' : 'none';
        };

        function addMenuItem(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append('action', 'add_menu');
            formData.append('category', document.getElementById('new-cat').value);
            formData.append('name', document.getElementById('new-name').value);
            formData.append('price', document.getElementById('new-price').value);
            formData.append('has_options', document.getElementById('new-has-options').checked ? 1 : 0);
            
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('زیادکرا');
                    document.getElementById('new-name').value = '';
                    document.getElementById('new-price').value = '';
                    loadData();
                }
            });
        }

        function renderAdminMenu() {
            let container = document.getElementById('admin-menu-list');
            container.innerHTML = '';
            globalMenu.forEach(item => {
                let div = document.createElement('div');
                div.className = 'bg-black border border-zinc-800 p-2 rounded-xl mb-2 flex items-center justify-between text-xs';
                div.innerHTML = `
                    <span class="text-zinc-300">${item.name} (${item.price} د)</span>
                    <button onclick="updateMenuItem(${item.id})" class="bg-amber-500 text-zinc-950 font-black px-2 py-1 rounded-lg">نوێکردنەوە</button>
                `;
                container.append(div);
            });
        }

        function updateMenuItem(id) {
            let newName = prompt('ناوی نوێ:');
            let newPrice = prompt('نرخی نوێ:');
            if(!newName || !newPrice) return;
            
            let formData = new FormData();
            formData.append('action', 'update_menu');
            formData.append('id', id);
            formData.append('name', newName);
            formData.append('price', newPrice);
            fetch('', { method: 'POST', body: formData }).then(() => loadData());
        }

        function renderSales() {
            let totalSales = globalOrders.reduce((sum, o) => sum + parseInt(o.total), 0);
            document.getElementById('sales-content').innerHTML = `
                <p class="mb-2"><b>کۆی گشتی فرۆش:</b> ${totalSales} دینار</p>
                <p><b>ژمارەی داواکارییەکان:</b> ${globalOrders.length}</p>
            `;
        }

        function updatePassword() {
            let formData = new FormData();
            formData.append('action', 'update_password');
            formData.append('role', document.getElementById('pwd-role').value);
            formData.append('new_pass', document.getElementById('pwd-new').value);
            fetch('', { method: 'POST', body: formData }).then(() => alert('گۆڕدرا'));
        }

        function renderCashierOrders() {
            let container = document.getElementById('cashier-orders-list');
            container.innerHTML = '';
            globalOrders.forEach(order => {
                let div = document.createElement('div');
                div.className = 'bg-zinc-900 border border-zinc-800 p-3 rounded-2xl text-xs';
                div.innerHTML = `
                    <div class="flex justify-between font-black text-amber-300">
                        <span>مێزی ${order.table_no}</span>
                        <span>${order.total} دینار</span>
                    </div>
                    <p class="text-zinc-400 mt-1">ناوساڵە: ${order.waiter}</p>
                `;
                container.append(div);
            });
        }

        async function connectBluetoothPrinter() {
            try {
                bluetoothDevice = await navigator.bluetooth.requestDevice({ acceptAllDevices: true });
                let server = await bluetoothDevice.gatt.connect();
                let service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                bluetoothCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                document.getElementById('bt-led').classList.replace('bg-red-500', 'bg-emerald-500');
                alert('پرێنتەر بەسترا!');
            } catch (err) {
                alert('کۆنێکت نەبوو');
            }
        }

        function printKitchenReceipt(tableNo, waiter, items) {}
        function printCustomerReceipt(tableNo, waiter, items, total) {}
    </script>
</body>
</html>
