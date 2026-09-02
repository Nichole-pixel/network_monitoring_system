<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../includes/config.php';

$error = '';
$success = '';

$name = '';
$website = '';

/*
|--------------------------------------------------------------------------
| Escape output
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Normalize and validate website domain
|--------------------------------------------------------------------------
| Examples accepted:
| facebook.com
| www.facebook.com
| https://facebook.com
| http://www.facebook.com
|
| Stored as:
| facebook.com
|--------------------------------------------------------------------------
*/
function normalizeDomain($input)
{
    $input = trim(strtolower($input));

    if ($input === '') {
        return '';
    }

    // Remove spaces
    $input = preg_replace('/\s+/', '', $input);

    // Remove trailing slash
    $input = rtrim($input, '/');

    // Add protocol if missing
    if (!preg_match('/^https?:\/\//i', $input)) {
        $input = 'http://' . $input;
    }

    // Get hostname
    $host = parse_url($input, PHP_URL_HOST);

    if (!$host) {
        return '';
    }

    // Remove "www."
    $host = preg_replace('/^www\./i', '', $host);

    // Remove trailing dot
    $host = rtrim($host, '.');

    // Validate domain
    if (
        !filter_var(
            $host,
            FILTER_VALIDATE_DOMAIN,
            FILTER_FLAG_HOSTNAME
        )
    ) {
        return '';
    }

    // Basic domain check
    if (
        strpos($host, '.') === false ||
        strlen($host) < 4
    ) {
        return '';
    }

    return $host;
}

/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['policy_name'] ?? '');
    $websiteInput = trim($_POST['website'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validate Policy Name
    |--------------------------------------------------------------------------
    */
    if ($name === '') {

        $error = 'Policy name is required.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Validate Website
        |--------------------------------------------------------------------------
        */
        $website = normalizeDomain($websiteInput);

        if ($website === '') {

            $error = 'Please enter a valid website domain.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE
            |--------------------------------------------------------------------------
            */
            $check = $conn->prepare("
                SELECT p.policy_id
                FROM policy p
                LEFT JOIN policy_domains pd
                    ON p.policy_id = pd.policy_id
                WHERE LOWER(p.policy_name) = LOWER(?)
                   OR LOWER(p.website) = LOWER(?)
                   OR LOWER(pd.domain) = LOWER(?)
                LIMIT 1
            ");

            if (!$check) {

                $error = 'Database error: ' . $conn->error;

            } else {

                $check->bind_param(
                    'sss',
                    $name,
                    $website,
                    $website
                );

                if (!$check->execute()) {

                    $error = 'Failed to check existing policy: ' . $check->error;

                } else {

                    $result = $check->get_result();

                    if ($result->num_rows > 0) {

                        $error = 'Policy name or website already exists.';

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | START TRANSACTION
                        |--------------------------------------------------------------------------
                        */
                        $conn->begin_transaction();

                        try {

                            /*
                            |--------------------------------------------------------------------------
                            | INSERT INTO POLICY
                            |--------------------------------------------------------------------------
                            */
                            $stmt = $conn->prepare("
                                INSERT INTO policy
                                (
                                    policy_name,
                                    website
                                )
                                VALUES
                                (?, ?)
                            ");

                            if (!$stmt) {
                                throw new Exception(
                                    'Failed to prepare policy query: ' . $conn->error
                                );
                            }

                            $stmt->bind_param(
                                'ss',
                                $name,
                                $website
                            );

                            if (!$stmt->execute()) {
                                throw new Exception(
                                    'Policy save failed: ' . $stmt->error
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | GET NEW POLICY ID
                            |--------------------------------------------------------------------------
                            */
                            $policy_id = $conn->insert_id;

                            /*
                            |--------------------------------------------------------------------------
                            | INSERT INTO POLICY_DOMAINS
                            |--------------------------------------------------------------------------
                            */
                            $domainStmt = $conn->prepare("
                                INSERT INTO policy_domains
                                (
                                    policy_id,
                                    domain
                                )
                                VALUES
                                (?, ?)
                            ");

                            if (!$domainStmt) {
                                throw new Exception(
                                    'Failed to prepare domain query: ' . $conn->error
                                );
                            }

                            $domainStmt->bind_param(
                                'is',
                                $policy_id,
                                $website
                            );

                            if (!$domainStmt->execute()) {
                                throw new Exception(
                                    'Domain save failed: ' . $domainStmt->error
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | COMMIT
                            |--------------------------------------------------------------------------
                            */
                            $conn->commit();

                            /*
                            |--------------------------------------------------------------------------
                            | REDIRECT
                            |--------------------------------------------------------------------------
                            */
                            header(
                                'Location: policy_dashboard.php?added=1'
                            );
                            exit();

                        } catch (Exception $e) {

                            /*
                            |--------------------------------------------------------------------------
                            | ROLLBACK IF SOMETHING FAILED
                            |--------------------------------------------------------------------------
                            */
                            $conn->rollback();

                            $error = $e->getMessage();
                        }
                    }
                }

                $check->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Website Policy</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f3f6fa;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 625px;

            background: #ffffff;

            border-radius: 20px;

            padding: 45px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 30px;
            color: #111827;
            margin-bottom: 14px;
        }

        .header p {
            font-size: 16px;
            color: #6b7280;
        }

        .message {
            padding: 14px 16px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-size: 15px;

            line-height: 1.5;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #dc2626;
        }

        .success {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;

            font-size: 16px;
            font-weight: 600;

            color: #1f2937;

            margin-bottom: 9px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 16px;
            top: 50%;

            transform: translateY(-50%);

            font-size: 18px;

            color: #6b7280;
        }

        input {
            width: 100%;

            height: 55px;

            border: 1px solid #d1d5db;

            border-radius: 11px;

            padding: 0 16px 0 52px;

            font-size: 16px;

            outline: none;

            transition: 0.2s;

            color: #111827;

            background: #ffffff;
        }

        input:focus {
            border-color: #087cf5;

            box-shadow:
                0 0 0 3px rgba(8, 124, 245, 0.12);
        }

        input::placeholder {
            color: #9ca3af;
        }

        .help-text {
            margin-top: 8px;

            font-size: 14px;

            color: #6b7280;
        }

        .button {
            width: 100%;

            height: 55px;

            border: none;

            border-radius: 10px;

            background: #087cf5;

            color: white;

            font-size: 17px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;
        }

        .button:hover {
            background: #0668d4;
        }

        .button:active {
            transform: scale(0.99);
        }

        .back {
            display: block;

            text-align: center;

            margin-top: 25px;

            color: #4b5563;

            text-decoration: none;

            font-size: 16px;
        }

        .back:hover {
            color: #087cf5;
        }

        .example-box {
            margin-top: 20px;

            padding: 14px 16px;

            background: #f8fafc;

            border-radius: 10px;

            font-size: 14px;

            color: #64748b;

            line-height: 1.6;
        }

        .example-box strong {
            color: #374151;
        }

        @media (max-width: 600px) {

            .container {
                padding: 30px 22px;
            }

            .header h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Add Website Policy</h1>

        <p>
            Create a policy to restrict access to a website.
        </p>

    </div>


    <?php if ($error !== ''): ?>

        <div class="message error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <?php if ($success !== ''): ?>

        <div class="message success">
            <?= e($success) ?>
        </div>

    <?php endif; ?>


    <form
        method="POST"
        action=""
        autocomplete="off"
    >

        <!-- POLICY NAME -->

        <div class="form-group">

            <label for="policy_name">
                Policy Name
            </label>

            <div class="input-wrapper">

                <span class="input-icon">
                    📋
                </span>

                <input
                    type="text"
                    id="policy_name"
                    name="policy_name"
                    value="<?= e($name) ?>"
                    placeholder="Example: Block Facebook"
                    maxlength="100"
                    required
                >

            </div>

        </div>


        <!-- WEBSITE -->

        <div class="form-group">

            <label for="website">
                Website Domain
            </label>

            <div class="input-wrapper">

                <span class="input-icon">
                    🌐
                </span>

                <input
                    type="text"
                    id="website"
                    name="website"
                    value="<?= e($website !== '' ? $website : $websiteInput ?? '') ?>"
                    placeholder="Example: facebook.com"
                    maxlength="255"
                    required
                >

            </div>

            <div class="help-text">
                Enter the domain only, such as facebook.com or youtube.com.
            </div>

        </div>


        <!-- EXAMPLES -->

        <div class="example-box">

            <strong>Examples:</strong><br>

            ✔ facebook.com<br>
            ✔ youtube.com<br>
            ✔ tiktok.com<br>
            ✔ example.com

        </div>


        <br>


        <!-- SUBMIT -->

        <button
            type="submit"
            class="button"
        >
            Add Blocking Policy
        </button>

    </form>


    <!-- BACK -->

    <a
        href="policy_dashboard.php"
        class="back"
    >
        ← Back to Policy Dashboard
    </a>

</div>

</body>

</html>

<?php

$conn->close();

?>