# 👩🏻‍💻 PDT_JastipYuk (Project untuk UAP Pemrosesan Data Terdistribusi)

**JastipYuk** adalah sebuah sistem pengelolaan yang dirancang untuk memfasilitasi jasa titip barang (jastip) antara pengguna. Dibangun menggunakan PHP dan MySQL, sistem ini menyediakan fitur pengelolaan akun pengguna, transaksi pemesanan, serta mekanisme backup otomatis.

![image](https://github.com/user-attachments/assets/f0dd3b83-aaa8-4145-85cc-c73dc13a22c7)

## 📋 Stored Procedure
Stored procedure digunakan untuk menangani proses pemesanan barang dan mencatat aktivitas pengguna secara langsung di dalam database. Salah satu prosedur yang digunakan adalah UbahStatusPesanan, yang berfungsi untuk memperbarui status pesanan pada tabel orders. Selain memperbarui status, prosedur ini juga secara otomatis mencatat perubahan tersebut ke tabel log_pesanan.

![Screenshot 2025-06-14 122255](https://github.com/user-attachments/assets/96c866a1-0447-434c-b0fe-931c11348bf9)

```php
if ($_POST && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $valid_statuses = ['Menunggu Persetujuan', 'Disetujui', 'Barang Dibeli', 'Dikirim', 'Selesai', 'Ditolak'];

    if (in_array($new_status, $valid_statuses)) {
        try {
            $db->beginTransaction();
            $query = "CALL UbahStatusPesanan(?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$order_id, $new_status]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            echo "Gagal memperbarui status: " . $e->getMessage();
        }
    }
} 
```

* Kode ini memanggil stored procedure UbahStatusPesanan dari PHP untuk mengubah status pesanan dan mencatatnya ke log. Stored procedure memproses logika langsung di database, sehingga aplikasi tetap ringan.

## ❗ Trigger
Sistem JastipYuk menerapkan trigger dengan nama SaatUserBaruDaftar. Trigger ini akan dijalankan setiap kali ada data baru dimasukkan ke dalam tabel users, yaitu saat seorang pengguna mendaftar. Trigger akan otomatis membuat entri awal pada tabel user_profiles dengan nama lengkap default.

![Screenshot 2025-06-14 122146](https://github.com/user-attachments/assets/81248988-8823-4944-b6bc-e2c16c1be98d)


## 🖥️ Transaction
Pada file SQL sistem ini juga digunakan transaksi database, yang ditandai dengan perintah START TRANSACTION dan COMMIT. Transaksi digunakan untuk menjamin semua perintah SQL di dalamnya dijalankan secara utuh atau tidak dijalankan sama sekali. Jika terjadi kesalahan di tengah proses, maka seluruh perubahan akan dibatalkan sehingga tidak ada data yang tersimpan setengah jalan.

## 💡 Function
Fungsi HitungTotalPesananAktif ini digunakan untuk menghitung jumlah pesanan aktif dari seorang jastiper berdasarkan ID-nya. Pesanan aktif didefinisikan sebagai pesanan yang belum memiliki status "Selesai" atau "Ditolak". 

![Screenshot 2025-06-14 122416](https://github.com/user-attachments/assets/e8f42df4-c4ee-42e3-979e-71f3b1c9e781)


## 📥 Backup Database
Sistem JastipYuk menyediakan fitur **backup database otomatis** yang dapat dijalankan melalui antarmuka admin. Fitur ini mengamankan data penting dengan membuat salinan dari seluruh database ke dalam file `.sql`.
### Kode PHP Backup (`admin-backup.php`)

```php
<if ($_POST && isset($_POST['generate_backup'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Ambil semua tabel dalam database
        $tables = [];
        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $backup_content = "-- JastipYuk Database Backup\n";
        $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $backup_content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($tables as $table) {
            // Struktur tabel
            $result = $db->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
            $backup_content .= $row[1] . ";\n\n";
            
            // Data tabel
            $result = $db->query("SELECT * FROM `$table`");
            $num_fields = $result->columnCount();
            
            if ($result->rowCount() > 0) {
                $backup_content .= "INSERT INTO `$table` VALUES ";
                $first_row = true;
                
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $backup_content .= ($first_row ? "\n(" : ",\n(");
                    for ($j = 0; $j < $num_fields; $j++) {
                        $backup_content .= ($j ? "," : "") . ($row[$j] === null ? "NULL" : "'" . addslashes($row[$j]) . "'");
                    }
                    $backup_content .= ")";
                    $first_row = false;
                }
                $backup_content .= ";\n\n";
            }
        }
        
        $backup_content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        // Kirim file ke browser untuk diunduh
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
```

## 📌 Relevansi Proyek dengan Pemrosesan Data Terdistribusi
Sistemem ini dirancang dengan memperhatikan prinsip-prinsip dasar pemrosesan data terdistribusi:
*Konsistensi: Semua transaksi dikelola menggunakan stored procedure dan mekanisme validasi yang terpusat di tingkat database, sehingga memastikan bahwa setiap perubahan data dilakukan secara konsisten, terlepas dari siapa pengguna atau perangkat yang mengaksesnya.
*Reliabilitas: Implementasi transaction (beginTransaction, commit, dan rollback) serta trigger bawaan di database memastikan sistem tetap berfungsi dengan baik meskipun terjadi kegagalan sebagian (seperti kesalahan saat eksekusi atau koneksi terputus).
*Integritas: Dengan menyimpan sebagian besar logika bisnis—seperti pengelolaan status pesanan dan pembuatan profil pengguna—langsung di dalam database (melalui procedure dan trigger), data tetap valid dan sinkron meskipun sistem dikembangkan untuk diakses dari berbagai sumber, seperti antarmuka web, aplikasi mobile, atau API eksternal.
