function lrPager() {
}

lrPager.reset = function(selector) {
	t = $(selector);
	
	t.children(".lr-pager-left-arrow").css("visibility", "hidden");
	
	pages = t.children(":not(.lr-pager-left-arrow)" + ":not(.lr-pager-right-arrow)");
	if (pages.size() <= 1) {
		t.children(".lr-pager-right-arrow").css("visibility", "hidden");
	}
	else {
		t.children(".lr-pager-right-arrow").css("visibility", "visible");
	}

	pages.filter(":not(:first)").each(function() {
		$(this).hide();
	});
	pages.filter(":first").each(function() {
		$(this).show();
	});
}

$(function() {
	$(".lr-pager").each(function() {
		t = $(this);

		t.prepend('<span class="lr-pager-left-arrow"><i class="glyphicon glyphicon-chevron-left"></i></span>');
		t.append('<span class="lr-pager-right-arrow"><i class="glyphicon glyphicon-chevron-right"></i></span>');
		
		lrPager.reset(this);
		
		t.children(".lr-pager-left-arrow").click(function() {
			p = $(this).parent();
			this_pages = p.children(":not(.lr-pager-left-arrow)" + ":not(.lr-pager-right-arrow)");
			current_page = p.children(":visible" + ":not(.lr-pager-left-arrow)" + ":not(.lr-pager-right-arrow)");
			prev_page = current_page.prev(":not(.lr-pager-left-arrow)");
			if (prev_page.size() > 0) {
				current_page.hide();
				prev_page.show();
				
				p.children(".lr-pager-right-arrow").css("visibility", "visible");

				if (prev_page.prev(":not(.lr-pager-left-arrow)").size() == 0) {
					p.children(".lr-pager-left-arrow").css("visibility", "hidden");
				}
			}
		});
		
		t.children(".lr-pager-right-arrow").click(function() {
			p = $(this).parent();
			this_pages = p.children(":not(.lr-pager-left-arrow)" + ":not(.lr-pager-right-arrow)");
			current_page = p.children(":visible" + ":not(.lr-pager-left-arrow)" + ":not(.lr-pager-right-arrow)");
			next_page = current_page.next(":not(.lr-pager-right-arrow)");
			if (next_page.size() > 0) {
				current_page.hide();
				next_page.show();
				
				p.children(".lr-pager-left-arrow").css("visibility", "visible");

				if (next_page.next(":not(.lr-pager-right-arrow)").size() == 0) {
					p.children(".lr-pager-right-arrow").css("visibility", "hidden");
				}
			}
		});
	});
});
