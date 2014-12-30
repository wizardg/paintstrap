var COLOR_NUM = 21;
var COLOR_INPUT_NUM = 7;

var last_api_type = "";
var last_kuler_id = -1;

var kuler_api_cache = {
	"kuler": {},
	"colourlovers": {}
};
var package_cache = {};

var initial_selected_colors = {
	"default": [0, 1, 2, 3, 4, 0, 2],
	"default_2_colors": [0, 0, 0, 1, 1, 0, 0],
	"default_3_colors": [0, 1, 1, 1, 2, 0, 1],
	"default_4_colors": [0, 1, 1, 2, 3, 0, 1],
	"kuler_example_1": [0, 1, 2, 3, 4, 0, 2],
	"kuler_example_2": [0, 1, 3, 2, 3, 0, 2],
	"kuler_example_3": [0, 1, 2, 3, 4, 0, 2],
	"colourlovers_example_1": [0, 1, 2, 3, 4, 0, 2],
	"colourlovers_example_2": [4, 3, 2, 1, 0, 4, 2],
	"colourlovers_example_3": [3, 1, 2, 0, 2, 3, 4]
};

var initial_selected_color_id = "";


$(document).ready(function(){
	var i, j;
	
	for (i = 0; i < COLOR_INPUT_NUM; i++) {
		for (j = 0; j < COLOR_NUM; j++) {
			$("#color-" + i + "-" + j).click(getSelectedColorChangeCallback(i, j));
		}
	}

	$("form#input-kuler").bind("submit", function() {
		initial_selected_color_id = "default";
		submitInputKulerID();
		return false;
	});
	
	$("#back-to-input-kuler-button").click(function() {
		updateStep(1);
	});
	
	$("#forward-to-download-button").click(function() {
		$("#select-color-container .buttons button").attr("disabled", "disabled");
		$("#select-color-container .buttons .loading-image").show();

		param = $("#selected-color-form").serialize();
		$.getJSON(BASE_URL + "api/package?" + $("#selected-color-form").serialize(), {}, function(json) {
			if (json) {
				package_cache[param] = json;

				applyDownloadLink(json);
				postShowDownloadPage();
			}
			else {
				enableSelectColorPageButtons();
				alert(ERROR_MESSAGES.generate_error);
			}
		});
		return false;
	});

	$("#back-to-select-color-button").click(function() {
		updateStep(2);
	});
	
	$("input[name=api_type]").change(function() {
		changeColorSchemeAPI($(this).val());
	});

	$("#open-kuler").click(function() {
		window.open("http://kuler.adobe.com/", "_blank");
	});
	
	$("#open-colourlovers").click(function() {
		window.open("http://www.colourlovers.com/", "_blank");
	});
	
	$(".input-kuler-example").click(function() {
		changeColorSchemeAPI("kuler");
		
		input_id = $(this).find("span.kuler-id").text();
		initial_selected_color_id = "kuler_example_" + $(this).find("span.example-no").text();
		
		$("#input-kuler-id").val(input_id);
		$("input[name=api_type]").val(["kuler"]);
		submitInputKulerID();
	});
	
	$(".input-colourlovers-example").click(function() {
		changeColorSchemeAPI("colourlovers");
		
		input_id = $(this).find("span.colourlovers-id").text();
		initial_selected_color_id = "colourlovers_example_" + $(this).find("span.example-no").text();
		
		$("#input-colourlovers-id").val(input_id);
		$("input[name=api_type]").val(["colourlovers"]);
		submitInputKulerID();
	});
	
	$("#open-large-preview").click(function() {
		openLargePreview();
	});
	
	updateStep(1);
	enableInputKulerPageButtons();
	enableSelectColorPageButtons();

	$("input[name=api_type]").val([arg_api_type]);
	changeColorSchemeAPI(arg_api_type);

	switch (arg_api_type) {
		case "kuler":
			$("#input-kuler-id").val(arg_cs_id);
			$("#input-colourlovers-id").val("");
			break;
		case "colourlovers":
			$("#input-kuler-id").val("");
			$("#input-colourlovers-id").val(arg_cs_id);
			break;
		default:
			$("#input-kuler-id").val("");
			$("#input-colourlovers-id").val("");
			break;
	}
	
	$("#input-colourlovers-id").focus();
});
	
function changeColorSchemeAPI(api_type) {
	$("p.input-api-particular").each(function() {
		$(this).find("input").attr("disabled", "disabled");
	});

	switch (api_type) {
		case "kuler":
			container = $("#input-kuler-id-container p.input-api-particular");
			container.find("input").removeAttr("disabled");
			break;
		case "colourlovers":
			container = $("#input-colourlovers-id-container p.input-api-particular");
			container.find("input").removeAttr("disabled");
			break;
	}
}

function submitInputKulerID() {
	var input_id;
	
	$("#input-kuler-id-container").removeClass("has-error");
	$("#input-kuler-id-error").text("");

	$("#input-colourlovers-id-container").removeClass("has-error");
	$("#input-colourlovers-id-error").text("");
	
	api_type = $("input[name=api_type]:checked").val();
	switch (api_type) {
		case "kuler":
			input_id = $.trim($("#input-kuler-id").val());
			$("#input-kuler-id").val(input_id);
		
			input_id = convertZenkakuToHankaku(input_id);
			if (input_id == "") {
				inputKulerIDError(api_type, ERROR_MESSAGES.required);
				return;
			}
		
			if (input_id.match(/^\d{1,10}$/)) {
				// do nothing
			}
			else if (input_id.match(/^https?\:\/\/kuler\.adobe\.com\/.*\-(\d+)\/?$/)) {
				input_id = RegExp.$1;
			}
			else {
				inputKulerIDError(api_type, ERROR_MESSAGES.invalid_input);
				return;
			}
		
			break;
			
		case "colourlovers":
			input_id = $.trim($("#input-colourlovers-id").val());
			$("#input-colourlovers-id").val(input_id);

			input_id = convertZenkakuToHankaku(input_id);
			if (input_id == "") {
				inputKulerIDError(api_type, ERROR_MESSAGES.required);
				return;
			}

			if (input_id.match(/^\d{1,10}$/)) {
				// do nothing
			}
			else if (input_id.match(/^https?\:\/\/www\.colourlovers\.com\/palette\/(\d+)/)) {
				input_id = RegExp.$1;
			}
			else {
				inputKulerIDError(api_type, ERROR_MESSAGES.invalid_input);
				return;
			}

			break;
			
		default:
			return;
	}
	
	$("#input-kuler-container .buttons button").attr("disabled", "disabled");
	$("#input-kuler-container .buttons .loading-image").show();

	if (kuler_api_cache[api_type][input_id]) {
		$("#selected-color-form input[name=api_type]").val(api_type);
		$("#selected-color-form input[name=id]").val(input_id);
		applySelectColorTable(kuler_api_cache[api_type][input_id]);
		postShowSelectColorPage();
	}
	else {
		data = {
			api_type: api_type,
			id: input_id
		};
		$.getJSON(BASE_URL + "api/get_color_scheme", data, function(json) {
			if (json) {
				if (json.record != undefined && json.record > 0) {
					kuler_api_cache[api_type][input_id] = json;

					$("#selected-color-form input[name=api_type]").val(api_type);
					$("#selected-color-form input[name=id]").val(input_id);

					applySelectColorTable(json);
					postShowSelectColorPage();
				}
				else {
					enableInputKulerPageButtons();
					inputKulerIDError(api_type, ERROR_MESSAGES.color_scheme_not_exists);
				}
			}
			else {
				enableInputKulerPageButtons();
				inputKulerIDError(api_type, ERROR_MESSAGES.api_call_error);
			}
		});
	}
}

function inputKulerIDError(api_type, msg) {
	$("#input-" + api_type + "-id-error").text(msg);
	$("#input-" + api_type + "-id-container").addClass("has-error");
	$("#input-" + api_type + "-id").focus();
}

function convertZenkakuToHankaku(str) {
	return str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function(s) {
	    return String.fromCharCode(s.charCodeAt(0) - 65248);
	});
}

function applySelectColorTable(json) {
	var i, j;

	for (i = 0; i < COLOR_INPUT_NUM; i++) {
		for (j = 0; j < 5; j++) {
			elements = $("#color-" + i + "-" + j);
			elements.each(function() {
				$(this).find("input").attr("value", "FFFFFF");
				$(this).hide();
			});
		}
	}
	
	for (i = 0; i < COLOR_INPUT_NUM; i++) {
		for (j = 0; j < json.hex.length; j++) {
			hex = json.hex[j];
			
			elements = $("#color-" + i + "-" + j);
			elements.find("input").attr("value", hex);
			elements.show();
		}
	}

	for (i = 0; i < COLOR_INPUT_NUM; i++) {
		for (j = 0; j < COLOR_NUM; j++) {
			val = $("#color-" + i + "-" + j + " input").attr("value").toUpperCase();
			elements = $("#color-" + i + "-" + j);
			elements.css("background-color", "#" + val);
			elements.tooltip("destroy");
			elements.tooltip({title: "#" + val, delay: 200});
		}
	}
	
	if (json.apiType != last_api_type || json.themeID != last_kuler_id) {
		for (i = 0; i < COLOR_INPUT_NUM; i++) {
			lrPager.reset("#color-" + i);
			
			if (initial_selected_color_id == "default") {
				if (json.hex.length > 1 && json.hex.length < 5) {
					initial_selected_color_id = "default_" + json.hex.length + "_colors"; 
				}
			}
			
			changeSelectedColor(i, initial_selected_colors[initial_selected_color_id][i]);
		}
		updatePreview();
	}
	
	c = $("#selecting-kuler-theme-container");
	c.find("#theme-name").text(json.title);
	c.find("#theme-url a").attr("href", json.link);
	c.find("#theme-id").text(json.themeID);
	c.find("#theme-artist").text(json.authorLabel);
	
	hex = "";
	for (i = 0; i < json.hex.length; i++) {
		hex += json.hex[i];
		if (i < json.hex.length - 1) {
			hex += ", ";
		}
	}
	c.find("#theme-hex").text(hex);
	
	last_api_type = json.apiType;
	last_kuler_id = json.themeID;
	/*
	$("#debug").html("");
	$("#debug").append("<p>" + json.channel.item.enclosure.title + "</p>");
	$("#debug").append("<p>" + json.channel.item.link + "</p>");
	$("#debug").append("<p>" + json.channel.item.description + "</p>");
	*/
}

function postShowSelectColorPage() {
	updateStep(2);

	enableInputKulerPageButtons();
}

function enableInputKulerPageButtons() {
	$("#input-kuler-container .buttons button").removeAttr("disabled");
	$("#input-kuler-container .buttons .loading-image").hide();
}

function getSelectedColorChangeCallback(i, j) {
	return function() {
		changeSelectedColor(i, j);
		updatePreview();
	};
}

function changeSelectedColor(i, j) {
	var a;
	
	$("#color-" + i + "-" + j).find("input").attr("checked", true);
	
	for (a = 0; a < COLOR_NUM; a++) {
		if (a != j) {
			$("#color-" + i + "-" + a)
					.css("margin-top", "4px")
					.css("margin-bottom", "4px")
					.css("margin-left", "8px")
					.css("margin-right", "8px")
					.css("width", "22px")
					.css("height", "22px")
					.css("border", "1px solid #333333");
		}
		else {
			$("#color-" + i + "-" + a)
					.css("margin-top", "0")
					.css("margin-bottom", "0")
					.css("margin-left", "4px")
					.css("margin-right", "4px")
					.css("width", "30px")
					.css("height", "30px")
					.css("border", "5px ridge #999999");
		}
	}
}

function updatePreview() {
	$("#loading-image-preview").show();
	$html = '<iframe id="preview" src="' + BASE_URL + 'preview?' + $("#selected-color-form").serialize() + '"></iframe>';
	$("#preview-iframe-container").html($html);
}

function postPreviewLoad() {
	$("#loading-image-preview").hide();
	//$("#preview").show();
}

function updateStep(step) {
	switch (step) {
		case 1:
			$("#input-kuler-container").show();
			$("#select-color-container").hide();
			$("#download-container").hide();
			$("#steps-1").addClass("active");
			$("#steps-2").removeClass("active");
			$("#steps-3").removeClass("active");
			break;
		case 2:
			$("#input-kuler-container").hide();
			$("#select-color-container").show();
			$("#download-container").hide();
			$("#steps-1").removeClass("active");
			$("#steps-2").addClass("active");
			$("#steps-3").removeClass("active");
			break;
		case 3:
			$("#input-kuler-container").hide();
			$("#select-color-container").hide();
			$("#download-container").show();
			$("#steps-1").removeClass("active");
			$("#steps-2").removeClass("active");
			$("#steps-3").addClass("active");
			break;
	}
}

function applyDownloadLink(json) {
	$("#download-zip").attr("href", json.url_zip).text(json.file_name_zip);
	$("#download-zip-kickstrap").attr("href", json.url_zip_kickstrap).text(json.file_name_zip_kickstrap);
	$("#download-bootstrap-css").attr("href", json.url_bootstrap_css);
	$("#download-bootstrap-min-css").attr("href", json.url_bootstrap_min_css);
	$("#download-bootstrap-responsive-css").attr("href", json.url_bootstrap_responsive_css);
	$("#download-bootstrap-responsive-min-css").attr("href", json.url_bootstrap_responsive_min_css);
	//$("#download-bootstrap-less").attr("href", json.url_bootstrap_less);
	$("#download-variables-less").attr("href", json.url_variables_less);
}

function postShowDownloadPage() {
	updateStep(3);
	enableSelectColorPageButtons();
}

function enableSelectColorPageButtons() {
	$("#select-color-container .buttons button").removeAttr("disabled");
	$("#select-color-container .buttons .loading-image").hide();
}

function openLargePreview() {
	$url = BASE_URL + 'preview?' + $("#selected-color-form").serialize() + "&design=large";
	window.open($url, "paintstrap_large_preview");
}
