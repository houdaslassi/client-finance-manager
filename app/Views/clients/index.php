<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="/dashboard" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                &larr; Back to Dashboard
            </a>
        </div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($title); ?></h1>
            <a href="/clients/create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Client
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['message']); ?></span>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded my-6">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                <a href="/clients/<?php echo $client['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                    <?php echo htmlspecialchars($client['name']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                <?php echo htmlspecialchars($client['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                <?php echo htmlspecialchars($client['phone']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                <span class="<?php echo $client['balance'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    $<?php echo number_format($client['balance'], 2); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5">
                                <a href="/clients/<?php echo $client['id']; ?>/edit" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <a href="/clients/<?php echo $client['id']; ?>/delete" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Pagination Controls -->
            <?php if (isset($page, $totalPages) && $totalPages > 1): ?>
            <div class="flex justify-center mt-6">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 