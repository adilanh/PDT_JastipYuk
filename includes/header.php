<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>JastipYuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-blue-600">JastipYuk</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                        <?php if (isJastiper()): ?>
                            <a href="jastiper-dashboard.php" class="text-gray-700 hover:text-blue-600">Kelola Jastip</a>
                        <?php endif; ?>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-blue-600">Login</a>
                        <a href="register.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
