<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Welcome to the Admin Dashboard</h1>
        <p class="mb-4">You are logged in as <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?></strong>.</p>
        <ul class="list-disc pl-6">
            <li>Manage clients</li>
            <li>Track expenses and earnings</li>
            <li>View financial reports</li>
        </ul>
        <div class="mt-6">
            <a href="/logout" class="text-blue-500 hover:underline">Logout</a>
        </div>
    </div>
</body>
</html> 