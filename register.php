<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (!in_array($role, ['JASTIPER', 'CUSTOMER'])) {
        $error = 'Role tidak valid.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi.';
            }
        }
    }
}

$page_title = 'Daftar';
include 'includes/header.php';
?>

<div class="max-w-md mx-auto mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Daftar Akun Baru</h2>
    
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
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" id="username" name="username" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Peran</label>
            <select id="role" name="role" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Pilih Peran</option>
                <option value="JASTIPER" <?php echo ($_POST['role'] ?? '') === 'JASTIPER' ? 'selected' : ''; ?>>Jastiper (Penyedia Jasa)</option>
                <option value="CUSTOMER" <?php echo ($_POST['role'] ?? '') === 'CUSTOMER' ? 'selected' : ''; ?>>Customer (Pelanggan)</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
            Daftar
        </button>
    </form>
    
    <p class="text-center text-sm text-gray-600 mt-4">
        Sudah punya akun? <a href="login.php" class="text-blue-500 hover:text-blue-600">Login di sini</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
