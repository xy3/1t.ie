<?php 
include 'components/head.php';
include 'components/sidebar.php';
include 'components/mobile_menu.php';
include 'components/report_issue_box.php';
include 'components/lightbox.php';
?>



<div class="container">
	<div class="intro">
		<?php include 'components/logo.php'; ?>
		<?php if ($request->table != 'home'): ?>
			<div class="page-title">
				<h1><?= ucfirst($request->table) ?></h1>
				<h4 class="user-table-count">You have <?= user_table_row_count($request->table) ?> movies in <b><?= ucfirst($request->table) ?></b>.</h4>
			</div>
			<?php else: ?>
				<div class="page-title">
					<h1></h1>
					<?php if ($_SESSION['username'] != "fathead"): ?>
						<div class="patreon">
							<a href="https://www.patreon.com/EE3me" target="_blank">
								<img src="src/img/patreon.png" alt="Support EE3 on Patreon" width="190px" style="border-radius: 5px;">
							</a>
							<div class="supporters">
								<h4>VIP Supporters: </h4>
								<p>Lemon</p>
							</div>
						</div>
					<?php endif ?>
				</div>
				<div class="sortby">
					<ul>
						<li data-by='datetime' class="active">Added recently</li>
						<li data-by='year'>Newest</li>
						<li data-by='glow'>Admin Picks</li>
						<li data-by='rating'>Highly rated</li>
					</ul>
				</div>
			<?php endif ?>
		</div>

		<!-- Start main movie div -->
		<div class="most" id="moviediv">

			<div id="jwpl"></div>
			<div class="movies" <?= "data-table=$request->table" ?>>
			</div>
		</div>
	</div>


	<?php 
	scripts([
		'jquery-3.4.0.min.js',
		'jquery.browser.detection.min.js',
		'lazyload.min.js',
		'sidebar.js',
		'ajax.functions.js',
		$page_script,
		'functions.js'
	]);
	?>

	<script> 
		setActive("<?= $request->table ?>"); // Set the active page in the sidebar
	</script>

</body>
</html>
