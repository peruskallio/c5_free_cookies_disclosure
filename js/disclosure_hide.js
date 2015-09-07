(function($) {

$(document).ready(function() {
	if (COOKIES_DISCLOSURE_HIDE_INTERVAL !== undefined) {
		var topOffset = $('#ccm-toolbar').height();
		if ($('body').offset().top > 0) {
			topOffset += $('body').offset().top;
		}
		
		var elem = $('#ccm-cookiesDisclosure .disclosure-container');
		$('#ccm-cookiesDisclosure').css('height', elem.outerHeight());
		var position = $('#ccm-cookiesDisclosure').offset().top > topOffset ? 'bottom' : 'top';
		function _hide() {
			if (position == 'bottom') {
				elem.stop().animate({
					top: elem.outerHeight()
				});
			} else {
				elem.stop().animate({
					top: -elem.outerHeight()
				});
			}
		}
		function _show() {
			elem.stop().animate({top:0});
		}
		
		setTimeout(function() {
			_hide();
			$('#ccm-cookiesDisclosure').hover(function() {
				_show()
			}, function() {
				if (!$(this).hasClass('no-slide')) _hide()
			});
		}, COOKIES_DISCLOSURE_HIDE_INTERVAL*1000);
	}
});

})(jQuery);