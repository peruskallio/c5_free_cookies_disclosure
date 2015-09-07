(function ($) {

    $(document).ready(function () {
        $('#ccm-cookiesDisclosure .disclosure-form form').submit(function (ev) {
            ev.preventDefault();
            var addr = $(this).attr('action');
            var data = $(this).serializeArray();
            data.push({name: 'ajax', value: 1});
            $('#ccm-cookiesDisclosure').addClass('no-slide');
            $.post(addr, data, function (resp) {
                if (resp.success) {
                    $('#ccm-cookiesDisclosure').animate({opacity: 0}, function () {
                        $('#ccm-cookiesDisclosure').remove();
                    });
                } else {
                    $('#ccm-cookiesDisclosure').removeClass('no-slide');
                    alert(ccmi18n_cookiesdisclosure.allowCookies);
                }
            }, 'json');
        });
    });

})(jQuery);
