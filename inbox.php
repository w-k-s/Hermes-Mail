<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mail</title>
		<link rel="shortcut icon" type="image/x-icon" href="res/favicon.ico"></link>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="css/Core.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Frame.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Inbox.css" type="text/css"></link>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/Inbox.js"></script>
	</head>
	<body>
		<div id="header">
			<div id="title">
				<table>
					<tr>
						<td><img src="res/logo_white.png" alt="logo" width="40" height="40"/></td>
						<td><h1>Hermes</h1></td>
					</tr>
				</table>
			</div>
			<div id="userPanel" >
				<div><h3>Waqqas Sheikh</h3></div>
				<div><a href="login.html">Log-Out</a></div>
			</div>
		</div>
		<div id="navigation">
			<ul id="nav">
				<li><a href="inbox.html" style="font-weight: bold;">Inbox</a></li>
				<li><a href="inbox.html">Sent Mail</a></li>
				<li><a href="inbox.html">Drafts</a></li>
				<li><a href="inbox.html">Deleted Mail</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="buttonPanel">
				<input type="button" class="button" value="Compose" onclick="location.href='compose.html'"/>
				<input type="button" class="button" id="delete" value="Delete" />
				<input type="button" class="button" value="Refresh" onclick="location.reload()"/>
				<input type="text" id="searchfield" value="Search"/>
			</div>
			<div id="emailPanel">
				<table id="table_inbox" style="background:white;width: 100%;">
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td>
							<td class="td_sender" onmouseover="link = true;">Sender McSender</td>
							<td class="td_subject" onmouseover="link = true;">I've sent you this email</td>
							<td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td>
						</tr>
				</table>
			</div>
			<div id="footer">
			 <p>
    			<a href="http://validator.w3.org/check?uri=referer"><img
      			src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
      			<a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" /></a>
			</p>
			</div>
		</div>
		<script type='text/javascript' src='js/Search.js'></script>
	</body>
</html>