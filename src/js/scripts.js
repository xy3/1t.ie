jQuery(document).ready(function($) {
	$("#shorten_url_form").submit(function(e) {
		e.preventDefault()
		var form_data = $(this).serialize()

		$.post(
			'/api/addlink',
			form_data,
			function(data) {
				data = JSON.parse(data)
				if (data.success) {
					$("#shortened_link").text(data.full_link)
					$(".result").fadeIn(10)
				} else {
					$(".error-warning").text(data.message).fadeIn(10)
				}
			})
	})
})