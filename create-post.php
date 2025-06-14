<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireJastiper();

$error = '';
$success = '';

if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $contact = trim($_POST['contact']);
    
    if (empty($title) || empty($description) || empty($contact)) {
        $error = 'Semua field harus diisi.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO jastip_posts (jastiper_id, title, description, contact) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $contact])) {
            $success = 'Postingan jastip berhasil dibuat!';
        } else {
            $error = 'Terjadi kesalahan saat membuat postingan.';
        }
    }
}

$page_title = 'Buat Postingan Jastip';
include 'includes/header.php';
?>

<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Buat Postingan Jastip Baru</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
                <div class="mt-2">
                    <a href="jastiper-dashboard.php" class="text-green-800 underline">Kembali ke dashboard</a>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Jastip</label>
                <input type="text" id="title" name="title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                       placeholder="Contoh: Jastip Uniqlo & H&M Jakarta">
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea id="description" name="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Jelaskan detail jastip Anda, seperti lokasi, jenis barang yang bisa dititipkan, dll."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="mb-6">
                <label for="contact" class="block text-sm font-medium text-gray-700 mb-2">Kontak</label>
                <input type="text" id="contact" name="contact" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>"
                       placeholder="Contoh: WhatsApp 08123456789">
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Buat Postingan
                </button>
                <a href="jastiper-dashboard.php" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
