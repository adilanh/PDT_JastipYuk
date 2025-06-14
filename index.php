<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get active jastip posts
$query = "SELECT jp.*, u.username, up.full_name 
          FROM jastip_posts jp 
          JOIN users u ON jp.jastiper_id = u.id 
          LEFT JOIN user_profiles up ON u.id = up.user_id 
          WHERE jp.is_active = 1 
          ORDER BY jp.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Beranda';
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Selamat Datang di JastipYuk</h1>
        <p class="text-xl text-gray-600 mb-8">Platform sederhana untuk menghubungkan Jastiper dengan Customer</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="space-x-4">
                <a href="register.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 inline-block">Mulai Sekarang</a>
                <a href="login.php" class="border border-blue-500 text-blue-500 px-6 py-3 rounded-lg hover:bg-blue-50 inline-block">Login</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Jastip Tersedia</h2>
        
        <?php if (empty($posts)): ?>
            <div class="text-center py-12">
                <p class="text-gray-500">Belum ada jastip yang tersedia saat ini.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($posts as $post): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($post['description']); ?></p>
                        
                        <div class="text-sm text-gray-500 mb-4">
                            <p>Jastiper: <?php echo htmlspecialchars($post['full_name'] ?: $post['username']); ?></p>
                            <p>Kontak: <?php echo htmlspecialchars($post['contact']); ?></p>
                            <p>Dibuat: <?php echo date('d M Y', strtotime($post['created_at'])); ?></p>
                        </div>
                        
                        <?php if (isLoggedIn() && isCustomer()): ?>
                            <a href="order.php?post_id=<?php echo $post['id']; ?>" 
                               class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 inline-block text-center">
                                Titip Barang Sekarang
                            </a>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" 
                               class="w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 inline-block text-center">
                                Login untuk Pesan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
