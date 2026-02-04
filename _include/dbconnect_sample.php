
<?php
if(session_status() === PHP_SESSION_NONE){
    // Session has not started, so start the session
    session_start();
}

$host = $_SERVER['HTTP_HOST'];

if ($host != "localhost:8888") {
    $con = mysqli_connect("localhost","uname","pword","dbase") or die ("Connection was not Established");
}else{
    $con = mysqli_connect("localhost","uname","pword","dbase") or die ("Connection was not Established");
}

// if ($host != "localhost:8888") {
//     $con = mysqli_connect("localhost","tryntmta_obuser","W__R-iut$@Ac&","tryntmta_obportal") or die ("Connection was not Established");
// }else{
//     $con = mysqli_connect("localhost","root","root","obrighton") or die ("Connection was not Established");
// }
?>