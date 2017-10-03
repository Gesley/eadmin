$(function() {
    $( ".editar" ).button({
        icons: {
            primary: "ui-icon-pencil"
        }
    }).attr('style','width: 40px; height: 16px;');
    $( ".excluir" ).button({
        icons: {
            primary: "ui-icon-trash"
        }
    }).attr('style','width: 40px; height: 16px;');
});