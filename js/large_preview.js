$(function(){
	$("body").append('<div style="width: 48px; height: 48px; border: 1px solid #999999; background-color: #ffffff; padding: 7px 0 0 9px;">' +
			'<span class="glyphicon glyphicon-list-alt" style="font-size: 24px; color: #000000;"></span>' +
			'<select id="change-design" name="">' +
			'<option value="carousel">Carousel</option>' +
			'<option value="grid">Grid</option>' +
			'</select>' +
			'</div>');
	
	$("#change-design").change(function() {
		alert("1");
	});
});
