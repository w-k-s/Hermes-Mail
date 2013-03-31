<?php
session_start();
session_destroy();
ob_start();
header('Location: ../login.php');
ob_end_clean();
?>