$(function() {
	initSocialButtons();
});

function initSocialButtons() {
	var language = window.navigator.userLanguage || window.navigator.language;

	$('#hatena').socialbutton('hatena', {
		button: 'simple'
	});

	$('#twitter').socialbutton('twitter', {
		button: 'none',
		text: 'PaintStrap',
		lang: language.substr(0, 2),
		via: 'wiz_g'
	});

	$('#google_plusone').socialbutton('google_plusone', {
		lang: 'ja',
		size: 'medium',
		count: false
	});

	$('#facebook_like').socialbutton('facebook_like', {
		button: 'button_count'
	});
}
