<?php
require_once 'dbConfig.php';
require_once 'models.php';

$response = [
    "message" => "",
    "statusCode" => 200,
    "querySet" => []
];


// Registration Logic
if (isset($_POST['regBtn'])) {
    // Sanitize and retrieve input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT);
    $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $contact_no = filter_input(INPUT_POST, 'contact_no', FILTER_SANITIZE_STRING);

    // Validate input
    if (empty($username) || empty($first_name) || empty($last_name) || empty($gender) || empty($email) || empty($password) || empty($age) || empty($date_of_birth) || empty($address) || empty($contact_no)) {
        $_SESSION['message'] = "All fields are required.";
        header('Location: ../register.php');
        exit;
    }

    // Password complexity check (optional)
    if (strlen($password) < 8) {
        $_SESSION['message'] = "Password must be at least 8 characters long.";
        header('Location: ../register.php');
        exit;
    }

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "Username or email already exists.";
        header('Location:../register.php');
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, gender, email, password, age, date_of_birth, address, contact_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $first_name, $last_name, $gender, $email, $hashed_password, $age, $date_of_birth, $address, $contact_no])) {
        $_SESSION['message'] = "Registration successful! You can log in now.";
        header('Location: ../login.php');
        exit;
    } else {
        $_SESSION['message'] = "Registration failed. Please try again.";
        header('Location: ../register.php');
        exit;
    }
}

if (isset($_POST['loginBtn'])) {
    // Sanitize input
    $login_input = filter_input(INPUT_POST, 'login_input', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Fetch user from the database by either username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$login_input, $login_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

    // Verify the user
    if ($user && password_verify($password, $user['password'])) { 
        // Password is correct, set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name']; 
        $_SESSION['welcomeMessage'] = "Welcome, " . $_SESSION['username'] . "!";

        // audit log eme
        logAction($pdo, $_SESSION['user_id'], 'LOG IN', "User logged in: " . $_SESSION['username']);

        header("Location: http://localhost/mycodes/login_reg_logs/index.php");
        exit;
    } else {
        // Invalid input
        $_SESSION['message'] = "Invalid login input. Please try again.";
        header("Location: http://localhost/mycodes/login_reg_logs/login.php");
        exit;
    }
}


if (isset($_POST['searchUserBtn'])) {
    // Check if any fields are filled
    if (empty($_POST['first_name']) && empty($_POST['last_name']) && empty($_POST['email']) && empty($_POST['contact_no']) && empty($_POST['gender']) && empty($_POST['qualification']) && empty($_POST['application_status'])) {
        $response['message'] = "Please enter at least one search criterion.";
        $response['statusCode'] = 400;
        $_SESSION['searchResponse'] = $response;
        header("Location: ../search_users.php");
        exit();
    }

    try {
        $sql = "SELECT * FROM applicants WHERE 1=1";
        $params = [];

        // Check each input and add to the query if it's not empty
        if (!empty($_POST['first_name'])) {
            $sql .= " AND first_name LIKE :first_name";
            $params[':first_name'] = '%' . $_POST['first_name'] . '%';
        }
        if (!empty($_POST['last_name'])) {
            $sql .= " AND last_name LIKE :last_name";
            $params[':last_name'] = '%' . $_POST['last_name'] . '%';
        }
        if (!empty($_POST['email'])) {
            $sql .= " AND email LIKE :email";
            $params[':email'] = '%' . $_POST['email'] . '%';
        }
        if (!empty($_POST['contact_no'])) {
            $sql .= " AND contact_no LIKE :contact_no";
            $params[':contact_no'] = '%' . $_POST['contact_no'] . '%';
        }
        if (!empty($_POST['gender'])) {
            $sql .= " AND gender = :gender";
            $params[':gender'] = $_POST['gender'];
        }
        if (!empty($_POST['qualification'])) {
            $sql .= " AND qualification LIKE :qualification";
            $params[':qualification'] = '%' . $_POST['qualification'] . '%';
        }
        if (!empty($_POST['application_status'])) {
            $sql .= " AND application_status = :application_status";
            $params[':application_status'] = $_POST['application_status'];
        }

        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $querySet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update response
        if ($querySet) {
            $response['message'] = "Search results found.";
            $response['querySet'] = $querySet;
        } else {
            $response['message'] = "No users found with the given criteria.";
        }

        // Log action if user is logged in
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $action_type = 'SEARCH';
        
            // Filter out empty fields and 'searchUserBtn' from $_POST
            $filteredCriteria = array_filter($_POST, function ($value, $key) {
                return $key !== 'searchUserBtn' && !empty($value);
            }, ARRAY_FILTER_USE_BOTH);
        
            // Manually format criteria for logging without quotation marks
            $criteriaStrings = [];
            foreach ($filteredCriteria as $key => $value) {
                $criteriaStrings[] = "$key: $value";
            }
            $action_details = "Searched with the criteria: [" . implode("], [", $criteriaStrings) . "]";

                    
            logAction($pdo, $user_id, $action_type, $action_details);
        }
        
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
        $response['statusCode'] = 500;
    } catch (Exception $e) {
        $response['message'] = "An unexpected error occurred: " . $e->getMessage();
        $response['statusCode'] = 400;
    }

    // Store response in session to retrieve in search_users.php
    $_SESSION['searchResponse'] = $response;

    // Redirect back to search_users.php to display the results
    header("Location: ../search_users.php");
    exit();
}


if (isset($_POST['insertApplicationBtn'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "User is not logged in.";
        exit; // Stop execution if the user is not logged in
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = insertApplication($pdo, $_POST['first_name'], $_POST['last_name'], 
        $_POST['gender'], $_POST['email'], $_POST['contact_no'], $_POST['qualification'], 
        $_POST['date_of_birth'],$_POST['application_status'],$user_id); // Use user_id here

    if ($query) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Insertion failed";
    }
}

if (isset($_POST['editApplicationBtn'])) {
    $user_id = $_SESSION['user_id'] ?? null; // Get user_id from session
    $applicant_id = $_GET['applicant_id'] ?? null; // Ensure applicant_id is passed

    // Get the application status from the form
    $application_status = $_POST['application_status'];

    // Ensure other fields are being passed
    $query = updateApplication(
        $pdo, $_POST['first_name'], $_POST['last_name'], 
        $_POST['gender'], $_POST['email'], $_POST['date_of_birth'], $_POST['qualification'], 
        $_POST['contact_no'], $application_status, $applicant_id, $user_id
    );

    if ($query) {
        header("Location: ../index.php");
        exit;
    } else {
        echo "Edit failed";
    }
}

if (isset($_POST['deleteApplicationBtn'])) {
    // Validate applicant_id
    if (!isset($_GET['applicant_id']) || !is_numeric($_GET['applicant_id'])) {
        echo "Invalid applicant ID";
        exit;
    }

    // Ensure session is started to access user_id
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in";
        exit;
    }

    // Retrieve applicant_id, applicant_name, and user_id
    $applicant_id = $_GET['applicant_id'];
    $user_id = $_SESSION['user_id'];

    $query = deleteApplication($pdo, $applicant_id, $user_id);

    if ($query) {
        // Redirect on success
        header("Location: ../index.php");
        exit;
    } else {
        // Display failure message
        echo "Deletion failed. Please try again.";
    }
}

