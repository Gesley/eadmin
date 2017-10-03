/**
 * Limita a quantidade de caracteres digitados pelo usuário nos inputs de texto.
 */
$(function () {
    /** Inputs limitados */
    $('#DOCM_DS_ASSUNTO_DOC').limit('32000');
    $('#SSOL_DS_OBSERVACAO').limit('350');
});
(function ($) {
    $.fn.extend({
        limit: function (limit, element) {

            var interval, f;
            var self = $(this);

            $(this).focus(function () {
                interval = window.setInterval(substring, 100);
            });

            $(this).blur(function () {
                clearInterval(interval);
                substring();
            });

            substringFunction = "function substring(){ var val = $(self).val();var length = val.length;if(length > limit){$(self).val($(self).val().substring(0,limit));}";
            if (typeof element != 'undefined')
                substringFunction += "if($(element).html() != limit-length){$(element).html((limit-length<=0)?'0':limit-length);}"

            substringFunction += "}";

            eval(substringFunction);

            substring();

        }
    });
})(jQuery);