//Função para retornar as subsecoes de acordo com a secao escolhida.

$(function () {
    $("select#LOTA_SIGLA_SECAO").change(function () {
        var secao = $(this).val();
       
        $.ajax({
            url: base_url + '/sisad/caixaunidade/internos/secao/' + secao,
          dataType : 'html',
          success: function(data) {
            $('#internos').html(data);
          }
        });
        
    });
});