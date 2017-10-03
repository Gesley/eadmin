/**
 * 
 * Arquivo javascript para a funcionalidade de controle de concorrência
 * 
 */
//$(document).ready(function(){
    
       function carrega_nivel(caixa, nivel) {
        $.ajax({
            async: false,
            dataType: 'json',
            method: 'post',
            url: base_url + '/sosti/controle-concorrencia/json-nivel-atendimento/caixa/'+caixa,
            beforeSend:function() {
                $("#NIVEL").removeClass('erroInputSelect');
                $("#NIVEL").val("");
                $("#NIVEL").addClass('carregandoInputSelect');
            },
            success: function(json) {
                var options = "";
                $.each(json, function(key, value) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });
                $("#NIVEL").removeClass('carregandoInputSelect');
                $("#NIVEL").html(options);
                (nivel != '')?($('#NIVEL option[value='+nivel+']').attr('selected', 'selected')):('');
            },
            error: function(){
                $("#NIVEL").removeClass('x-form-field');
                $("#NIVEL").val('Erro ao carregar.');
                $("#NIVEL").addClass('erroInputSelect');
                $("#NIVEL").html('<option value="">Erro ao carregar</option>');
            }
        });
    }
   
//})