<?php 
include 'components/head.php';
?>

<div class="intro">
	<?php include 'components/logo.php'; ?>
</div>

<div class="login_register_div">
	<div class="tab-btns">
		<button id="login_tab" class="red">LOGIN</button>
		<button id="register_tab" class="red inactive">REGISTER</button>
	</div>

	<div class="tab login">
		<h1>Login</h1>
		<form id="login_form" action="." method="post">
			<input name="user" type="text" placeholder="Username (Not email)" autofocus>
			<input name="pass" type="password" placeholder="Password">
			<button class="red" type="submit">LOGIN</button>
		</form>
	</div>

	<div class="tab register">
		<h1>Register</h1>
		<form id='register_form' action="#" method="POST">
			<input type="email" name='email' placeholder="Email (optional)">
			<input type="text" name='username' placeholder="Username" maxlength="32" required>
			<input type="password" name='password' placeholder="Password" id='password' required>
			<input type="password" name='confirm_pass' placeholder="Confirm Password" required>
			<p>The invite code is the password you usually use for EE3</p>
			<input type="text" name='code' placeholder="Invite Code" required>
			<button class="red" type="submit">REGISTER</button>
		</form>
	</div>

	<p id='result'></p>

</div>



<?php 
scripts([
	'jquery-3.4.0.min.js',
	'ajax.functions.js',
	'functions.js'
]);
?>
</body>
</html>