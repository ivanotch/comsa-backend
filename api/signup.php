<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
    
    if (isset($_POST["bulk_create"])) {
        $domain = $_POST["domain"];
        $full_names = array_filter(array_map('trim', explode("\n", $_POST["full_names"])));
        $ids = array_filter(array_map('trim', explode("\n", $_POST["ids"])));

        try {
            require_once '../config/db.php';
            require_once '../model/signup_model.php';
            require_once '../controller/signup_contr.php';

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            // Validate equal number of full names and IDs
            if (count($full_names) !== count($ids)) {
                echo json_encode([
                    "success" => false,
                    "errors" => ["server" => "Number of full names and IDs must match"]
                ]);
                exit;
            }

            foreach ($full_names as $index => $full_name) {
                $id = $ids[$index] ?? '';
                $full_name = trim($full_name);
                $id = trim($id);
                
                if (empty($full_name) || empty($id)) {
                    $results[] = [
                        'id' => $id,
                        'full_name' => $full_name,
                        'status' => 'error',
                        'message' => 'Missing full name or ID'
                    ];
                    $errorCount++;
                    continue;
                }

                // Get the last name (last word in the full name)
                $nameParts = explode(' ', $full_name);
                $lastName = end($nameParts);
                $firstInitial = !empty($nameParts[0]) ? strtolower(substr($nameParts[0], 0, 1)) : '';
                
                // Generate email and password
                $email = strtolower($lastName) . '.' . $firstInitial . '.bscs@' . $domain;
                $password = strtoupper($lastName); // All capital letters

                $errors = [];

                if (is_student_number_taken($pdo, $id)) {
                    $errors["id_taken"] = "ID already exists!";
                }

                if (is_email_taken($pdo, $email)) {
                    $errors["email_taken"] = "Email already exists!";
                }

                if ($errors) {
                    $results[] = [
                        'id' => $id,
                        'full_name' => $full_name,
                        'status' => 'error',
                        'message' => implode(' ', $errors)
                    ];
                    $errorCount++;
                    continue;
                }

                // Create the user with the full name
                create_user($pdo, $id, $full_name, $email, $password);

                $results[] = [
                    'id' => $id,
                    'full_name' => $full_name,
                    'status' => 'success',
                    'email' => $email,
                    'password' => $password
                ];
                $successCount++;
            }

            $pdo = null;
            $stmt = null;

            echo json_encode([
                "success" => true,
                "message" => "Bulk creation completed: $successCount success, $errorCount errors",
                "results" => $results
            ]);
            exit;

        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "errors" => ["server" => "Query Failed: " . $e->getMessage()]
            ]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Account Generator</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 1000px;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 24px;
            font-size: 28px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
            line-height: 1.5;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
            padding-left: 40px !important; /* Increased padding to accommodate line numbers */
        }
        
        .line-numbers {
            position: absolute;
            left: 12px;
            top: 40px;
            bottom: 12px;
            width: 20px;
            overflow-y: hidden;
            color: #999;
            font-size: 16px;
            line-height: 1.5;
            padding-top: 3px; /* Adjust this to align with text */
        }
        
        input:focus, textarea:focus {
            border-color: #4a90e2;
            outline: none;
        }
        
        button {
            padding: 12px 24px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #357ab8;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-top: 20px;
            font-weight: 600;
            display: none;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .results-table th, .results-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .results-table th {
            background-color: #f2f2f2;
            position: sticky;
            top: 0;
        }
        
        .status-success {
            color: #2ecc71;
        }
        
        .status-error {
            color: #e74c3c;
        }
        
        .example {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .example h3 {
            margin-bottom: 10px;
        }
        
        .example pre {
            white-space: pre-wrap;
            font-family: monospace;
        }
        
        .scrollable-table {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 15px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            padding: 30px;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 24px;
            color: #333;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }

        .modal-textarea {
            min-height: 300px;
            font-size: 16px;
            line-height: 1.5;
            padding-left: 40px !important;
        }

        .modal-line-numbers {
            position: absolute;
            left: 12px;
            top: 60px;
            bottom: 12px;
            width: 20px;
            overflow-y: hidden;
            color: #999;
            font-size: 16px;
            line-height: 1.5;
            padding-top: 3px;
        }

        .open-modal-btn {
            margin-bottom: 15px;
            background-color: #5cb85c;
        }

        .open-modal-btn:hover {
            background-color: #4cae4c;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Account Generator</h1>
        
        <div class="example">
            <h3>Format Example:</h3>
            <pre>
ID: 2023-001    Full Name: Juan Carlos Delacruz Reyes
→ Email: reyes.j.bscs@gmail.com
→ Password: REYES

ID: 2023-002    Full Name: Nina Mikaela Tan-Santos
→ Email: santos.n.bscs@gmail.com
→ Password: SANTOS

ID: 2023-003    Full Name: Popi Lori
→ Email: lori.p.bscs@gmail.com
→ Password: LORI
            </pre>
        </div>
        
        <form id="bulkForm">
            <div class="form-group">
                <label for="domain">Email Domain</label>
                <input type="text" id="domain" name="domain" required 
                       value="gmail.com" placeholder="e.g., gmail.com">
            </div>
            
            <button type="button" class="open-modal-btn" id="openModalBtn">Open Full-Screen Editor</button>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ids">Student IDs (one per line)</label>
                    <div class="line-numbers" id="id-line-numbers"></div>
                    <textarea id="ids" name="ids" required 
                              placeholder="2023-001&#10;2023-002&#10;2023-003"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="full_names">Full Names (one per line, same order)</label>
                    <div class="line-numbers" id="name-line-numbers"></div>
                    <textarea id="full_names" name="full_names" required 
                              placeholder="Juan Carlos Delacruz Reyes&#10;Nina Mikaela Tan-Santos&#10;Popi Lori"></textarea>
                </div>
            </div>
            
            <button type="submit">Generate Accounts</button>
            
            <div class="success-message" id="bulkSuccessMessage"></div>
            
            <div id="resultsContainer" style="display: none;">
                <h3>Results</h3>
                <div class="scrollable-table">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal for full-screen editing -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Full-Screen Editor</h2>
                <button class="close-modal" id="closeModalBtn">&times;</button>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="modal-ids">Student IDs (one per line)</label>
                    <div class="modal-line-numbers" id="modal-id-line-numbers"></div>
                    <textarea id="modal-ids" class="modal-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="modal-full_names">Full Names (one per line, same order)</label>
                    <div class="modal-line-numbers" id="modal-name-line-numbers"></div>
                    <textarea id="modal-full_names" class="modal-textarea"></textarea>
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" id="applyChangesBtn">Apply Changes</button>
                <button type="button" id="cancelChangesBtn">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Add line numbers to textareas
        function updateLineNumbers(textarea, lineNumberElement) {
            const lines = textarea.value.split('\n').length;
            let numbers = '';
            for (let i = 1; i <= lines; i++) {
                numbers += i + '<br>';
            }
            lineNumberElement.innerHTML = numbers;
            
            // Sync scroll positions
            lineNumberElement.scrollTop = textarea.scrollTop;
        }

        // Initialize line numbers
        const idsTextarea = document.getElementById('ids');
        const fullNamesTextarea = document.getElementById('full_names');
        const idLineNumbers = document.getElementById('id-line-numbers');
        const nameLineNumbers = document.getElementById('name-line-numbers');

        // Modal elements
        const modal = document.getElementById('editModal');
        const modalIdsTextarea = document.getElementById('modal-ids');
        const modalFullNamesTextarea = document.getElementById('modal-full_names');
        const modalIdLineNumbers = document.getElementById('modal-id-line-numbers');
        const modalNameLineNumbers = document.getElementById('modal-name-line-numbers');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const applyChangesBtn = document.getElementById('applyChangesBtn');
        const cancelChangesBtn = document.getElementById('cancelChangesBtn');

        // Update line numbers when text changes
        idsTextarea.addEventListener('input', () => updateLineNumbers(idsTextarea, idLineNumbers));
        fullNamesTextarea.addEventListener('input', () => updateLineNumbers(fullNamesTextarea, nameLineNumbers));
        modalIdsTextarea.addEventListener('input', () => updateLineNumbers(modalIdsTextarea, modalIdLineNumbers));
        modalFullNamesTextarea.addEventListener('input', () => updateLineNumbers(modalFullNamesTextarea, modalNameLineNumbers));

        // Sync scrolling between textarea and line numbers
        idsTextarea.addEventListener('scroll', () => {
            idLineNumbers.scrollTop = idsTextarea.scrollTop;
        });
        
        fullNamesTextarea.addEventListener('scroll', () => {
            nameLineNumbers.scrollTop = fullNamesTextarea.scrollTop;
        });
        
        modalIdsTextarea.addEventListener('scroll', () => {
            modalIdLineNumbers.scrollTop = modalIdsTextarea.scrollTop;
        });
        
        modalFullNamesTextarea.addEventListener('scroll', () => {
            modalNameLineNumbers.scrollTop = modalFullNamesTextarea.scrollTop;
        });

        // Initialize line numbers
        updateLineNumbers(idsTextarea, idLineNumbers);
        updateLineNumbers(fullNamesTextarea, nameLineNumbers);

        // Modal functionality
        openModalBtn.addEventListener('click', () => {
            // Copy current values to modal
            modalIdsTextarea.value = idsTextarea.value;
            modalFullNamesTextarea.value = fullNamesTextarea.value;
            updateLineNumbers(modalIdsTextarea, modalIdLineNumbers);
            updateLineNumbers(modalFullNamesTextarea, modalNameLineNumbers);
            
            // Show modal
            modal.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        cancelChangesBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        applyChangesBtn.addEventListener('click', () => {
            // Copy modal values back to form
            idsTextarea.value = modalIdsTextarea.value;
            fullNamesTextarea.value = modalFullNamesTextarea.value;
            updateLineNumbers(idsTextarea, idLineNumbers);
            updateLineNumbers(fullNamesTextarea, nameLineNumbers);
            
            // Close modal
            modal.style.display = 'none';
        });

        // Close modal when clicking outside the content
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Bulk form submission
        document.getElementById('bulkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Creating Accounts...';
            submitButton.disabled = true;
            
            // Hide previous results
            document.getElementById('resultsContainer').style.display = 'none';
            document.getElementById('bulkSuccessMessage').style.display = 'none';
            
            try {
                const formData = new FormData(this);
                formData.append('bulk_create', 'true');
                
                const response = await fetch('signup.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    const successMessage = document.getElementById('bulkSuccessMessage');
                    successMessage.textContent = result.message;
                    successMessage.style.display = 'block';
                    
                    // Display results
                    const resultsBody = document.getElementById('resultsBody');
                    resultsBody.innerHTML = '';
                    
                    result.results.forEach(item => {
                        const row = document.createElement('tr');
                        
                        row.innerHTML = `
                            <td>${item.id || ''}</td>
                            <td>${item.full_name || ''}</td>
                            <td>${item.email || ''}</td>
                            <td>${item.password || ''}</td>
                            <td class="status-${item.status}">${item.status}</td>
                        `;
                        
                        resultsBody.appendChild(row);
                    });
                    
                    document.getElementById('resultsContainer').style.display = 'block';
                    
                    // Clear form if everything was successful
                    if (result.results.every(r => r.status === 'success')) {
                        this.reset();
                        document.getElementById('domain').value = 'gmail.com';
                        updateLineNumbers(idsTextarea, idLineNumbers);
                        updateLineNumbers(fullNamesTextarea, nameLineNumbers);
                    }
                } else {
                    // Show error message
                    const successMessage = document.getElementById('bulkSuccessMessage');
                    successMessage.textContent = 'Error: ' + (result.errors?.server || 'Unknown error');
                    successMessage.style.color = '#e74c3c';
                    successMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                const successMessage = document.getElementById('bulkSuccessMessage');
                successMessage.textContent = 'An unexpected error occurred. Please try again.';
                successMessage.style.color = '#e74c3c';
                successMessage.style.display = 'block';
            } finally {
                // Restore button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            }
        });
    </script>
</body>
</html>