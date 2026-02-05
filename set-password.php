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

    $message = "Enter your new password.";
    $response_class = "text-muted";
    $show_form = true;

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

    ensurePasswordResetTable($con);

    $token = $_GET['token'] ?? '';
    $uid = $_GET['uid'] ?? '';

    $token_hash = $token !== '' ? hash('sha256', $token) : '';
    $user_id = InputValidator::validateInteger($uid, 1);

    $reset_row = null;

    if ($token_hash !== '' && $user_id) {
        $lookup_stmt = $con->prepare("SELECT id, user_id, expires_at FROM password_resets WHERE user_id=? AND token_hash=? LIMIT 1");
        if ($lookup_stmt) {
            $lookup_stmt->bind_param("is", $user_id, $token_hash);
            $lookup_stmt->execute();
            $result = $lookup_stmt->get_result();
            $reset_row = $result->fetch_assoc();
            $lookup_stmt->close();
        }
    }

    if (!$reset_row) {
        $message = "This reset link is invalid or has expired.";
        $response_class = "text-danger";
        $show_form = false;
    } else {
        $expires_at = strtotime($reset_row['expires_at']);
        if ($expires_at < time()) {
            $message = "This reset link has expired. Please request a new one.";
            $response_class = "text-danger";
            $show_form = false;
        }
    }

    if ($show_form && isset($_POST['set_new_password'])) {
        CSRFProtection::checkToken($_POST['csrf_token'] ?? '');

        $new_password = $_POST['new_password'] ?? '';
        $confirmed_password = $_POST['confirmed_password'] ?? '';

        if ($new_password === '' || $confirmed_password === '') {
            $message = "All password fields are required.";
            $response_class = "text-danger";
        } elseif ($new_password !== $confirmed_password) {
            $message = "Passwords do not match. Please try again.";
            $response_class = "text-danger";
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $update_stmt = $con->prepare("UPDATE users SET password=? WHERE id=?");
            if ($update_stmt) {
                $update_stmt->bind_param("si", $new_hash, $user_id);
                if ($update_stmt->execute()) {
                    $update_stmt->close();

                    $delete_stmt = $con->prepare("DELETE FROM password_resets WHERE user_id=?");
                    if ($delete_stmt) {
                        $delete_stmt->bind_param("i", $user_id);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    }

                    AuditLog::log('PASSWORD_RESET', 'users', $user_id, null, array('timestamp' => date('Y-m-d H:i:s')), 0);

                    $message = "Password updated successfully. You can now log in.";
                    $response_class = "text-success";
                    $show_form = false;
                } else {
                    $update_stmt->close();
                    $message = "Password update failed. Please try again.";
                    $response_class = "text-danger";
                }
            } else {
                $message = "Password update failed. Please try again.";
                $response_class = "text-danger";
            }
        }
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

    <title>O.BRIGHTON EMPIRE LIMITED - Set Password</title>

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
                            <h3 class="title">Set New Password</h3>
                            <p class="<?php echo $response_class; ?>"><?php echo $message; ?></p>
                        </div>
                        <?php if ($show_form) { ?>
                        <form method="POST">
                            <?php CSRFProtection::tokenField(); ?>
                            <div class="mb-4">
                                <label class="mb-1 text-dark">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="mb-1 text-dark">Confirm New Password</label>
                                <input type="password" name="confirmed_password" class="form-control" required>
                            </div>
                            <div class="text-center mb-4">
                                <button type="submit" name="set_new_password" class="btn btn-primary btn-block">Update Password</button>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn-link text-primary">Back to Login</a>
                            </div>
                        </form>
                        <?php } else { ?>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Back to Login</a>
                        </div>
                        <?php } ?>
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
