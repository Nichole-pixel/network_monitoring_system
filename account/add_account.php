<?php  
include('../db.php');

$error = "";

// Initialize variables
$lname = $fname = $mname = $ename = $email = $username = $position = "";
$acc_status = "active";

// ✅ FORCE ROLE TO ADMIN
$role = "admin";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $lname = trim($_POST['lname'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $ename = trim($_POST['ename'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $acc_status = $_POST['acc_status'] ?? 'active';
    $position = trim($_POST['position'] ?? '');

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Invalid email format.";

    } else {

        $check = $conn->prepare("SELECT * FROM Account WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Email or Username already exists.";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO Account
                (lname,fname,mname,ename,email,username,password,acc_status,position,role)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "ssssssssss",
                $lname,
                $fname,
                $mname,
                $ename,
                $email,
                $username,
                $password,
                $acc_status,
                $position,
                $role // always admin
            );

            if ($stmt->execute()) {

                header("Location: account_dashboard.php?added=1");
                exit();

            } else {
                $error = "Error adding account.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Account</title>

<style>
/* KEEP YOUR STYLE */
* { box-sizing:border-box; margin:0; padding:0; font-family:'Segoe UI',sans-serif; }

body {
min-height:100vh;
display:flex;
justify-content:center;
align-items:flex-start;
background:url('../images/background.jpg') no-repeat center center fixed;
background-size:cover;
padding:40px;
position:relative;
}

body::before {
content:'';
position:absolute;
inset:0;
background:linear-gradient(rgba(0,0,0,0.30),rgba(0,0,0,0.20));
z-index:0;
}

.wrapper { position:relative; z-index:1; width:100%; max-width:600px; }

h1 {
text-align:center;
color:#fff;
margin-bottom:20px;
}

.form-container {
background:rgba(255,255,255,0.15);
backdrop-filter:blur(12px);
border-radius:18px;
padding:25px;
}

label { color:#eee; font-size:13px; }

input, select {
width:100%;
height:44px;
padding:0 12px;
margin-bottom:15px;
border-radius:10px;
border:none;
outline:none;
background:rgba(255,255,255,0.2);
color:#fff;
}

button {
width:100%;
padding:12px;
border:none;
border-radius:10px;
background:linear-gradient(135deg,#00c853,#64dd17);
color:#fff;
font-weight:bold;
cursor:pointer;
}

.error {
background:rgba(255,0,0,0.25);
padding:10px;
border-radius:8px;
margin-bottom:10px;
text-align:center;
}
</style>
</head>

<body>

<div class="wrapper">
<h1>Add New Account</h1>

<div class="form-container">

<?php if (!empty($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" autocomplete="off">

<label>Last Name:</label>
<input type="text" name="lname" required>

<label>First Name:</label>
<input type="text" name="fname" required>

<label>Middle Name:</label>
<input type="text" name="mname">

<label>Extension Name:</label>
<input type="text" name="ename">

<label>Email:</label>
<input type="email" name="email" required autocomplete="off">

<label>Username:</label>
<input type="text" name="username" required autocomplete="off">

<label>Password:</label>
<input type="password" name="password" required autocomplete="new-password">

<label>Account Status:</label>
<select name="acc_status">
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>

<label>Position:</label>
<input type="text" name="position" required>

<!-- ✅ SHOW ROLE BUT LOCKED -->
<label>Role:</label>
<input type="text" value="Admin" disabled>

<button type="submit">Add Account</button>

</form>
</div>
</div>

</body>
</html>

<?php $conn->close(); ?>