<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Movement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Delete Movement</h1>
        <div class="bg-white p-6 rounded shadow-md">
            <p class="mb-4">Are you sure you want to delete this movement?</p>
            <form action="/movements/<?= $movement['id'] ?>/delete" method="post">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                <a href="/movements" class="ml-4 text-gray-600 hover:underline">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html> 