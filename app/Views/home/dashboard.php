<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: /login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-gray-800">CRM System</span>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <form action="/logout" method="POST" class="inline">
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Client Management Section -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Client Management</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="/clients" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100">
                        <h3 class="text-lg font-semibold text-blue-800">View All Clients</h3>
                        <p class="text-blue-600">Manage your client database</p>
                    </a>
                    <a href="/clients/create" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100">
                        <h3 class="text-lg font-semibold text-green-800">Add New Client</h3>
                        <p class="text-green-600">Create a new client profile</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Movement Management Section -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Movement Management</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="/movements" class="block p-4 bg-purple-50 rounded-lg hover:bg-purple-100">
                        <h3 class="text-lg font-semibold text-purple-800">View All Movements</h3>
                        <p class="text-purple-600">Track all financial movements</p>
                    </a>
                    <a href="/movements/create" class="block p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                        <h3 class="text-lg font-semibold text-indigo-800">Add New Movement</h3>
                        <p class="text-indigo-600">Record a new financial movement</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mt-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Total Clients</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars($totalClients); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Total Movements</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo htmlspecialchars($totalMovements); ?></p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mt-4">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Total Income</h3>
                <p class="text-3xl font-bold text-green-600">$<?php echo number_format($totalIncome, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Total Expenses</h3>
                <p class="text-3xl font-bold text-red-600">$<?php echo number_format($totalExpenses, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Balance</h3>
                <p class="text-3xl font-bold <?php echo ($balance >= 0) ? 'text-green-600' : 'text-red-600'; ?>">$<?php echo number_format($balance, 2); ?></p>
            </div>
        </div>
    </div>
</body>
</html> 