<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($client['name']); ?> - Client Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded shadow">
        <div class="mb-4">
            <a href="/dashboard" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                &larr; Back to Dashboard
            </a>
        </div>
        <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($client['name']); ?></h1>
        <div class="mb-4 text-gray-700">
            <div><strong>Email:</strong> <?php echo htmlspecialchars($client['email']); ?></div>
            <div><strong>Phone:</strong> <?php echo htmlspecialchars($client['phone']); ?></div>
            <div><strong>Created at:</strong> <?php echo htmlspecialchars($client['created_at']); ?></div>
        </div>
        <div class="mb-6">
            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold">Balance: $<?php echo number_format($balance, 2); ?></span>
        </div>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-xl font-semibold">Movements</h2>
            <a href="/movements/create?client_id=<?php echo $client['id']; ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Movement</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Date</th>
                        <th class="py-2 px-4 border-b">Type</th>
                        <th class="py-2 px-4 border-b">Amount</th>
                        <th class="py-2 px-4 border-b">Description</th>
                        <th class="py-2 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($movements)): ?>
                    <tr><td colspan="5" class="py-4 text-center text-gray-500">No movements found.</td></tr>
                <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($movement['date'] ?? $movement['created_at']); ?></td>
                            <td class="py-2 px-4 border-b">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $movement['type'] === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($movement['type']); ?>
                                </span>
                            </td>
                            <td class="py-2 px-4 border-b">$<?php echo number_format($movement['amount'], 2); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($movement['description'] ?? ''); ?></td>
                            <td class="py-2 px-4 border-b">
                                <a href="/movements/<?php echo $movement['id']; ?>/edit" class="text-blue-600 hover:underline mr-2">Edit</a>
                                <a href="/movements/<?php echo $movement['id']; ?>/delete" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            <a href="/clients" class="text-gray-600 hover:underline">&larr; Back to Clients</a>
        </div>
    </div>
</body>
</html> 