<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('display_errors', 'On');
require_once '../../sites/globals.php';


?>

<html>
<body>
	<form action="$PHP_SELF" method="POST"> 
		<p>
			<span>Dirty</span>
			<input type="input" name="dirty">
		</p>
		
		<p>
			<span>Clean</span>
			<input type="input" name="clean">
		</p>
		
		<input type="submit" name="merge" value="MEGRGE">
	</form>
</body>
</html>

