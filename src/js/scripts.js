jQuery(document).ready(function($) {
	$(document).on("click", ".movies .play", function(){
		var id = $(this).data("id")

		buttons = {}
		add_btns = $(this).siblings('.add-btn')
		add_btns.each(function(index, btn) {
			buttons[$(btn).data('to')] = $(btn).find('input').is(':checked')
		})


		$.post(
			'/get',
			{
				action: 'get_movie_info',
				id: id
			},
			function(data) {
				data = JSON.parse(data)
				if (data.status == 1) {
					data.message['buttons'] = buttons
					lopen(data.message)
				}
			})
	})

	$(document).ajaxStop(function() {
		if ($('.movies').html().trim() == '') {
			msg = "No movies found"
			$('.movies').append(`<div><h3>${msg}</h3> <a href='/'><button class="red">See all movies</button></a></div>`)
		}
	});
})