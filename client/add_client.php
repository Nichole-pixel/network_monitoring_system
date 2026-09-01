<?php
session_start();
include('../db.php');

$message = "";
$mac_address = "";
$pc_no = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mac_address = trim($_POST['mac_address'] ?? '');
    $pc_no = trim($_POST['pc_no'] ?? '');

    // Normalize MAC address
    $mac_address = strtoupper(str_replace('-', ':', $mac_address));

    // Basic validation
    if ($mac_address === '' || $pc_no === '') {
        $message = "Error: All fields are required.";
    } else {
        // Optional: validate MAC address format
        if (!preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $mac_address)) {
            $message = "Error: Invalid MAC Address format.";
        } else {
            // CHECK DUPLICATE MAC
            $checkMac = $conn->prepare("SELECT client_id FROM client WHERE mac_address = ?");
            if (!$checkMac) {
                $message = "Database error: Unable to prepare MAC check.";
            } else {
                $checkMac->bind_param("s", $mac_address);
                $checkMac->execute();
                $mac_result = $checkMac->get_result();

                // CHECK DUPLICATE PC NO
                $checkPc = $conn->prepare("SELECT client_id FROM client WHERE pc_no = ?");
                if (!$checkPc) {
                    $message = "Database error: Unable to prepare PC check.";
                } else {
                    $checkPc->bind_param("s", $pc_no);
                    $checkPc->execute();
                    $pc_result = $checkPc->get_result();

                    if ($mac_result->num_rows > 0) {
                        $message = "Error: MAC Address already exists.";
                    } elseif ($pc_result->num_rows > 0) {
                        $message = "Error: PC No. already exists.";
                    } else {
                        // INSERT CLIENT
                        $stmt = $conn->prepare("INSERT INTO client (mac_address, pc_no) VALUES (?, ?)");
                        if (!$stmt) {
                            $message = "Database error: Unable to prepare insert.";
                        } else {
                            $stmt->bind_param("ss", $mac_address, $pc_no);

                            if ($stmt->execute()) {
                                header("Location: client_dashboard.php?success=1");
                                exit();
                            } else {
                                $message = "Error adding client.";
                            }

                            $stmt->close();
                        }
                    }

                    $checkPc->close();
                }

                $checkMac->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Client</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('../images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 420px;
            padding: 25px;
            border-radius: 16px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: fadeIn 0.6s ease;
            color: #fff;
        }

        input[type=text] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            outline: none;
            background: rgba(255,255,255,0.9);
            color: #000;
        }

        button {
            width: 100%;
            padding: 10px 15px;
            background: linear-gradient(135deg, #00c853, #64dd17);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        button:hover {
            transform: scale(1.03);
            opacity: 0.9;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.15);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .msg {
            margin-bottom: 10px;
            font-weight: bold;
            color: #ff4d4d;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="client_dashboard.php" class="back-btn">⬅ Back to Client Dashboard</a>

        <h2>Add Client</h2>

        <?php if ($message !== ''): ?>
            <div class="msg">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>MAC Address</label>
            <input
                type="text"
                name="mac_address"
                value="<?php echo htmlspecialchars($mac_address); ?>"
                required
            >

            <label>PC No.</label>
            <input
                type="text"
                name="pc_no"
                value="<?php echo htmlspecialchars($pc_no); ?>"
                required
            >

            <button type="submit">Add Client</button>
        </form>
    </div>
</body>
</html>