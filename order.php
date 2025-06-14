<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireCustomer();

$post_id = $_GET['post_id'] ?? 0;
$error = '';
$success = '';

$database = new Database();
$db = $database->getConnection();

// Get post details
$query = "SELECT jp.*, u.username, up.full_name 
          FROM jastip_posts jp 
          JOIN users u ON jp.jastiper_id = u.id 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE jp.id = ? AND jp.is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit();
}

if ($_POST) {
    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $notes = trim($_POST['notes']);
    
    if (empty($item_name) || $quantity < 1) {
        $error = 'Nama barang dan jumlah harus diisi dengan benar.';
    } else {
        // Insert order
        $query = "INSERT INTO orders (post_id, customer_id, item_name, quantity, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$post_id, $_SESSION['user_id'], $item_name, $quantity, $notes])) {
            $success = 'Pesanan berhasil dikirim! Jastiper akan segera memproses pesanan Anda.';
        } else {
            $error = 'Terjadi kesalahan saat mengirim pesanan.';
        }
    }
}

$page_title = 'Buat Pesanan';
include 'includes/header.php';
?>

<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Detail Jastip</h2>
        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($post['title']); ?></h3>
        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($post['description']); ?></p>
        <div class="text-sm text-gray-500">
            <p>Jastiper: <?php echo htmlspecialchars($post['full_name'] ?: $post['username']); ?></p>
            <p>Kontak: <?php echo htmlspecialchars($post['contact']); ?></p>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Buat Pesanan</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-2">
                    <a href="dashboard.php" class="text-green-800 underline">Lihat pesanan saya</a>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="item_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                <input type="text" id="item_name" name="item_name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($_POST['item_name'] ?? ''); ?>"
                       placeholder="Contoh: Kaos Uniqlo warna hitam size M">
            </div>
            
            <div class="mb-4">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                <input type="number" id="quantity" name="quantity" min="1" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1'); ?>">
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Tambahan informasi atau permintaan khusus..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600">
                    Kirim Pesanan
                </button>
                <a href="index.php" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
