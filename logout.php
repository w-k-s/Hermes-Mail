<?php
	//destroy session data
	session_start();
	session_destroy();

	//redirect to login.
	ob_start();
	header('Location: index.php');

	//stop output buffering.
	ob_end_clean();
?>