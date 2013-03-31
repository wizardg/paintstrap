$(function() {
	initSocialButtons();
});

function initSocialButtons() {
	$('#hatena').socialbutton('hatena', {
		button: 'simple'
	});

	$('#twitter').socialbutton('twitter', {
		button: 'none',
		text: 'PaintStrap',
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
