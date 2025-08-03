<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
    
    if (isset($_POST["bulk_create"])) {
        $domain = $_POST["domain"];
        $names = explode("\n", trim($_POST["names"]));
        $ids = explode("\n", trim($_POST["ids"]));

        try {
            require_once '../config/db.php';
            require_once '../model/signup_model.php';
            require_once '../controller/signup_contr.php';

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($names as $index => $name) {
                $name = trim($name);
                $id = trim($ids[$index] ?? '');
                
                if (empty($name) || empty($id)) {
                    $results[] = [
                        'name' => $name,
                        'status' => 'error',
                        'message' => 'Missing name or ID'
                    ];
                    $errorCount++;
                    continue;
                }

                // Parse name into first and last name
                $nameParts = explode(' ', $name);
                $firstName = $nameParts[0];
                $lastName = end($nameParts);
                $firstInitial = strtolower(substr($firstName, 0, 1));
                
                // Generate email and password
                $email = strtolower($lastName) . '.' . $firstInitial . '.bscs@' . $domain;
                $password = ucfirst(strtolower($lastName)); // First letter uppercase

                $errors = [];

                if (is_student_number_taken($pdo, $id)) {
                    $errors["id_taken"] = "ID already exists!";
                }

                if (is_email_taken($pdo, $email)) {
                    $errors["email_taken"] = "Email already exists!";
                }

                if ($errors) {
                    $results[] = [
                        'name' => $name,
                        'status' => 'error',
                        'message' => implode(' ', $errors)
                    ];
                    $errorCount++;
                    continue;
                }

                // Create the user
                create_user($pdo, $id, $name, $email, $password);

                $results[] = [
                    'id' => $id,
                    'name' => $name,
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
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
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
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        
        .scrollable {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Account Generator</h1>
        
        <div class="example">
            <h3>Format Example:</h3>
            <p><strong>Input:</strong></p>
            <pre>
IDs:        Names:
1001        Juan Delacruz
1002        Nina Mika
1003        Popi Lori
            </pre>
            <p><strong>Output:</strong></p>
            <pre>
ID: 1001 | Email: delacruz.j.bscs@gmail.com | Password: Delacruz
ID: 1002 | Email: mika.n.bscs@gmail.com | Password: Mika
ID: 1003 | Email: lori.p.bscs@gmail.com | Password: Lori
            </pre>
        </div>
        
        <form id="bulkForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="domain">Email Domain</label>
                    <input type="text" id="domain" name="domain" required 
                           value="gmail.com" placeholder="e.g., gmail.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ids">IDs (one per line)</label>
                    <textarea id="ids" name="ids" required 
                              placeholder="1001&#10;1002&#10;1003"></textarea>
                </div>
                <div class="form-group">
                    <label for="names">Full Names (one per line, matching order)</label>
                    <textarea id="names" name="names" required 
                              placeholder="Juan Delacruz&#10;Nina Mika&#10;Popi Lori"></textarea>
                </div>
            </div>
            
            <button type="submit">Generate Accounts</button>
            
            <div id="resultsContainer" style="display: none;">
                <h3>Results</h3>
                <div class="scrollable">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
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

    <script>
        document.getElementById('bulkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Creating Accounts...';
            submitButton.disabled = true;
            
            document.getElementById('resultsContainer').style.display = 'none';
            
            try {
                const formData = new FormData(this);
                formData.append('bulk_create', 'true');
                
                const response = await fetch('signup.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const resultsBody = document.getElementById('resultsBody');
                    resultsBody.innerHTML = '';
                    
                    result.results.forEach(item => {
                        const row = document.createElement('tr');
                        
                        row.innerHTML = `
                            <td>${item.id || ''}</td>
                            <td>${item.name || ''}</td>
                            <td>${item.email || ''}</td>
                            <td>${item.password || ''}</td>
                            <td class="status-${item.status}">${item.status}</td>
                        `;
                        
                        resultsBody.appendChild(row);
                    });
                    
                    document.getElementById('resultsContainer').style.display = 'block';
                    
                    if (result.results.every(r => r.status === 'success')) {
                        this.reset();
                        document.getElementById('domain').value = 'gmail.com';
                    }
                } else {
                    alert('Error: ' + (result.errors?.server || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            } finally {
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            }
        });
    </script>
</body>
</html>