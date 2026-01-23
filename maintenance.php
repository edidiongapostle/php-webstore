<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - WebStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto text-center p-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-6">
                <i class="fas fa-tools text-6xl text-indigo-600"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Under Maintenance</h1>
            
            <p class="text-gray-600 mb-6">
                We're currently performing scheduled maintenance to improve our services. 
                We'll be back online shortly!
            </p>
            
            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">What to expect:</h3>
                    <ul class="text-sm text-blue-800 space-y-1 text-left">
                        <li><i class="fas fa-check-circle mr-2"></i>Improved performance</li>
                        <li><i class="fas fa-check-circle mr-2"></i>New features</li>
                        <li><i class="fas fa-check-circle mr-2"></i>Enhanced security</li>
                    </ul>
                </div>
                
                <div class="bg-gray-100 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-2"></i>
                        Estimated downtime: Less than 2 hours
                    </p>
                </div>
            </div>
            
            <div class="mt-8">
                <p class="text-sm text-gray-500">
                    For urgent inquiries, please contact our support team.
                </p>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                © <?php echo date('Y'); ?> WebStore. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
