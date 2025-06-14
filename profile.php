<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get current profile
$query = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_POST) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Update profile
    $query = "UPDATE user_profiles SET full_name = ?, phone = ?, address = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$full_name, $phone, $address, $_SESSION['user_id']])) {
        $success = 'Profil berhasil diperbarui!';
        // Refresh profile data
        $query = "SELECT * FROM user_profiles WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = 'Terjadi kesalahan saat memperbarui profil.';
    }
}

$page_title = 'Profil';
include 'includes/header.php';
?>

<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Profil</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">
            </div>
            
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                <input type="text" id="phone" name="phone"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
            
            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea id="address" name="address" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Simpan Perubahan
                </button>
                <a href="dashboard.php" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 text-center">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
