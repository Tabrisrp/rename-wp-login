jQuery(function ($) {
    $('.wps-updates.is-dismissible').each(function () {
        var $el = $(this),
            $button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>');

        // Ensure plain text
        $button.find('.screen-reader-text').text( 'Dismiss this notice.' );
        $button.on('click.wp-dismiss-notice', function (event) {
            event.preventDefault();
            $el.fadeTo(100, 0, function () {
                $el.slideUp(100, function () {
                    $el.remove();
                });
            });
        });

        $el.append($button);
    });

    $('div[data-dismissible] button.notice-dismiss').click(function (event) {
        event.preventDefault();
        var $this = $(this);

        var attr_value, option_name, dismissible_length, data;

        attr_value = $this.parent().attr('data-dismissible').split('-');

        // remove the dismissible length from the attribute value and rejoin the array.
        dismissible_length = attr_value.pop();

        option_name = attr_value.join('-');

        data = {
            'action': 'dismiss_admin_notice',
            'option_name': option_name,
            'dismissible_length': dismissible_length,
            '_ajax_nonce': dismissible_notice.nonce,
        };

        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        $.post(ajaxurl, data);
    });
});