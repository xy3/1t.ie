jQuery(document).ready(function($) {
	$("#shorten_url_form").submit(function(e) {
		e.preventDefault()
		var form_data = $(this).serialize()
		$("#shorten-btn").addClass("animated loading u-center loading-white hide-text")

		$.post(
			'/api/addlink',
			form_data,
			function(data) {
				data = JSON.parse(data)
				if (data.success) {
					$("#shortened_link").text(data.full_link)
					$("#open_in_new_tab").attr("href", data.full_link)
					$(".result").fadeIn(10)
				} else {
					$(".shorten-error").show();
					$(".error-warning").text(data.message).fadeIn(10)
				}
			})
	})

	$(document).ajaxStop(function () {
		$("#shorten-btn").removeClass("animated loading u-center loading-white hide-text")
	})

	$(".copy").click(function(e){
		e.preventDefault()
		navigator.clipboard.writeText($("#shortened_link").text());
		$(".copy").text("Copied!")
		setTimeout(() => {
			$(".copy").text("Copy")
		}, 4000)
	})

	$(document).on("click", ".btn-close", function(e) {
		$(this).parent().hide();
	})
})