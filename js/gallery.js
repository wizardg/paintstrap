$(function() {
	$(".gallery-download-link").click(function() {
		$("#download-dialog-ready").hide();
		$("#download-dialog-generating").show();

		linkId = $(this).attr("id")
		result = linkId.match(/^download\-link\-(\d+)$/);
		if (result.length < 2) {
			return;
		}
		themeId = result[1];
		
		themeName = $(".item .theme-" + themeId.toString() + " .theme-name").text();
		$("#download-dialog-theme-name").text(themeName);
		
		$.getJSON(BASE_URL + "api/package_by_id/" + themeId.toString(), {}, function(json) {
			if (json) {
				$("#download-dialog-form").attr("action", json.url_zip);
				
				$("#download-dialog-generating").hide();
				$("#download-dialog-ready").show();
			}
			else {
				alert(ERROR_MESSAGES.generate_error);
			}
		});
		
		$('#download-dialog').modal('show');
	})
});
