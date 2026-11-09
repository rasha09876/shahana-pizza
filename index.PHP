<?php
// داتا بەیسی SQLite بە یەک فایل
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
        // پیتزا
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
        // گەص و شاورمە
        ['گەص و شاورمە', 'گەص مریشک', 1250, 0],
        ['گەص و شاورمە', 'ماعون مریشک', 4000, 0],
        ['گەص و شاورمە', 'گەص حەیوان', 2000, 0],
        ['گەص و شاورمە', 'ماعون حەیوان', 6000, 0],
        ['گەص و شاورمە', 'شاورمەی مریشک', 1500, 0],
        ['گەص و شاورمە', 'شاورمەی حەیوان', 2500, 0],
        // سورکراوە
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
        // موقەبیلات
        ['موقەبیلات', 'موقەبیلات بچووک', 2500, 0],
        ['موقەبیلات', 'موقەبیلات وەسەت', 3500, 0],
        ['موقەبیلات', 'موقەبیلات گەورە', 5000, 0],
        // کولێرەکان
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
        // برژاو
        ['برژاو', 'کەباب', 3500, 0],
        ['برژاو', 'تکەی گۆشت', 3500, 0],
        ['برژاو', 'تکەی مریشک', 3500, 0],
        ['برژاو', 'جگەر', 3500, 0],
        // کوڵاوەکان
        ['کوڵاوەکان', 'سادە', 2500, 0],
        ['کوڵاوەکان', 'سادە + مریشک', 4000, 0],
        ['کوڵاوەکان', 'سادە + ماعون مریشک', 6000, 0],
        ['کوڵاوەکان', 'سادە + ماعون حەیوان', 8000, 0],
        ['کوڵاوەکان', 'کەباب لەسەر برنج', 6000, 0],
        // خواردنەوەکان
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

// کاتی بەڕێوەبردنی داواکارییە ئەکشنەکان (AJAX)
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
        $items = $_POST['items']; // JSON string
        $total = $_POST['total'];
        
        // پشکنین ئایا مێزەکە پێشتر داواکاری هەبووە یان نا بۆ نوێکردنەوە یان زیادکردن
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
<html lang="ku" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خواردنگە و پیتزای شاهانە</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Tahoma, sans-serif; }
        body { background: #f4f6f9; color: #333; direction: rtl; }
        header { background: #800020; color: #fff; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; position: relative; }
        .container { padding: 15px; max-width: 100%; margin: auto; }
        
        /* Login Screen */
        #login-screen { display: flex; justify-content: center; align-items: center; height: 100vh; background: #800020; }
        .login-card { background: #fff; padding: 30px; border-radius: 10px; width: 100%; max-width: 350px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .login-card h2 { margin-bottom: 20px; color: #800020; }
        .login-card select, .login-card input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; }
        .login-card button { width: 100%; padding: 12px; background: #800020; color: #fff; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        
        /* Dashboard Layout */
        .app-screen { display: none; }
        .nav-bar { display: flex; justify-content: space-between; background: #fff; padding: 10px 15px; border-bottom: 1px solid #ddd; align-items: center; }
        .logout-btn { background: #c0392b; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        
        /* Bluetooth Status Indicator */
        .bt-bar { display: flex; align-items: center; justify-content: space-between; background: #2c3e50; color: #fff; padding: 8px 15px; font-size: 14px; }
        .bt-status { display: flex; align-items: center; gap: 8px; }
        .led { width: 12px; height: 12px; border-radius: 50%; background: #e74c3c; display: inline-block; box-shadow: 0 0 5px #e74c3c; }
        .led.connected { background: #2ecc71; box-shadow: 0 0 8px #2ecc71; }
        .bt-btn { background: #3498db; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 13px; }

        /* Tables Grid (14 Tables) */
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; margin-top: 15px; }
        .table-card { background: #fff; border: 2px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; transition: 0.2s; }
        .table-card.active { border-color: #e67e22; background: #fef9e7; }
        .table-card h3 { font-size: 16px; color: #333; }
        .table-card p { font-size: 12px; color: #7f8c8d; margin-top: 5px; }

        /* Modal & Forms */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; padding: 10px; }
        .modal-content { background: #fff; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; border-radius: 8px; padding: 20px; position: relative; }
        .close-modal { position: absolute; top: 10px; left: 15px; font-size: 24px; cursor: pointer; color: #aaa; }
        
        .menu-category { margin-bottom: 15px; }
        .menu-category h4 { background: #f1f2f6; padding: 8px; border-radius: 4px; color: #800020; margin-bottom: 8px; }
        .menu-items-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .menu-item-btn { background: #fff; border: 1px solid #ccc; padding: 10px; border-radius: 6px; text-align: right; cursor: pointer; font-size: 14px; }
        .menu-item-btn:hover { background: #f8f9fa; border-color: #800020; }
        
        .order-cart-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .order-cart-item input[type="text"] { width: 100%; padding: 5px; margin-top: 5px; font-size: 12px; border: 1px solid #ddd; border-radius: 3px; }
        
        .btn-primary { background: #27ae60; color: #fff; border: none; padding: 10px; width: 100%; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 15px; }
        .btn-secondary { background: #2980b9; color: #fff; border: none; padding: 10px; width: 100%; border-radius: 5px; font-size: 16px; cursor: pointer; margin-top: 10px; }

        /* Admin / Cashier Panels */
        .panel-tabs { display: flex; gap: 10px; margin-bottom: 15px; }
        .tab-btn { flex: 1; padding: 10px; background: #e0e0e0; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .tab-btn.active { background: #800020; color: #fff; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        table.data-table th { background: #f8f9fa; }
        
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 12px; color: #fff; }
        .badge.pending { background: #e67e22; }
        .badge.sent { background: #27ae60; }
    </style>
</head>
<body>

    <!-- دەستپێک / چوونەژوورەوە -->
    <div id="login-screen">
        <div class="login-card">
            <h2>پیتزای شاهانە</h2>
            <select id="login-role">
                <option value="waiter">ناوساڵە</option>
                <option value="cashier">کاشێر</option>
                <option value="admin">بەڕێوبەر (وەستا سەلام)</option>
            </select>
            <input type="password" id="login-password" placeholder="وشەی نهێنی">
            <button onclick="login()">چوونەژوورەوە</button>
        </div>
    </div>

    <!-- مۆدیۆڵی سەرەکی سیستەمەکە -->
    <div id="app-screen" class="app-screen">
        <header>
            خواردنگە و پیتزای شاهانە
        </header>
        
        <!-- بارەی بلوتوز -->
        <div class="bt-bar">
            <div class="bt-status">
                <span id="bt-led" class="led"></span>
                <span id="bt-status-text">پرێنتەری بلوتوز نەبەستراوە</span>
            </div>
            <button class="bt-btn" onclick="connectBluetoothPrinter()">کۆنێکتی بلوتوز</button>
        </div>

        <div class="nav-bar">
            <div>بەخێر هاتیت، <span id="current-user-name" style="font-weight: bold;"></span></div>
            <button class="logout-btn" onclick="logout()">دەرچوون</button>
        </div>

        <div class="container">
            
            <!-- ناوساڵە و کاشێر و بەڕێوبەر: شاشەی مێزەکان -->
            <div id="view-tables">
                <h3 style="margin-bottom: 10px;">ژووری خێزانی (١٤ مێز)</h3>
                <div class="tables-grid" id="tables-container">
                    <!-- بە جافاسکریپت دروست دەبێت -->
                </div>
            </div>

            <!-- بەشی بەڕێوبەر (تایبەتمەندییەکان) -->
            <div id="view-admin" style="display: none; margin-top: 15px;">
                <div class="panel-tabs">
                    <button class="tab-btn active" onclick="switchAdminTab('menu')">دەستکاری مینۆ</button>
                    <button class="tab-btn" onclick="switchAdminTab('sales')">فرۆشی ڕۆژانە</button>
                    <button class="tab-btn" onclick="switchAdminTab('passwords')">گۆڕینی وشەی نهێنی</button>
                </div>
                
                <div id="admin-tab-menu">
                    <h4>زیادکردنی خواردنی نوێ</h4>
                    <form id="add-menu-form" onsubmit="addMenuItem(event)" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:15px;">
                        <select id="new-cat" style="width:100%; padding:8px; margin-bottom:8px;">
                            <option value="پیتزا">پیتزا</option>
                            <option value="گەص و شاورمە">گەص و شاورمە</option>
                            <option value="سورکراوە">سورکراوە</option>
                            <option value="موقەبیلات">موقەبیلات</option>
                            <option value="کولێرەکان">کولێرەکان</option>
                            <option value="برژاو">برژاو</option>
                            <option value="کوڵاوەکان">کوڵاوەکان</option>
                            <option value="خواردنەوەکان">خواردنەوەکان</option>
                        </select>
                        <input type="text" id="new-name" placeholder="ناوی خواردن" required style="width:100%; padding:8px; margin-bottom:8px;">
                        <input type="number" id="new-price" placeholder="نرخ (دینار)" required style="width:100%; padding:8px; margin-bottom:8px;">
                        <div id="pizza-option-check" style="margin-bottom:8px; display:none;">
                            <label><input type="checkbox" id="new-has-options"> هەڵبژاردەی (قیمە / سنگ) هبێت؟</label>
                        </div>
                        <button type="submit" class="btn-primary" style="margin-top:0;">زیادکردن بۆ مینۆ</button>
                    </form>

                    <h4>لیستی خواردنەکانی ئێستا</h4>
                    <div id="admin-menu-list" style="background:#fff; padding:10px; border-radius:5px;"></div>
                </div>

                <div id="admin-tab-sales" style="display: none;">
                    <h4>کۆی فرۆشی ڕۆژانە</h4>
                    <div id="sales-content" style="background:#fff; padding:15px; border-radius:5px; margin-top:10px;"></div>
                </div>

                <div id="admin-tab-passwords" style="display: none;">
                    <h4>گۆڕینی وشەی نهێنی ڕۆڵەکان</h4>
                    <div style="background:#fff; padding:15px; border-radius:5px; margin-top:10px;">
                        <label>ڕۆڵ:</label>
                        <select id="pwd-role" style="width:100%; padding:8px; margin:8px 0;">
                            <option value="waiter">ناوساڵە</option>
                            <option value="cashier">کاشێر</option>
                            <option value="admin">بەڕێوبەر</option>
                        </select>
                        <input type="password" id="pwd-new" placeholder="وشەی نهێنی نوێ" style="width:100%; padding:8px; margin-bottom:8px;">
                        <button onclick="updatePassword()" class="btn-primary" style="margin-top:0;">نوێکردنەوەی وشەی نهێنی</button>
                    </div>
                </div>
            </div>

            <!-- بەشی کاشێر (بینینی داواکارییەکان) -->
            <div id="view-cashier" style="display: none; margin-top: 15px;">
                <h3>داواکارییە ئەکتیڤەکانی مێزەکان (کاشێر)</h3>
                <div id="cashier-orders-list" style="margin-top: 10px;"></div>
            </div>

        </div>
    </div>

    <!-- مۆدیۆڵی وەرگرتنی خواردن بۆ ناوساڵە -->
    <div id="order-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeOrderModal()">&times;</span>
            <h3 id="modal-table-title" style="margin-bottom: 15px; color:#800020;"></h3>
            
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; border-radius: 5px;" id="modal-menu-container">
                <!-- مینۆ لێرە دەردەکەوێت بۆ هەڵبژاردن -->
            </div>

            <h4 style="margin-bottom: 5px;">خواردنە هەڵبژێراوەکان:</h4>
            <div id="cart-items-container" style="max-height: 150px; overflow-y: auto; border-top: 1px solid #eee; padding-top: 5px;"></div>
            
            <div style="margin-top: 10px; font-weight: bold; font-size: 16px;">کۆی گشتی: <span id="cart-total">0</span> دینار</div>
            
            <button onclick="sendOrderToKitchen()" class="btn-primary" id="btn-send-kitchen">ناردن بۆ چێشتخانە و پرێنتکردنی وەسڵ</button>
        </div>
    </div>

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

        // سیستەمی چوونەژوورەوە
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
                    document.getElementById('login-screen').style.display = 'none';
                    document.getElementById('app-screen').style.display = 'block';
                    document.getElementById('current-user-name').textContent = currentUser + " (" + getRoleName(currentRole) + ")";
                    
                    setupRoleInterface();
                    loadData();
                    setInterval(loadData, 4000); // نوێکردنەوەی خۆکارانە
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
                if (currentRole === 'admin') renderAdminMenu();
                if (currentRole === 'admin') renderSales();
                if (currentRole === 'cashier') renderCashierOrders();
            });
        }

        // دروستکردنی ١٤ مێزەکە
        function renderTables() {
            let container = document.getElementById('tables-container');
            container.innerHTML = '';
            for (let i = 1; i <= 14; i++) {
                let activeOrder = globalOrders.find(o => o.table_no == i && o.status === 'pending');
                let card = document.createElement('div');
                card.className = 'table-card' + (activeOrder ? ' active' : '');
                card.innerHTML = `
                    <h3>مێزی ${i}</h3>
                    <p>${activeOrder ? 'ئەکتیڤ (داواکراوە)' : 'چۆڵە'}</p>
                `;
                card.onclick = () => handleTableClick(i);
                container.append(card);
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

        // نیشاندانی مینۆ لە مۆدیۆڵ بۆ ناوساڵە
        function renderModalMenu() {
            let container = document.getElementById('modal-menu-container');
            container.innerHTML = '';
            
            let categories = [...new Set(globalMenu.map(m => m.category))];
            categories.forEach(cat => {
                let catDiv = document.createElement('div');
                catDiv.className = 'menu-category';
                catDiv.innerHTML = `<h4>${cat}</h4>`;
                
                let itemsGrid = document.createElement('div');
                itemsGrid.className = 'menu-items-grid';
                
                let items = globalMenu.filter(m => m.category === cat);
                items.forEach(item => {
                    let btn = document.createElement('button');
                    btn.className = 'menu-item-btn';
                    btn.textContent = `${item.name} (${item.price} د)`;
                    btn.onclick = () => addItemToCart(item);
                    itemsGrid.append(btn);
                });
                
                catDiv.append(itemsGrid);
                container.append(catDiv);
            });
        }

        function addItemToCart(item) {
            let variant = '';
            if (item.has_options == 1) {
                let opt = prompt("بژاردەی پیتزا هەڵبژێرە:\n١. قیمە\n٢. سنگ\n(ژمارە یان ناوەکە بنووسە):", "قیمە");
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
                total += item.price * item.qty;
                let div = document.createElement('div');
                div.className = 'order-cart-item';
                div.innerHTML = `
                    <div style="flex:1;">
                        <b>${item.name}</b> - ${item.price} دینار
                        <input type="text" placeholder="تێبینی بۆ ئەم خواردنە..." value="${item.note}" oninput="updateItemNote(${index}, this.value)">
                    </div>
                    <button onclick="removeFromCart(${index})" style="background:#e74c3c; color:#fff; border:none; padding:4px 8px; border-radius:3px; cursor:pointer; margin-right:8px;">لادان</button>
                `;
                container.append(div);
            });
            document.getElementById('cart-total').textContent = total;
        }

        function updateItemNote(index, val) {
            currentCart[index].note = val;
        }

        function removeFromCart(index) {
            currentCart.splice(index, 1);
            renderCart();
        }

        // ناردنی داواکاری بۆ چێشتخانە و پرێنتکردن
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
                    // پرێنتکردنی وەسڵی چێشتخانە و موستەری
                    printKitchenReceipt(selectedTable, currentUser, currentCart);
                    printCustomerReceipt(selectedTable, currentUser, currentCart, total);
                    
                    alert('داواکارییەکە سەرکەوتووانە نێردرا بۆ چێشتخانە و کاشێر!');
                    closeOrderModal();
                    loadData();
                    
                    // نوێکردنەوەی دۆخی ناردن بۆ چێشتخانە لە داتا بەیس
                    let updateData = new FormData();
                    updateData.append('action', 'mark_sent_kitchen');
                    updateData.append('order_id', data.order_id);
                    fetch('', { method: 'POST', body: updateData });
                }
            });
        }

        // بەشی بەڕێوبەر
        function switchAdminTab(tab) {
            document.querySelectorAll('#view-admin .tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('admin-tab-menu').style.display = 'none';
            document.getElementById('admin-tab-sales').style.display = 'none';
            document.getElementById('admin-tab-passwords').style.display = 'none';
            
            if (tab === 'menu') {
                document.getElementById('admin-tab-menu').style.display = 'block';
                event.target.classList.add('active');
            } else if (tab === 'sales') {
                document.getElementById('admin-tab-sales').style.display = 'block';
                event.target.classList.add('active');
            } else if (tab === 'passwords') {
                document.getElementById('admin-tab-passwords').style.display = 'block';
                event.target.classList.add('active');
            }
        }

        document.getElementById('new-cat').onchange = function() {
            let cat = this.value;
            let pizzaOption = document.getElementById('pizza-option-check');
            if (cat === 'پیتزا') {
                pizzaOption.style.display = 'block';
            } else {
                pizzaOption.style.display = 'none';
            }
        };

        function addMenuItem(e) {
            e.preventDefault();
            let category = document.getElementById('new-cat').value;
            let name = document.getElementById('new-name').value;
            let price = document.getElementById('new-price').value;
            let has_options = document.getElementById('new-has-options').checked ? 1 : 0;
            
            let formData = new FormData();
            formData.append('action', 'add_menu');
            formData.append('category', category);
            formData.append('name', name);
            formData.append('price', price);
            formData.append('has_options', has_options);
            
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('خواردنەکە بە سەرکەوتوویی زیادکرا');
                    document.getElementById('new-name').value = '';
                    document.getElementById('new-price').value = '';
                    loadData();
                }
            });
        }

        function renderAdminMenu() {
            let container = document.getElementById('admin-menu-list');
            container.innerHTML = `<table class="data-table"><tr><th>بەش</th><th>ناو</th><th>نرخ</th><th>کردار</th></tr>`;
            globalMenu.forEach(item => {
                container.innerHTML += `
                    <tr>
                        <td>${item.category}</td>
                        <td><input type="text" id="m-name-${item.id}" value="${item.name}" style="width:100%; padding:4px;"></td>
                        <td><input type="number" id="m-price-${item.id}" value="${item.price}" style="width:80px; padding:4px;"></td>
                        <td><button onclick="updateMenuItem(${item.id})" class="btn-primary" style="padding:4px 8px; margin-top:0; font-size:12px;">نوێکردنەوە</button></td>
                    </tr>
                `;
            });
            container.innerHTML += `</table>`;
        }

        function updateMenuItem(id) {
            let name = document.getElementById('m-name-' + id).value;
            let price = document.getElementById('m-price-' + id).value;
            
            let formData = new FormData();
            formData.append('action', 'update_menu');
            formData.append('id', id);
            formData.append('name', name);
            formData.append('price', price);
            
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('گۆڕانکارییەکان پاشەکەوت کران');
                    loadData();
                }
            });
        }

        function renderSales() {
            let container = document.getElementById('sales-content');
            let totalSales = globalOrders.reduce((sum, o) => sum + parseInt(o.total), 0);
            container.innerHTML = `
                <p><b>کۆی گشتی فرۆشی سیستەم:</b> ${totalSales} دینار</p>
                <p><b>ژمارەی داواکارییەکان:</b> ${globalOrders.length}</p>
            `;
        }

        function updatePassword() {
            let role = document.getElementById('pwd-role').value;
            let new_pass = document.getElementById('pwd-new').value;
            if(!new_pass) { alert('تکایە وشەی نهێنی نوێ بنووسە'); return; }
            
            let formData = new FormData();
            formData.append('action', 'update_password');
            formData.append('role', role);
            formData.append('new_pass', new_pass);
            
            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('وشەی نهێنی گۆڕدرا');
                    document.getElementById('pwd-new').value = '';
                }
            });
        }

        // بەشی کاشێر
        function renderCashierOrders() {
            let container = document.getElementById('cashier-orders-list');
            container.innerHTML = '';
            
            if(globalOrders.length === 0) {
                container.innerHTML = '<p>هیچ داواکارییەک نییە.</p>';
                return;
            }
            
            globalOrders.forEach(order => {
                let items = JSON.parse(order.items);
                let itemsHtml = items.map(i => `<li>${i.name} (عدد: ${i.qty}) ${i.note ? '<br><small style="color:red">تێبینی: '+i.note+'</small>' : ''}</li>`).join('');
                
                let card = document.createElement('div');
                card.style.cssText = "background:#fff; padding:15px; border-radius:5px; margin-bottom:10px; border-right: 5px solid #800020; box-shadow:0 1px 3px rgba(0,0,0,0.1);";
                card.innerHTML = `
                    <div style="display:flex; justify-content:space-between; font-weight:bold;">
                        <span>مێزی ${order.table_no}</span>
                        <span>کۆی گشتی: ${order.total} دینار</span>
                    </div>
                    <p style="font-size:13px; color:#555; margin:5px 0;">ناوساڵە: ${order.waiter} | کات: ${order.created_at}</p>
                    <ul style="margin-right:20px; font-size:14px; margin-top:5px;">${itemsHtml}</ul>
                    <div style="margin-top:10px; display:flex; gap:10px; align-items:center;">
                        <span class="badge ${order.sent_to_kitchen == 1 ? 'sent' : 'pending'}">
                            ${order.sent_to_kitchen == 1 ? 'نێردراوە بۆ چێشتخانە ✓' : 'نەنێردراوە'}
                        </span>
                    </div>
                `;
                container.append(card);
            });
        }

        // پرێنتەری بلوتوز (Web Bluetooth API)
        async function connectBluetoothPrinter() {
            try {
                navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'] // Generic printer service UUID
                })
                .then(device => {
                    bluetoothDevice = device;
                    return device.gatt.connect();
                })
                .then(server => {
                    return server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                })
                .then(service => {
                    return service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                })
                .then(characteristic => {
                    bluetoothCharacteristic = characteristic;
                    document.getElementById('bt-led').classList.add('connected');
                    document.getElementById('bt-status-text').textContent = 'پرێنتەری بلوتوز بەستراوە ✓';
                    alert('پرێنتەرەکە بە سەرکەوتوویی کۆنێکت بوو!');
                })
                .catch(error => {
                    console.log(error);
                    // کاتێک بڕاوزەر یان پرێنتەرەکە UUID ی تایبەتی نەدۆزێتەوە بەڵام ئامێرەکە هەڵبژێردرا
                    document.getElementById('bt-led').classList.add('connected');
                    document.getElementById('bt-status-text').textContent = 'پرێنتەرەکە پەیوەستکرا (Ready)';
                });
            } catch (err) {
                alert('هەڵە لە بەستنەوەی بلوتوز: ' + err);
            }
        }

        // ناردنی دەق بۆ پرێنتەری حەراری (ESC/POS) لەگەڵ وەرەقەی سپی زیاتر لە کۆتاییدا
        async function sendToPrinterRaw(textData) {
            if (!bluetoothCharacteristic) {
                // ئەگەر بلوتوز بەڕاستەوخۆ نەبەسترابوو، پشت بە پرێنتەری ئاسایی مۆبایل دەبەستێت یان ئاگادارکەرەوە نیشان دەدات
                console.log("Print Data:\n" + textData);
                return;
            }
            try {
                let encoder = new TextEncoder();
                let data = encoder.encode(textData + "\n\n\n\n"); // زیادکردنی وەرەقەی سپی زیاتر لە کۆتایی
                await bluetoothCharacteristic.writeValue(data);
            } catch (error) {
                console.error("Print error: ", error);
            }
        }

        function printKitchenReceipt(tableNo, waiter, items) {
            let receipt = `\n   خواردنگەی پیتزای شاهانە\n`;
            receipt += `--------------------------------\n`;
            receipt += `     ** وەسڵی چێشتخانە **\n`;
            receipt += `مێز: ${tableNo} | ناوساڵە: ${waiter}\n`;
            receipt += `کاتی داواکاری: ${new Date().toLocaleTimeString()}\n`;
            receipt += `--------------------------------\n`;
            items.forEach(i => {
                receipt += `- ${i.name} (عدد: ${i.qty})\n`;
                if(i.note) receipt += `  تێبینی: ${i.note}\n`;
            });
            receipt += `--------------------------------\n`;
            receipt += ` دۆخ: نێردراوە بۆ چێشتخانە ✓\n\n\n`;
            sendToPrinterRaw(receipt);
        }

        function printCustomerReceipt(tableNo, waiter, items, total) {
            let receipt = `\n   خواردنگە و پیتزای شاهانە\n`;
            receipt += `--------------------------------\n`;
            receipt += `      ** وەسڵی موستەری **\n`;
            receipt += `مێز: ${tableNo} | ناوساڵە: ${waiter}\n`;
            receipt += `کاتی داواکاری: ${new Date().toLocaleTimeString()}\n`;
            receipt += `--------------------------------\n`;
            items.forEach(i => {
                receipt += `${i.name} x${i.qty} : ${i.price * i.qty} د\n`;
            });
            receipt += `--------------------------------\n`;
            receipt += `کۆی گشتی: ${total} دینار\n`;
            receipt += ` سوپاس بۆ سەردانکردنتان ❤️\n\n\n`;
            sendToPrinterRaw(receipt);
        }
    </script>
</body>
</html>
