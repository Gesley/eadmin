$(function () {
    //$( "button, input:submit, a", ".dema" ).button();
    // MENU
    $(".navigation").wijmenu();
    $('a.icoAdminlink').wmenu({
        itemShowid :'#mudarUsuario',
        selectedClass :'icoAdminlink_click'
    });
    
    // FIELDS
    $("input:text, select, input:file").addClass("x-form-text");
    $("input.datepicker").datepicker($.datepicker.regional["pt_BR"]);
    $("textarea").addClass("x-form-field");
    
    // ICONES
    $('a.editar').button({
        icons: {
            primary: 'ui-icon-pencil'
        }
    });
    $('a.excluir').button({
        icons: {
            primary: 'ui-icon-trash'
        }
    });
    $('a.novo').button({
        icons: {
            primary: 'ui-icon-document'
        }
    });
    $( ".sair" ).button({
        icons: {
            primary: "ui-icon-power"
        }
    });
    
    // GRID
    $(".grid").addClass("ui-widget ui-widget-content");
    $("thead tr",".grid").addClass("ui-widget-header");
    $( ".paginationControl").addClass("ui-widget-header ui-corner-all");
    $( "a, span", ".paginationControl" ).each(function () {
        var disabled = true;

        if ($(this).is('a')) {
            disabled = false;
        }
                    
        if ($(this).is('.next')) {
            $(this).button({
                disabled: disabled,
                icons: {
                    secondary: 'ui-icon-seek-next'
                }
            });
        } else if ($(this).is('.previous')) {
            $(this).button({
                disabled: disabled,
                icons: {
                    primary: 'ui-icon-seek-prev'
                }
            });
        } else if ($(this).is('.last')) {
            $(this).button({
                disabled: disabled,
                icons: {
                    secondary: 'ui-icon-seek-end'
                }
            });
        } else if ($(this).is('.first')) {
            $(this).button({
                disabled: disabled,
                icons: {
                    primary: 'ui-icon-seek-first'
                }
            });
        } else {
            $(this).button();
        }
    });
}); 