/**
 * @category    Laboratório
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author      Daniel Rodrigues
 * @license     FREE, keep original copyrights
 * @version     controlada pelo SVN
 */

var valor_flag;
var valor_modelo;
var valor_descricao;

function carregaValores(valores){
    if(valores.flag != ""){
        valor_flag = valores.flag;
    }
    if(valores.modelo != ""){
        valor_modelo = valores.modelo;
    }
    if(valores.descricao != ""){
        valor_descricao = valores.descricao;
    }
}

$(document).ready(function(){
    
    /**
     * Configurando entrada de dados no campo
     */
    $('#MTEN_QT_ENTRADA_MATERIAL').keyup(function(){
        if(isNaN($(this).val())){
            $(this).val('');
        }else{
            var x = $(this).val().replace(".","");
            $(this).val(x);
        }
    });
    
    /**
     * Ao escolher uma marca, carregar os modelos relacionados
     */
    var LHDW_CD_MARCA;
    $("#MTEN_DS_MARCA").autocomplete({
        source: base_url + '/sosti/labhardware/ajaxmarca',
        minLength: 2,
        delay: 100,
        select: function( event, ui ) {
            $("#MTEN_CD_MARCA").val(ui.item.id);
            LHDW_CD_MARCA = ui.item.id;
            var modelos = LHDW_CD_MARCA;
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxmodelo/id/'+modelos,
                beforeSend:function() {
                    bloqueiaCamposMarca();
                    $("#MTEN_CD_MODELO").removeClass('erroInputSelect');
                    $("#MTEN_CD_MODELO").html('');
                    $("#MTEN_CD_MODELO").addClass('carregandoInputSelect');
                    $("#MTEN_CD_MODELO").removeAttr('disabled','disabled');
                    $("#MTEN_CD_MODELO").focus();
                },
                success: function(data) {
                    $("#MTEN_CD_MODELO").html(data);
                    $("#MTEN_CD_MODELO").removeClass('carregandoInputSelect');
                },
                error: function(){
                    $("#MTEN_CD_MODELO").removeClass('x-form-field');
                    $("#MTEN_CD_MODELO").val('Erro ao carregar.');
                    $("#MTEN_CD_MODELO").addClass('erroInputSelect');
                    $("#MTEN_CD_MODELO").html('<option>Erro ao carregar</option>');
                }
            });      
        },
        change: function( event, ui ) {
            $("#MTEN_CD_MARCA").val(ui.item.id);
            LHDW_CD_MARCA = ui.item.id;
            var modelos = LHDW_CD_MARCA;
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxmodelo/id/'+modelos,
                beforeSend:function() {
                    bloqueiaCamposMarca();
                    $("#MTEN_CD_MODELO").removeClass('erroInputSelect');
                    $("#MTEN_CD_MODELO").html('');
                    $("#MTEN_CD_MODELO").addClass('carregandoInputSelect');
                    $("#MTEN_CD_MODELO").removeAttr('disabled','disabled');
                    $("#MTEN_CD_MODELO").focus();
                },
                success: function(data) {
                    $("#MTEN_CD_MODELO").html(data);
                    $("#MTEN_CD_MODELO").removeClass('carregandoInputSelect');
                },
                error: function(){
                    $("#MTEN_CD_MODELO").removeClass('x-form-field');
                    $("#MTEN_CD_MODELO").val('Erro ao carregar.');
                    $("#MTEN_CD_MODELO").addClass('erroInputSelect');
                    $("#MTEN_CD_MODELO").html('<option>Erro ao carregar</option>');
                }
            });       
        }
    });
        
    /**
     * Ajax para o campo Seção/Subseção
     * Ao escolher uma Seção, carrega suas subseções
     */
    $("select#TRF1_SECAO").change(
        function () {
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                delay: 100,
                beforeSend:function() {
                    bloqueiaCampos();
                    $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                    $('select#SECAO_SUBSECAO').html('');
                },
                success: function(data) {
                    $('select#SECAO_SUBSECAO').html(data);
                },
                error: function(){
                    $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                }
            });
        });
        
    /*
     * Ao selecionar uma subsecao, desbloquear o campo de Marca
     */
    $('select#SECAO_SUBSECAO').change(function(){
        bloqueiaCamposSubsecao();
        $("#MTEN_DS_MARCA").removeAttr('disabled','disabled');
        $("#MTEN_DS_MARCA").focus();
    });
        
    /*
     * Tranforma o campo do hardware em combobox e faz a escolha do hardware para
     * dar entrada no estoque
     */
    $("#LHDW_DS_HARDWARE").combobox({ 
        selected: function(event, ui) {
            $('#LHDW_DS_HARDWARE').val(ui.item.value);
            $('#MTEN_ID_HARDWARE').val(ui.item.value);
            if($('#LHDW_DS_HARDWARE').val() != 0){
                //console.log($('#LHDW_DS_HARDWARE :selected').text());
                $('#LHDW_DS_HARDWARE_AUX').val($('#LHDW_DS_HARDWARE :selected').text());
                limpaValoresEscolhaHardware();
                $('#complemento-form > dd > input').removeAttr('disabled','disabled');
            }else{
                limpaValoresPadrao();
            }
        },
        changed: function(event, ui) {
            $('#LHDW_DS_HARDWARE').val(ui.item.value);
            $('#MTEN_ID_HARDWARE').val(ui.item.value);
            if($('#LHDW_DS_HARDWARE').val() != 0){
                $('#LHDW_DS_HARDWARE_AUX').val($('#LHDW_DS_HARDWARE :selected').text());
                limpaValoresEscolhaHardware();
                $('#complemento-form > dd > input').removeAttr('disabled','disabled');
            }else{
                limpaValoresPadrao();
            }
        } 
    });
    $('#combobox-input-text-LHDW_DS_HARDWARE').css('width', '490px');
        
    /*
     * Ao escolher o modelo, buscar os hardwares cadastrados para o modelo,
     * secao, subsecao e marca selecionados como filtro
     */
    $("#MTEN_CD_MODELO").change(function(){
        var secao = $('#TRF1_SECAO').val().split('|')[0];
        var subsecao = $('#SECAO_SUBSECAO').val().split('|')[1];
        var marca = $('#MTEN_CD_MARCA').val();
        var modelo = $('#MTEN_CD_MODELO').val();
        
        if($("#MTEN_CD_MODELO").val() != ""){
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxmaterialalmox/secao/'+secao+'/subsecao/'+subsecao+'/marca/'+marca+'/modelo/'+modelo,
                dataType : 'html',
                beforeSend:function() {
                    $('#LHDW_DS_HARDWARE').removeAttr('disabled','disabled');
                    $('#LHDW_DS_HARDWARE').html("");
                    $('#combobox-input-text-LHDW_DS_HARDWARE').removeAttr('disabled','disabled');
                    $('#combobox-input-button-LHDW_DS_HARDWARE').removeAttr('disabled','disabled');
                },
                success: function(data) {
                    $('#LHDW_DS_HARDWARE').html(data);
                },
                error: function(){
                    $('#LHDW_DS_HARDWARE').html('<option>Erro ao carregar</option>');
                }
            })
        }else{
            bloqueiaCamposModelo();
        }
    });
      
      
    //################################## Functions ##############################  
     
     
    /**
     * Função seta o estado inicial de todos os campos do formulário 
     */
    function bloqueiaCampos(){
        //Bloqueia campos
        $('#SECAO_SUBSECAO').attr('disabled','disabled');
        $('#MTEN_DS_MARCA').attr('disabled','disabled');
        $('#MTEN_CD_MODELO').attr('disabled','disabled');
        $('#combobox-input-text-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#combobox-input-button-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#complemento-form > dd > input').attr('disabled','disabled');
        //limpa valores
        $('#SECAO_SUBSECAO').val('');
        $('#MTEN_DS_MARCA').val('');
        $('#MTEN_CD_MODELO').val('');
        limpaValoresPadrao();          
    }
    
    /**
     * Função bloqueia os campos com os critérios da subsecao
     */
    function bloqueiaCamposSubsecao(){
        //Bloqueia campos
        $('#MTEN_CD_MODELO').attr('disabled','disabled');
        $('#combobox-input-text-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#combobox-input-button-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#complemento-form > dd > input').attr('disabled','disabled');
        //limpa valores
        $('#MTEN_DS_MARCA').val('');
        $('#MTEN_CD_MODELO').val('');
        limpaValoresPadrao();
    }
    
    /**
     * Função bloqueia os campos com os critérios da marca
     */
    function bloqueiaCamposMarca(){
        //Bloqueia campos
        $('#combobox-input-text-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#combobox-input-button-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#complemento-form > dd > input').attr('disabled','disabled');
        //limpa valores
        limpaValoresPadrao();   
    }
    
    /**
     * Função bloqueia os campos com os critérios do modelo
     */
    function bloqueiaCamposModelo(){
        //bloqueia
        $('#combobox-input-text-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#combobox-input-button-LHDW_DS_HARDWARE').attr('disabled','disabled');
        $('#complemento-form > dd > input').attr('disabled','disabled');
        //limpa valores
        limpaValoresPadrao();
    }
    
    /**
     * Função bloqueia os campos com os critérios do hardware
     */
    function bloqueiaCamposHardware(){
        //bloqueia
        $('#complemento-form > dd > input').attr('disabled','disabled');
        //limpa valores
        limpaValoresPadrao();
    }
    
    /**
     * Funcao limpa os valores padrão do formulário
     */
    function limpaValoresPadrao(){
        $('#MTEN_ID_HARDWARE').val('');
        $('#LHDW_DS_HARDWARE').val('');
        $('#LHDW_DS_OBSERVACAO').val('');
        $('#MTEN_QT_ENTRADA_MATERIAL').val('');
        $('#MTEN_NR_REQUISICAO_MATERIAL').val('');
        $('#MTEN_DS_OBSERVACAO').val('');
        $('#combobox-input-text-LHDW_DS_HARDWARE').val('');
    }
    
    /**
     * Funcao limpa os valores dos campos ao selecionar um hardware diferente
     */
    function limpaValoresEscolhaHardware(){
        $('#LHDW_DS_OBSERVACAO').val('');
        $('#MTEN_QT_ENTRADA_MATERIAL').val('');
        $('#MTEN_NR_REQUISICAO_MATERIAL').val('');
        $('#MTEN_DS_OBSERVACAO').val('');
    }
    
    
    function carregaSubsecaoInicio(){
        if($('#TRF1_SECAO').val() != ""){
            var secao = $('#TRF1_SECAO').val().split('|')[0];
            var lotacao = $('#TRF1_SECAO').val().split('|')[1];
            var tipolotacao = $('#TRF1_SECAO').val().split('|')[2];
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                delay: 100,
                beforeSend:function() {
                    //bloqueiaCampos();
                    $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                //$('select#SECAO_SUBSECAO').html('');
                },
                success: function(data) {
                //$('select#SECAO_SUBSECAO').html(data);
                },
                error: function(){
                    $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                }
            });
        } 
    }

    //Chamada do estado inicial do formulário
    if(!valor_flag){   
        bloqueiaCampos();
    }else{
        //Se for um populate, recarregar o campo de hardware
        var secao = $('#TRF1_SECAO').val().split('|')[0];
        var subsecao = $('#SECAO_SUBSECAO').val().split('|')[1];
        var marca = $('#MTEN_CD_MARCA').val();
        var modelo = $('#MTEN_CD_MODELO').val();
        
        if($("#MTEN_CD_MODELO").val() != ""){
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxmaterialalmox/secao/'+secao+'/subsecao/'+subsecao+'/marca/'+marca+'/modelo/'+modelo,
                dataType : 'html',
                beforeSend:function() {
                    
                },
                success: function(data) {
                    $('#LHDW_DS_HARDWARE').html(data);
                },
                error: function(){
                    $('#LHDW_DS_HARDWARE').html('<option>Erro ao carregar</option>');
                }
            })
        }else{
            bloqueiaCamposModelo();
        }
        
        $('#combobox-input-text-LHDW_DS_HARDWARE').val(valor_descricao);
    } 

});