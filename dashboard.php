<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get user profile
$query = "SELECT up.* FROM user_profiles up WHERE up.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// BARU: Mengambil notifikasi terbaru untuk pengguna yang login
$query_notif = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt_notif = $db->prepare($query_notif);
$stmt_notif->execute([$_SESSION['user_id']]);
$notifications = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);


if (isJastiper()) {
    // Get active orders count using stored function
    $query = "SELECT HitungTotalPesananAktif(?) as total_active";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $active_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_active'];
    
    // Get recent orders
    $query = "SELECT o.*, jp.title as post_title, u.username as customer_name 
              FROM orders o 
              JOIN jastip_posts jp ON o.post_id = jp.id 
              JOIN users u ON o.customer_id = u.id 
              WHERE jp.jastiper_id = ? 
              ORDER BY o.created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Get customer orders
    $query = "SELECT o.*, jp.title as post_title, u.username as jastiper_name 
              FROM orders o 
              JOIN jastip_posts jp ON o.post_id = jp.id 
              JOIN users u ON jp.jastiper_id = u.id 
              WHERE o.customer_id = ? 
              ORDER BY o.created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $my_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </div>
    
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
        <?php if (isJastiper()): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pesanan Aktif</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $active_orders; ?></p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <a href="jastiper-dashboard.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 text-center mb-2">
                    Kelola Jastip
                </a>
                <a href="create-post.php" class="block w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 text-center">
                    Buat Postingan Baru
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <a href="index.php" class="block w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 text-center">
                    Lihat Jastip Tersedia
                </a>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Profil</h3>
            <p class="text-sm text-gray-600 mb-2">Nama: <?php echo htmlspecialchars($profile['full_name'] ?: 'Belum diisi'); ?></p>
            <p class="text-sm text-gray-600 mb-4">Telepon: <?php echo htmlspecialchars($profile['phone'] ?: 'Belum diisi'); ?></p>
            <a href="profile.php" class="text-blue-500 hover:text-blue-600 text-sm">Edit Profil</a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2 lg:col-span-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notifikasi Terbaru</h3>
            <div class="space-y-4">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="border-l-4 <?php echo $notification['is_read'] ? 'border-gray-200' : 'border-blue-500'; ?> pl-4">
                            <p class="text-sm <?php echo $notification['is_read'] ? 'text-gray-700' : 'font-semibold text-gray-900'; ?>">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('d M Y, H:i', strtotime($notification['created_at'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Tidak ada notifikasi baru.</p>
                <?php endif; ?>
            </div>
        </div>
        </div>
    
    <?php if (isJastiper() && !empty($recent_orders)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pesanan Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['item_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif (!isJastiper() && !empty($my_orders)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pesanan Saya</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jastiper</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($my_orders as $order): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['item_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($order['jastiper_name']); ?></td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>