<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151; /* Gray-700 */
        }
        .form-input, .form-textarea, .form-file {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #D1D5DB; /* Gray-300 */
            border-radius: 0.375rem;
            box-shadow: inset 0 1px 2px 0 rgba(0,0,0,0.05);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-input:focus, .form-textarea:focus, .form-file:focus {
            outline: none;
            border-color: #2563EB; /* Blue-600 */
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .form-file {
            padding: 0.5rem; /* Specific padding for file input */
        }
        .submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background-color: #2563EB; /* Blue-600 */
            color: white;
            font-weight: 500;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
        }
        .submit-button:hover {
            background-color: #1D4ED8; /* Blue-700 */
        }
        .message-area {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }
        .message-success {
            background-color: #D1FAE5; /* Green-100 */
            color: #065F46; /* Green-700 */
            border: 1px solid #A7F3D0; /* Green-300 */
        }
        .message-error {
            background-color: #FEE2E2; /* Red-100 */
            color: #991B1B; /* Red-700 */
            border: 1px solid #FECACA; /* Red-300 */
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="form-container">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Add New Product</h1>

        <form id="addProductForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="form-label">Product Name:</label>
                <input type="text" id="name" name="name" class="form-input" required>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" rows="4" class="form-textarea" required></textarea>
            </div>

            <div class="mb-4">
                <label for="price" class="form-label">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0" class="form-input" required>
            </div>

            <div class="mb-4">
                <label for="stock" class="form-label">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" min="0" class="form-input" required>
            </div>

            <div class="mb-4">
                <label for="category_id" class="form-label">Category ID:</label>
                <input type="number" id="category_id" name="category_id" min="1" class="form-input" required>
                </div>

            <div class="mb-6">
                <label for="image" class="form-label">Product Image:</label>
                <input type="file" id="image" name="image" class="form-file" accept="image/*" required>
            </div>

            <div class="text-center">
                <button type="submit" class="submit-button">
                    <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Add Product
                </button>
            </div>
        </form>

        <div id="messageArea" class="message-area" style="display: none;"></div>
    </div>

    <script>
        const form = document.getElementById('addProductForm');
        const messageArea = document.getElementById('messageArea');
        // IMPORTANT: Replace this URL with the actual URL of your PHP API script
        const apiUrl = 'YOUR_API_ENDPOINT_URL_HERE/your_php_script_name.php'; // e.g., 'http://localhost/PHP-Project/php/api/product.php'

        form.addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent default form submission

            // Clear previous messages
            messageArea.style.display = 'none';
            messageArea.textContent = '';
            messageArea.className = 'message-area'; // Reset classes

            // Create FormData object from the form
            const formData = new FormData(form);

            // Basic client-side validation (you already have 'required' in HTML)
            if (!formData.get('name') || !formData.get('description') || !formData.get('price') || !formData.get('stock') || !formData.get('category_id') || !formData.get('image').name) {
                showMessage('Please fill in all required fields and select an image.', 'error');
                return;
            }
            
            if (apiUrl === 'YOUR_API_ENDPOINT_URL_HERE/your_php_script_name.php') {
                showMessage('Error: API URL not configured. Please update the `apiUrl` variable in the script.', 'error');
                console.error('API URL not configured. Please update the `apiUrl` variable in the script.');
                return;
            }


            try {
                // Send POST request to the API
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData // No need to set Content-Type, browser handles it for FormData
                });

                // Check if the response is OK (status 200-299)
                if (!response.ok) {
                    // Try to parse error response if it's JSON, otherwise use status text
                    let errorData;
                    try {
                        errorData = await response.json();
                    } catch (e) {
                        // Not JSON, use status text
                    }
                    const errorMessage = errorData?.message || `Error: ${response.status} ${response.statusText}`;
                    throw new Error(errorMessage);
                }

                // Parse the JSON response from the server
                const result = await response.json();

                if (result.message && result.message.toLowerCase().includes('successfully')) {
                    showMessage(result.message, 'success');
                    form.reset(); // Optionally reset the form on success
                } else {
                    showMessage(result.message || 'An unknown error occurred.', 'error');
                }

            } catch (error) {
                console.error('Submission error:', error);
                showMessage(`Submission failed: ${error.message}`, 'error');
            }
        });

        function showMessage(message, type) {
            messageArea.textContent = message;
            messageArea.className = `message-area message-${type}`; // e.g., message-success or message-error
            messageArea.style.display = 'block';
        }
    </script>

</body>
</html>