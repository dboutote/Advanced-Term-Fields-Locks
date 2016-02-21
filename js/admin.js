( function ($) {
    'use strict';

	var locked_tags = $('table.tags .dashicons-lock');
	locked_tags.parents('a.row-title').contents().unwrap().parent().wrapInner('<span class="row-title"></span>');
	locked_tags.parents('tr').children('.check-column').find(':checkbox').remove();

})(jQuery);