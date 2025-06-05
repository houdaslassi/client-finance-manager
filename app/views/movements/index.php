<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movements</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Movements</h1>
        <a href="/movements/create" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Add Movement</a>
        <table class="min-w-full bg-white shadow-md rounded mb-4">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">Client</th>
                    <th class="py-2 px-4 border-b">Type</th>
                    <th class="py-2 px-4 border-b">Amount</th>
                    <th class="py-2 px-4 border-b">Date</th>
                    <th class="py-2 px-4 border-b">Description</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $movement): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($movement['client_name']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars(ucfirst($movement['type'])) ?></td>
                    <td class="py-2 px-4 border-b">$<?= number_format($movement['amount'], 2) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($movement['date']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($movement['description']) ?></td>
                    <td class="py-2 px-4 border-b">
                        <a href="/movements/<?= $movement['id'] ?>/edit" class="text-blue-500 hover:underline">Edit</a>
                        <a href="/movements/<?= $movement['id'] ?>/delete" class="text-red-500 hover:underline ml-2">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="/dashboard" class="text-gray-600 hover:underline">Back to Dashboard</a>
    </div>
</body>
</html> 