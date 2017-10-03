(function( $ ){
    $.fn.wmenu = function(usr) {
        if (typeof usr != 'object') {
            var options = {
                itemShowid:    '#w-show',
                selectedClass: 'w-selected'
            };
        } else {
            var options = usr;
        }
        var elem = this;
        // there's no need to do $(this) because
        // "this" is already a jquery object

        // $(this) would be the same as $($('#element'));
        /*
    this.fadeIn('normal', function(){
      // the this keyword is a DOM element
    });
    */
        mostrar = function (){
            //console.log('show');
            var id =  options.itemShowid;
            $(id).animate({
                height: 'toggle'
            }, 100);
            elem.addClass(options.selectedClass);
        };

        esconder = function () {
           // console.log('hide');
            var id =  options.itemShowid;
            $(id).animate({
                height: 'toggle'
            }, 100);
            elem.removeClass(options.selectedClass);
        };

        this.click(function () {
            if (elem.hasClass(options.selectedClass)) {
                esconder();
            } else {
                mostrar();
            }
            return false;
        });
    };
})( jQuery );