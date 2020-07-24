	<?php 
	include 'components/header.php';
	?>



	<div class="container">
		<div class="intro">
			<?php include 'components/logo.php'; ?>
			<div class="page-title">
				<h1>Control Panel</h1>
			</div>
		</div>


	</div>


	<?php 
	scripts([
		'jquery-3.4.0.min.js',
		'sidebar.js',
		'ajax.functions.js',
		'functions.js',
	]);
	?>
</body>
</html>
