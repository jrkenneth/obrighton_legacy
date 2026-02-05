<?php
    include("_include/dbconnect.php");

    require_once '_include/DatabaseHelper.php';
    require_once '_include/InputValidator.php';
    require_once '_include/CSRFProtection.php';
    require_once '_include/Authorization.php';
    require_once '_include/AuditLog.php';

    $db = new DatabaseHelper($con);
    AuditLog::initialize($db);
    CSRFProtection::initialize();

    $this_user = 0;
    $agent_hidden = "";
    $editor_hidden = "";
    $sql_append = "";

    include("_include/route-handlers.php");

    $message = "Enter your email or user ID to receive a password reset link.";
    $response_class = "text-muted";

    function ensurePasswordResetTable($con) {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token_hash (token_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($con, $sql);
    }

    function isHostedEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '' || $host === 'localhost') {
            return false;
        }
        if (strpos($host, 'localhost:') === 0) {
            return false;
        }
        if ($host === '127.0.0.1' || $host === '::1') {
            return false;
        }
        return true;
    }

    function buildResetLink($token, $user_id) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_path = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        return $scheme . '://' . $host . $base_path . '/set-password.php?uid=' . urlencode($user_id) . '&token=' . urlencode($token);
    }

    if (isset($_POST['request_password_reset'])) {
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        ensurePasswordResetTable($con);

        $identifier = InputValidator::sanitizeText($_POST['identifier'] ?? '', 255);
        $lookup_stmt = $con->prepare("SELECT id, email, first_name, last_name FROM users WHERE email=? OR user_id=? LIMIT 1");

        if ($lookup_stmt) {
            $lookup_stmt->bind_param("ss", $identifier, $identifier);
            $lookup_stmt->execute();
            $result = $lookup_stmt->get_result();
            $user = $result->fetch_assoc();
            $lookup_stmt->close();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);
                $expires_at = date('Y-m-d H:i:s', time() + 3600);

                $delete_stmt = $con->prepare("DELETE FROM password_resets WHERE user_id=?");
                if ($delete_stmt) {
                    $delete_stmt->bind_param("i", $user['id']);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }

                $insert_stmt = $con->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("iss", $user['id'], $token_hash, $expires_at);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }

                $reset_link = buildResetLink($token, $user['id']);

                $is_hosted = isHostedEnvironment();
                if ($is_hosted) {
                    $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                    $subject = "Password Reset Request";
                    $body = "<p>Hello " . htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') . ",</p>" .
                        "<p>We received a request to reset your password. Use the link below to set a new password:</p>" .
                        "<p><a href='" . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . "'>Reset Password</a></p>" .
                        "<p>This link expires in 1 hour. If you did not request this, please ignore this email.</p>";

                    sendMail('no-reply@obrightonempire.com', 'O.Brighton Empire', $user['email'], $full_name, $subject, $body);
                } else {
                    error_log("PASSWORD RESET LINK (LOCAL): " . $reset_link);
                }

                AuditLog::log('PASSWORD_RESET_REQUEST', 'users', $user['id'], null, array('timestamp' => date('Y-m-d H:i:s')), 0);
            }
        }

        $message = "If the account exists, a reset link has been sent. Please check your email.";
        $response_class = "text-success";
    }
?>

<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="robots" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">

    <title>O.BRIGHTON EMPIRE LIMITED - Forgot Password</title>

    <link rel="shortcut icon" type="image/png" href="images/favicon.png">
    <link href="vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 col-md-12 col-sm-12 mx-auto align-self-center">
                    <div class="login-form">
                        <div class="text-center">
                            <h3 class="title">Forgot Password</h3>
                            <p class="<?php echo $response_class; ?>"><?php echo $message; ?></p>
                        </div>
                        <form method="POST">
                            <?php CSRFProtection::tokenField(); ?>
                            <div class="mb-4">
                                <label class="mb-1 text-dark">Email/User ID</label>
                                <input type="text" name="identifier" class="form-control" placeholder="Enter your Email address or User ID" required>
                            </div>
                            <div class="text-center mb-4">
                                <button type="submit" name="request_password_reset" class="btn btn-primary btn-block">Send Reset Link</button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn-link text-primary">Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="pages-left h-100">
                        <div class="login-content">
                            <a href="index.php"><img src="images/logo-full.png" class="mb-3 logo-dark" alt=""></a>
                            <a href="index.php"><img src="images/logi-white.png" class="mb-3 logo-light" alt=""></a>
                            <p>Find Your Dream Home</p>
                        </div>
                        <div class="login-media text-center">
                            <img src="images/log_pic.png" style="width: 400px;" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="vendor/global/jquery.min.js"></script>
    <script src="vendor/global/global.min.js"></script>
    <script src="vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="js/deznav-init.js"></script>
    <script src="js/demo.js"></script>
    <script src="js/custom.js"></script>
</body>

</html>
