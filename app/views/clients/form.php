<?php
$isEdit = isset($client);
$title = $isEdit ? 'Edit Client' : 'Add New Client';
$action = $isEdit ? "/clients/{$client['id']}/edit" : '/clients/create';
$old = $old ?? ($client ?? []);
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6"><?php echo htmlspecialchars($title); ?></h1>

        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $action; ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Name
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo isset($errors['name']) ? 'border-red-500' : ''; ?>"
                       id="name" type="text" name="name" value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
                       placeholder="Client name">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                       id="email" type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                       placeholder="client@example.com">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                    Phone
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo isset($errors['phone']) ? 'border-red-500' : ''; ?>"
                       id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>"
                       placeholder="+1234567890">
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    <?php echo $isEdit ? 'Update Client' : 'Create Client'; ?>
                </button>
                <a href="/clients" class="text-blue-500 hover:text-blue-800">Cancel</a>
            </div>
        </form>
    </div>
</div> 