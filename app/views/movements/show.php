<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movement Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Movement Details</h1>
        <div class="bg-white p-6 rounded shadow-md">
            <p><strong>Client ID:</strong> <?= htmlspecialchars($movement['client_id']) ?></p>
            <p><strong>Type:</strong> <?= htmlspecialchars(ucfirst($movement['type'])) ?></p>
            <p><strong>Amount:</strong> $<?= number_format($movement['amount'], 2) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($movement['date']) ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($movement['description']) ?></p>
        </div>
        <a href="/movements" class="text-gray-600 hover:underline mt-4 inline-block">Back to Movements</a>
    </div>
</body>
</html> 