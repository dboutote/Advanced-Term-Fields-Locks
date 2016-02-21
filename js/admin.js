( function ($) {
    'use strict';
	
	var locked_tags = $('table.tags .dashicons-lock').parents('a.row-title');
	locked_tags.contents().unwrap().parent().wrapInner('<span class="row-title"></span>');
	
})(jQuery);