# PDT_JastipYuk (Project untuk UAP Pemrosesan Data Terdistribusi)

**JastipYuk** adalah sebuah sistem pengelolaan yang dirancang untuk memfasilitasi jasa titip barang (jastip) antara pengguna. Dibangun menggunakan PHP dan MySQL, sistem ini menyediakan fitur pengelolaan akun pengguna, transaksi pemesanan, serta mekanisme backup otomatis.

## Stored Procedure
Stored procedure digunakan untuk menangani proses pemesanan barang dan mencatat aktivitas pengguna secara langsung di dalam database. Salah satu prosedur yang digunakan adalah UbahStatusPesanan, yang berfungsi untuk memperbarui status pesanan pada tabel orders. Selain memperbarui status, prosedur ini juga secara otomatis mencatat perubahan tersebut ke tabel log_pesanan.


![Screenshot 2025-06-14 114117](https://github.com/user-attachments/assets/10af12be-465e-44bc-8286-3fab606a61ff)



## Trigger
Sistem JastipYuk menerapkan trigger dengan nama SaatUserBaruDaftar. Trigger ini akan dijalankan setiap kali ada data baru dimasukkan ke dalam tabel users, yaitu saat seorang pengguna mendaftar. Trigger akan otomatis membuat entri awal pada tabel user_profiles dengan nama lengkap default.

## Transaction
Pada file SQL sistem ini juga digunakan transaksi database, yang ditandai dengan perintah START TRANSACTION dan COMMIT. Transaksi digunakan untuk menjamin semua perintah SQL di dalamnya dijalankan secara utuh atau tidak dijalankan sama sekali. Jika terjadi kesalahan di tengah proses, maka seluruh perubahan akan dibatalkan sehingga tidak ada data yang tersimpan setengah jalan.

## Function
Fungsi HitungTotalPesananAktif ini digunakan untuk menghitung jumlah pesanan aktif dari seorang jastiper berdasarkan ID-nya. Pesanan aktif didefinisikan sebagai pesanan yang belum memiliki status "Selesai" atau "Ditolak". 

##  Backup Database
