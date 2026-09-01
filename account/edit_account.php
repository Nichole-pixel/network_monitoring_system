<?php
include('../db.php');  // Include the database connection

if (isset($_GET['id'])) {
    $acc_id = $_GET['id'];

    // Fetch account details from the database
    $sql = "SELECT * FROM Account WHERE acc_id = $acc_id";
    $result = $conn->query($sql);
    $account = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get updated form data
        $lname = $_POST['lname'];
        $fname = $_POST['fname'];
        $mname = $_POST['mname'];
        $ename = $_POST['ename'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Encrypt password
        $acc_status = $_POST['acc_status'];
        $position = $_POST['position'];
        $role = $_POST['role'];

        // Update account details
        $sql = "UPDATE Account SET lname='$lname', fname='$fname', mname='$mname', ename='$ename', 
                username='$username', password='$password', acc_status='$acc_status', 
                position='$position', role='$role' WHERE acc_id=$acc_id";

        if ($conn->query($sql) === TRUE) {
            echo "Account updated successfully";
            header("Location: account_dashboard.php");  // Redirect to dashboard
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} else {
    echo "Account not found.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <style>

/* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
}

/* Background */
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    background: url('../images/background.jpg')no-repeat center center fixed;
    background-size: cover;
    padding: 40px;
    position: relative;
}

/* Overlay */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient( rgba(0,0,0,0.45),rgba(0,0,0,0.20));
    z-index: 0;
}

/* Wrapper */
.wrapper {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 600px;
    animation: fadeIn 0.8s ease;
}

/* Title */
h1 {
    text-align: center;
    color: #fff;
    font-size: 30px;
    margin-bottom: 20px;
    text-shadow: 0 4px 10px rgba(0,0,0,0.6);
}

/* Glass Form Container */
.form-container {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    padding: 25px;
    animation: slideUp 0.6s ease;
}

/* Labels */
label {
    display: block;
    color: #eee;
    margin-bottom: 5px;
    font-size: 13px;
}

/* Inputs */
input,
select {
    width: 100%;
    height: 44px;
    padding: 0 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.2);
    color: #fff;
    backdrop-filter: blur(5px);
    transition: 0.2s;
}

/* Focus */
input:focus,
select:focus {
    background: rgba(255,255,255,0.3);
    box-shadow: 0 0 6px rgba(255,255,255,0.5);
}

/* Buttons */
button,
.btn-back {
    width: 100%;
    height: 45px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #00c853, #64dd17);
    color: white;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
}

/* Hover */
button:hover,
.btn-back:hover {
    transform: scale(1.03);
    opacity: 0.9;
}

/* Back Button */
.back-wrapper {
    margin-bottom: 15px;
}

/* Animations */

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from {
        transform: translateY(40px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

</style>
</head>
<body>

<div class="wrapper">

<h1>Edit Account</h1>

<div class="back-wrapper">
<a href="account_dashboard.php" class="btn-back">
⬅ Back to Dashboard
</a>
</div>

<div class="form-container">

<form method="POST">

<label>Last Name:</label>
<input type="text" name="lname"
value="<?php echo $account['lname']; ?>" required>

<label>First Name:</label>
<input type="text" name="fname"
value="<?php echo $account['fname']; ?>" required>

<label>Middle Name:</label>
<input type="text" name="mname"
value="<?php echo $account['mname']; ?>">

<label>Extension Name:</label>
<input type="text"
name="ename"
value="<?php echo $account['ename']; ?>">

<label>Email Address:</label>
<input type="email"
name="email"
value="<?php echo $account['email']; ?>"
required>


<label>Username:</label>
<input type="text" name="username"
value="<?php echo $account['username']; ?>" required>

<label>Password:</label>
<input type="password" name="password" required>

<label>Account Status:</label>
<select name="acc_status" required>

<option value="active"
<?php if ($account['acc_status'] == 'active') echo 'selected'; ?>>
Active
</option>

<option value="inactive"
<?php if ($account['acc_status'] == 'inactive') echo 'selected'; ?>>
Inactive
</option>

</select>

<label>Position:</label>
<input type="text" name="position"
value="<?php echo $account['position']; ?>" required>

<label>Role:</label>
<select name="role" required>

<option value="admin"
<?php if ($account['role'] == 'admin') echo 'selected'; ?>>
Admin
</option>

<option value="user"
<?php if ($account['role'] == 'user') echo 'selected'; ?>>
User
</option>

</select>

<button type="submit">
Update Account
</button>

</form>

</div>
</div>
</body>
</html>

<?php
$conn->close();  // Close the database connection
?>