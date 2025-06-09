<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movements</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="mb-4">
            <a href="/dashboard" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                &larr; Back to Dashboard
            </a>
        </div>
        <h1 class="text-2xl font-bold mb-6">Movements</h1>
        <a href="/movements/create" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Add Movement</a>
        <form method="get" class="mb-6 flex flex-wrap items-end gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date ?? ''); ?>" class="mt-1 block w-full border px-3 py-2 rounded">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date ?? ''); ?>" class="mt-1 block w-full border px-3 py-2 rounded">
            </div>
            <div class="pt-5">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Filter</button>
                <a href="/movements" class="ml-2 text-gray-600 hover:underline">Reset</a>
            </div>
        </form>
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
        <!-- Pagination Controls -->
        <?php if (isset($page, $totalPages)): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <?php
                $query = [];
                if (!empty($start_date)) $query['start_date'] = $start_date;
                if (!empty($end_date)) $query['end_date'] = $end_date;
                ?>
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($query, ['page' => $page - 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($query, ['page' => $i])) ?>" class="<?= $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($query, ['page' => $page + 1])) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 