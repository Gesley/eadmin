$(function(){ 
    $('#ANEXOS-0').MultiFile({
        STRING: {
            file: '<em title="Clique para Remover" onclick="$(this).parent().prev().click()">$file</em>',
            remove: '<img class="excluirpdf" title="Clique para Remover" height="16" width="16"/>'
        }
    });
});