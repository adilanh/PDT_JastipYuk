<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Simple admin check - you can enhance this with proper admin role
if (!isLoggedIn() || $_SESSION['username'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_POST && isset($_POST['generate_backup'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Get all tables
        $tables = [];
        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $backup_content = "-- JastipYuk Database Backup\n";
        $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $backup_content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($tables as $table) {
            // Get table structure
            $result = $db->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
            $backup_content .= $row[1] . ";\n\n";
            
            // Get table data
            $result = $db->query("SELECT * FROM `$table`");
            $num_fields = $result->columnCount();
            
            if ($result->rowCount() > 0) {
                $backup_content .= "INSERT INTO `$table` VALUES ";
                $first_row = true;
                
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    if (!$first_row) {
                        $backup_content .= ",";
                    }
                    $backup_content .= "\n(";
                    
                    for ($j = 0; $j < $num_fields; $j++) {
                        if ($j > 0) {
                            $backup_content .= ",";
                        }
                        
                        if ($row[$j] === null) {
                            $backup_content .= "NULL";
                        } else {
                            $backup_content .= "'" . addslashes($row[$j]) . "'";
                        }
                    }
                    $backup_content .= ")";
                    $first_row = false;
                }
                $backup_content .= ";\n\n";
            }
        }
        
        $backup_content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        // Send file for download
        $filename = 'jastipyuk_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($backup_content));
        
        echo $backup_content;
        exit();
        
    } catch (Exception $e) {
        $message = 'Error generating backup: ' . $e->getMessage();
    }
}

$page_title = 'Admin - Database Backup';
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Database Backup</h2>
        
        <?php if ($message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Gunakan fitur ini untuk membuat backup database JastipYuk. 
                File backup akan berisi semua tabel dan data dalam format SQL.
            </p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Pastikan untuk menyimpan file backup di tempat yang aman. File ini berisi semua data sensitif dari database.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST">
            <button type="submit" name="generate_backup" 
                    class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 font-medium">
                Generate & Download Backup
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Database</h3>
            
            <?php
            try {
                $database = new Database();
                $db = $database->getConnection();
                
                // Get database info
                $result = $db->query("SELECT COUNT(*) as user_count FROM users");
                $user_count = $result->fetch(PDO::FETCH_ASSOC)['user_count'];
                
                $result = $db->query("SELECT COUNT(*) as post_count FROM jastip_posts");
                $post_count = $result->fetch(PDO::FETCH_ASSOC)['post_count'];
                
                $result = $db->query("SELECT COUNT(*) as order_count FROM orders");
                $order_count = $result->fetch(PDO::FETCH_ASSOC)['order_count'];
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $user_count; ?></div>
                    <div class="text-sm text-gray-600">Total Users</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo $post_count; ?></div>
                    <div class="text-sm text-gray-600">Total Posts</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600"><?php echo $order_count; ?></div>
                    <div class="text-sm text-gray-600">Total Orders</div>
                </div>
            </div>
            
            <?php
            } catch (Exception $e) {
                echo '<p class="text-red-600">Error retrieving database information.</p>';
            }
            ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
