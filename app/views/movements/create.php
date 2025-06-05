<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Movement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Add Movement</h1>
        <div class="mb-4">
            <a href="/dashboard" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                &larr; Back to Dashboard
            </a>
        </div>
        <form action="/movements/store" method="post" class="bg-white p-6 rounded shadow-md">
            <div class="mb-4">
                <label class="block mb-2">Client</label>
                <select name="client_id" class="w-full border px-3 py-2 rounded" required>
                    <option value="">Select Client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Type</label>
                <select name="type" class="w-full border px-3 py-2 rounded" required>
                    <option value="">Select Type</option>
                    <option value="earning">Earning</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Amount</label>
                <input type="number" name="amount" step="0.01" min="0" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Date</label>
                <input type="date" name="date" class="w-full border px-3 py-2 rounded" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Description</label>
                <textarea name="description" class="w-full border px-3 py-2 rounded"></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Movement</button>
            <a href="/movements" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
</body>
</html> 