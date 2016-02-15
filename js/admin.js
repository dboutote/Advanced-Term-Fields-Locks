( function ($) {
    'use strict';

    $('#the-list').on('click', '.editinline', function () {
        var tr_id = $(this).parents('tr').attr('id');
        var meta_value = $('td.' + l10n_ATF_Locks.custom_column_name + ' i', '#' + tr_id).attr('data-' + l10n_ATF_Locks.data_type);

        $(':input[name="' + l10n_ATF_Locks.meta_key + '"]', '.inline-edit-row').val(meta_value);
    });

})(jQuery);