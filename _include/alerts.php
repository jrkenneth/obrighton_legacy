<?php
if(isset($_SESSION['expire'])){
    if(time() > $_SESSION['expire']){  
        unset($_SESSION['response']);
        unset($_SESSION['message']);
        unset($_SESSION['expire']);
    }
}

if(isset($_SESSION['response'])){
    $response = $_SESSION['response'];
    $message = $_SESSION['message']; // Success: Message has been sent. | Warning: Incorrect email/image format. | Error: Message sending failed.

    if($response == "success"){
?>
    <div class="alert alert-success alert-dismissible fade show">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>	
        <strong>Success!</strong> <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"><i class="fas fa-times"></i></button>
    </div>
<?php
    }else if($response == "error"){
?>
    <div class="alert alert-danger alert-dismissible fade show">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        <strong>Error!</strong> <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"><i class="fas fa-times"></i></button>
    </div>
<?php
    }
}
?>