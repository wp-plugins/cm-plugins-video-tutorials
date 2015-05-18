
(function ($) {
    $(document).ready(function ($) {
        $('select').on('change', function(){
            $(this).parents('form').submit();
        });
    });
})(jQuery);