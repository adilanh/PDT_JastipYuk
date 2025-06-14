<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireJastiper();

$database = new Database();
$db = $database->getConnection();

// Handle status update using stored procedure
if ($_POST && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $valid_statuses = ['Menunggu Persetujuan', 'Disetujui', 'Barang Dibeli', 'Dikirim', 'Selesai', 'Ditolak'];
    
    if (in_array($new_status, $valid_statuses)) {
        try {
            // Use stored procedure with transaction for status update and notification
            $db->beginTransaction();
            
            // Call stored procedure
            $query = "CALL UbahStatusPesanan(?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id, $new_status]);
            
            // Add notification for customer
            $query = "INSERT INTO notifications (user_id, message) 
                      SELECT o.customer_id, CONCAT('Status pesanan #', o.id, ' diubah menjadi: ', ?) 
                      FROM orders o WHERE o.id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$new_status, $order_id]);
            
            $db->commit();
            $success = 'Status pesanan berhasil diperbarui.';
        } catch (Exception $e) {
            $db->rollback();
            $error = 'Terjadi kesalahan saat memperbarui status.';
        }
    }
}

// Get all orders for this jastiper
$query = "SELECT o.*, jp.title as post_title, u.username as customer_name, up.full_name as customer_full_name 
          FROM orders o 
          JOIN jastip_posts jp ON o.post_id = jp.id 
          JOIN users u ON o.customer_id = u.id 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE jp.jastiper_id = ? 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get jastiper posts
$query = "SELECT * FROM jastip_posts WHERE jastiper_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Kelola Jastip';
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Kelola Jastip</h1>
        <a href="create-post.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            Buat Postingan Baru
        </a>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Orders Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Pesanan Masuk</h2>
        
        <?php if (empty($orders)): ?>
            <p class="text-gray-500">Belum ada pesanan masuk.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $order['id']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium"><?php echo htmlspecialchars($order['item_name']); ?></div>
                                    <?php if ($order['notes']): ?>
                                        <div class="text-gray-500 text-xs">Catatan: <?php echo htmlspecialchars($order['notes']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($order['customer_full_name'] ?: $order['customer_name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $order['quantity']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php 
                                        switch($order['status']) {
                                            case 'Menunggu Persetujuan': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'Disetujui': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'Barang Dibeli': echo 'bg-purple-100 text-purple-800'; break;
                                            case 'Dikirim': echo 'bg-indigo-100 text-indigo-800'; break;
                                            case 'Selesai': echo 'bg-green-100 text-green-800'; break;
                                            case 'Ditolak': echo 'bg-red-100 text-red-800'; break;
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="text-xs border border-gray-300 rounded px-2 py-1">
                                            <option value="Menunggu Persetujuan" <?php echo $order['status'] === 'Menunggu Persetujuan' ? 'selected' : ''; ?>>Menunggu Persetujuan</option>
                                            <option value="Disetujui" <?php echo $order['status'] === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                            <option value="Barang Dibeli" <?php echo $order['status'] === 'Barang Dibeli' ? 'selected' : ''; ?>>Barang Dibeli</option>
                                            <option value="Dikirim" <?php echo $order['status'] === 'Dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                            <option value="Selesai" <?php echo $order['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="Ditolak" <?php echo $order['status'] === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                        </select>
                                        <button type="submit" name="update_status" class="ml-2 bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Posts Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Postingan Jastip Saya</h2>
        
        <?php if (empty($posts)): ?>
            <p class="text-gray-500">Belum ada postingan jastip. <a href="create-post.php" class="text-blue-500 hover:text-blue-600">Buat postingan pertama</a></p>
        <?php else: ?>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($posts as $post): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($post['description']); ?></p>
                        <p class="text-gray-500 text-xs mb-2">Kontak: <?php echo htmlspecialchars($post['contact']); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs <?php echo $post['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $post['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                            </span>
                            <span class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
