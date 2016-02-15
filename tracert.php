<?php

	if (isset($_POST['button1']))
	{
		set_time_limit(300);
		exec('tracert www.google.com', $out);
		
		$list = implode("<br>", $out);
		echo $list;
	}
?>

<html>
	<head>
		<title>Tracert</title>
	</head>
	<body>
		<form method="POST" action="tracert.php">
			<input type="submit" name="button1" value="My Button">
		</form>
	</body>
</html>