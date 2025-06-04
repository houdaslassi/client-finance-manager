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
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($title); ?></h1>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <p class="mb-4">Are you sure you want to delete the client "<?php echo htmlspecialchars($client['name']); ?>"?</p>
                <p class="mb-6 text-red-600">This action cannot be undone.</p>

                <form action="/clients/<?php echo $client['id']; ?>/delete" method="POST">
                    <div class="flex items-center justify-between">
                        <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Delete Client
                        </button>
                        <a href="/clients" class="text-blue-500 hover:text-blue-800">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 