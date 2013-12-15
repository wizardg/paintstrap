$(function(){
	$(window).resize(function() {
		updateLargePreviewSize();
	});
	
	$("#design-changer").change(function() {
		updateLargePreviewContents($(this).val());
	});
	
	updateLargePreviewSize();
	updateLargePreviewContents(default_design);
});

function updateLargePreviewSize() {
	$("#large-preview").css("width", "100%").css("height", ($(window).height() - 51) + "px");
}

function updateLargePreviewContents(design) {
	$("#large-preview").attr("src", "preview_large" + 
			"?url_bootstrap_min_css=" + encodeURI(url_bootstrap_min_css) +
			"&design=" + encodeURI(design));
}
