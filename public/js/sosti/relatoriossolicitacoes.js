$(document).ready(function(){
    /**
     * Esconde as categorias se o grupo for Desen/Sustentacao
     */
    $("#CTSS_NM_CATEGORIA_SERVICO").hide();
    $("#CTSS_NM_CATEGORIA_SERVICO-label").hide();
    $("#AGRUPAMENTO-1").prop("checked", true);
//    if (($('#LOTA_COD_LOTACAO').val() == ('TR|1783|2')) /*|| ($('#LOTA_COD_LOTACAO').val() == 'TR|1784|2') || ($('#LOTA_COD_LOTACAO').val() == 'TR|1155|2')*/) {
//        $("#CTSS_NM_CATEGORIA_SERVICO").show();
//        $("#CTSS_NM_CATEGORIA_SERVICO-label").show();
//    }
    $("select#TRF1_SECAO").change(
        function () {
        var secao = $(this).val().split('|')[0];
        var lotacao = $(this).val().split('|')[1];
        var tipolotacao = $(this).val().split('|')[2];

        $.ajax({
            url: base_url + '/sosti/relatoriossolicitacoes/ajaxsecaolotacaosigla/secao/'+secao,
            dataType : 'html',
            beforeSend:function() {
                $("#LOTA_COD_LOTACAO").addClass('carregandoInputSelect');
                $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).attr('value','');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('erroInputSelect');
                $( "select#SECAO_SUBSECAO").html("");
                $( "select#SGRS_ID_GRUPO").html("<option value=''> Selecione </option>");
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
            },
            success: function(data) {
                $('#LOTA_COD_LOTACAO').html(data);
                $("#LOTA_COD_LOTACAO").removeClass('carregandoInputSelect');
                $("#LOTA_COD_LOTACAO").focus();
            },
            error: function(){
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('x-form-field');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).val('Erro ao carregar.');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('erroInputSelect');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: 0;');
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
    });
            
    /**
     * Configuração do combobox  UNIDADE
     */
    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('style','width: 492px;');
    $("#combobox-input-text-LOTA_COD_LOTACAO").css('text-transform','uppercase');
    aux_button_style =  $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style');
    $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' left: -20px; top: 4px;');
    aux_button_style =  $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style');
    
    /**
     * Carregar os Serviços
     */
    $('select#LOTA_COD_LOTACAO').change( 
        function() {
        var dsv;
        if (($(this).val() == ('TR|1783|2')) || ($(this).val() == 'TR|1784|2') || ($(this).val() == 'TR|1155|2')) {
            dsv = 1;
        } else {
            dsv = 0;
        }
        $.ajax({
            url: base_url+'/sosti/relatoriossolicitacoes/ajaxcategoriaservico/dsv/'+dsv,
            dataType: 'html',
            type: 'POST',
            data: this.value,
            contentType: 'application/json',
            processData: false,
            beforeSend:function() {
                $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('erroInputSelect');
                $("#CTSS_NM_CATEGORIA_SERVICO").html('');
                $("#CTSS_NM_CATEGORIA_SERVICO").addClass('carregandoInputSelect');
            },
            success: function(data) {
                if (dsv == 1) {
                    $("#CTSS_NM_CATEGORIA_SERVICO").html(data);
                    $("#CTSS_NM_CATEGORIA_SERVICO").hide();
                    $("#CTSS_NM_CATEGORIA_SERVICO-label").hide();
                    $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('carregandoInputSelect');
                    $("#CTSS_NM_CATEGORIA_SERVICO").focus();
                } else {
                    $("#CTSS_NM_CATEGORIA_SERVICO").hide();
                    $("#CTSS_NM_CATEGORIA_SERVICO-label").hide();
                }
            },
            error: function(){
                $("#CTSS_NM_CATEGORIA_SERVICO").removeClass('x-form-field');
                $("#CTSS_NM_CATEGORIA_SERVICO").val('Erro ao carregar.');
                $("#CTSS_NM_CATEGORIA_SERVICO").addClass('erroInputSelect');
                $("#CTSS_NM_CATEGORIA_SERVICO").html('<option>Erro ao carregar</option>');
            }
        });  
    });
    
    $('#CTSS_NM_CATEGORIA_SERVICO').change(function(){
        var categoria = $('#CTSS_NM_CATEGORIA_SERVICO option:selected').text();
        $('#nome-categoria').val(categoria);
    });
});
