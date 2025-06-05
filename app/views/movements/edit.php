<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Movement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Edit Movement</h1>
        <form action="/movements/<?= $movement['id'] ?>/update" method="post" class="bg-white p-6 rounded shadow-md">
            <div class="mb-4">
                <label class="block mb-2">Client</label>
                <select name="client_id" class="w-full border px-3 py-2 rounded" required>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>" <?= $client['id'] == $movement['client_id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Type</label>
                <select name="type" class="w-full border px-3 py-2 rounded" required>
                    <option value="earning" <?= $movement['type'] === 'earning' ? 'selected' : '' ?>>Earning</option>
                    <option value="expense" <?= $movement['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Amount</label>
                <input type="number" name="amount" step="0.01" min="0" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($movement['amount']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Date</label>
                <input type="date" name="date" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($movement['date']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Description</label>
                <textarea name="description" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($movement['description']) ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Movement</button>
            <a href="/movements" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
</body>
</html> 