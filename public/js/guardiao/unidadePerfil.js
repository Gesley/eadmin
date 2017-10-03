/**
 * @category    GUARDIAO
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author                        Daniel Rodrigues
 * @license                       FREE, keep original copyrights
 * @version                       controlada pelo SVN
 */


/**
* Variaveis globais
* Serão usadas para salvar a ultima consulta do formulario
*/
var valor_secao;
var valor_subsecao;
var valor_unidade;

/**
*
* Carrega os valores das variaveis via JSON   
*/
function carregaVariaveis(valores){
    if(valores.secao != ""){
        valor_secao = valores.secao;
    }
    if(valores.subsecao != ""){
        valor_subsecao = valores.subsecao;
    }
    if(valores.unidade != ""){
        valor_unidade = valores.unidade;
    }
}

/**
 *
 * READY
 */
$(document).ready(function() {
        
    /*
    * Esconder o submit de Associação
    */
    $("#Salvar").css('display','none');
    $("#confirma").css('display','none');

    /**
    * Escolha da Seção
    */
    $("select#TRF1_SECAO").focus(function(){
        /*
        * Se existir o campo da validacao, entao perguntar se o usuario qer salvar as alteracoes
        */
        if($("#form_validator").length){     
            /**
            * 
            * Dialog de confirmação
            */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons:{
                    Sim:function(){
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não:function(){
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $("#combobox-input-text-UNPE_SG_SECAO").attr('value','');
                        $("#UNPE_SG_SECAO").empty();
                        valor_subsecao = "";
                        $('select#SECAO_SUBSECAO').html('');
                        $('select#TRF1_SECAO').attr('value','');
                    }
                }
            });
        }
    });
    
    
    $("select#TRF1_SECAO").change(
        function () {
            
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];

            $.ajax({
                url: base_url + '/guardiao/unidadeperfil/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                beforeSend:function() {
                    /*
                     * Esconder o submit de Associação
                     * Limpar o HTML do listBox
                     * Bloquear combobox Unidade
                     * Habilitar o select Subseção
                     */
                    $("#Salvar").css('display','none');
                    $('#div_associar_perfil').empty();
                    $("#combobox-input-text-UNPE_SG_SECAO").attr('value','');
                    $("#UNPE_SG_SECAO").empty();
                    valor_subsecao = "";
                    $('select#SECAO_SUBSECAO').html('');
                },
                success: function(data) {
                    $('select#SECAO_SUBSECAO').html(data);
                    $('select#SECAO_SUBSECAO').focus();
                },
                error: function(){
                    $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
                }
            });
        });
        
    /**
    * Escolha da SubSeção 
    */
    $("select#SECAO_SUBSECAO").focus(function(){
        /*
        * Se existir o campo da validacao, entao perguntar se o usuario qer salvar as alteracoes
        */
        if($("#form_validator").length){     
            /**
            * 
            * Dialog de confirmação
            */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons:{
                    Sim:function(){
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não:function(){
                        $(this).dialog('close');
                        $("#div_associar_perfil").html('');
                        $("#combobox-input-text-UNPE_SG_SECAO").attr('value','');
                        $("#UNPE_SG_SECAO").empty();
                        
                        secao = $("select#SECAO_SUBSECAO").val().split('|')[0];
                        lotacao = $("select#SECAO_SUBSECAO").val().split('|')[1];
                        tipolotacao = $("select#SECAO_SUBSECAO").val().split('|')[2];

                        $.ajax({
                            url: base_url + '/guardiao/unidadeperfil/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                            dataType : 'html',
                            beforeSend:function() {
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('erroInputSelect');
                                $('select#UNPE_SG_SECAO').html('');
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('carregandoInputSelect');
                                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: -1000;');
                            },
                            success: function(data) {

                                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).attr('value','');
                                $( "#combobox-input-button-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');


                                $('#UNPE_SG_SECAO').html(data);
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('carregandoInputSelect');
                                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).focus();
                            },
                            error: function(){
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('x-form-field');
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).val('Erro ao carregar.');
                                $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('erroInputSelect');
                                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                                $('select#UNPE_SG_SECAO').html('<option>Erro ao carregar</option>');
                            }
                        }); 
                    }
                }
            });
        }
    });
    
    $("select#SECAO_SUBSECAO").change(
        function () {
            /*
             * Esconder o submit de Associação
             */
            $("#Salvar").css('display','none');
            $('#div_associar_perfil').empty();
        
            /**
             * Verifica se a subseção é vazia
             */
            if($("select#SECAO_SUBSECAO").val() != ""){
            
                secao = $(this).val().split('|')[0];
                lotacao = $(this).val().split('|')[1];
                tipolotacao = $(this).val().split('|')[2];

                $.ajax({
                    url: base_url + '/guardiao/unidadeperfil/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                    dataType : 'html',
                    beforeSend:function() {
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('erroInputSelect');
                        $('select#UNPE_SG_SECAO').html('');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('carregandoInputSelect');
                        $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: -1000;');
                    },
                    success: function(data) {

                        $( "#combobox-input-text-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).attr('value','');
                        $( "#combobox-input-button-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');


                        $('#UNPE_SG_SECAO').html(data);
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('carregandoInputSelect');
                        $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).focus();
                    },
                    error: function(){
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('x-form-field');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).val('Erro ao carregar.');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('erroInputSelect');
                        $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                        $('select#UNPE_SG_SECAO').html('<option>Erro ao carregar</option>');
                    }
                });
            }else{
                $( "#combobox-input-text-UNPE_SG_SECAO" ).attr('disabled','disabled');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).val('');
                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('disabled','disabled');
            }
        });
        
    /**
    * Configuração do campo da Unidade 
    */
    $("select#UNPE_SG_SECAO").combobox({
        /**
        * Ao selecionar uma Unidade, carregar os campos de perfis
        */
        selected: function(event, ui) {

            var secao = $('#TRF1_SECAO').val();
            var subsecao = $('#SECAO_SUBSECAO').val();
            var unidade = $('#UNPE_SG_SECAO').val();

            if((secao != '') && (subsecao != '') && (unidade != '')){
                url = base_url + '/guardiao/unidadeperfil/ajaxperfilunidade/unidade/'+unidade;  
                $.ajax({
                    url: url,
                    dataType: 'html',
                    processData: false, 
                    success: function(data) {
                        /**
                        * Carega os campos de perfis
                        * Configura a listbox
                        * Configura os botões
                        * Ativa o submit de associação
                        */
                        $('#div_associar_perfil').html(data);
                        $.configureBoxes();
                        $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                        $("#Salvar").css('display','block');
                        $("#Salvar").focus();
                    }
                });    
            }else{
                return false;
            }
        },
        /**
        * Ao selecionar uma Unidade pelo TAB, carregar os campos de perfis
        */
        changed: function(event, ui) {

            var secao = $('#TRF1_SECAO').val();
            var subsecao = $('#SECAO_SUBSECAO').val();
            var unidade = $('#UNPE_SG_SECAO').val();

            if((secao != '') && (subsecao != '') && (unidade != '')){
                url = base_url + '/guardiao/unidadeperfil/ajaxperfilunidade/unidade/'+unidade;  
                $.ajax({
                    url: url,
                    dataType: 'html',
                    processData: false, 
                    success: function(data) {
                        /**
                        * Carega os campos de perfis
                        * Configura a listbox
                        * Configura os botões
                        * Ativa o submit de associação
                        */
                        $('#div_associar_perfil').html(data);
                        $.configureBoxes();
                        $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                        $("#Salvar").css('display','block');
                        $("#Salvar").focus();
                    }
                });    
            }else{
                return false;
            }
        }
    });//fim da combobox
    
    /**
    * Configuração do combobox 
    */
    $("#combobox-input-text-UNPE_SG_SECAO").attr('style','width: 492px;');
    $("#combobox-input-text-UNPE_SG_SECAO").css('text-transform','uppercase');
    aux_button_style =  $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style');
    $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' left: -20px; top: 5px;');
    aux_button_style =  $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style');
    
    /**
     * Validacao do focus da Unidade
     */
    $("#combobox-input-text-UNPE_SG_SECAO").focus(function(){
        /*
         * Se existir o campo da validacao, entao perguntar se o usuario qer salvar as alteracoes
         */
        if($("#form_validator").length){     
            /**
            * 
            * Dialog de confirmação
            */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons:{
                    Sim:function(){
                        $(this).dialog('close');
                        $("#form").submit();
                    },
                    Não:function(){
                        $(this).dialog('close');
                        $('#div_associar_perfil').empty();
                        $("#Salvar").css('display','none');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).attr('value','');
                        $("#combobox-input-text-UNPE_SG_SECAO").focus();
                    }
                }
            });
        }
    });
    

    /**
    * Ao clicar no botão Desfazer, voltam as configurações originais dos perfis daquela Unidade
    */
    $("#desfazer").live('click', function(){

        var secao = $('#TRF1_SECAO').val();
        var subsecao = $('#SECAO_SUBSECAO').val();
        var unidade = $('#UNPE_SG_SECAO').val();

        if((secao != '') && (subsecao != '') && (unidade != '')){
            url = base_url + '/guardiao/unidadeperfil/ajaxperfilunidade/unidade/'+unidade;  
            $.ajax({
                url: url,
                dataType: 'html',
                processData: false, 
                success: function(data) {
                    /**
                    * Carega os campos de perfis
                    * Configura a listbox
                    * Configura os botões
                    * Ativa o submit de associação
                    */
                    $('#div_associar_perfil').html(data);
                    $.configureBoxes();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                    $("#Salvar").css('display','block');
                }
            });    
        }else{
            return false;
        }
    });
        
    /**
    * Ao submeter o formulário, seleciona todos os perfis associados e verifica se foi escolhida alguma unidade
    */
    $("#form").submit(function(){
        if($("#combobox-input-text-UNPE_SG_SECAO").val() == ""){
            $('#UNPE_SG_SECAO').val('');
        }
        $("#box2View option").attr("selected","selected");
        return true;  
    });
    

    /**
     * ###################### Mentem os valores da ultima consulta #########################
     * 
     * Subsecao
     */
    if($("select#TRF1_SECAO").val() != ""){
        
        var secao = $("select#TRF1_SECAO").val().split('|')[0];
        var lotacao = $("select#TRF1_SECAO").val().split('|')[1];
        var tipolotacao = $("select#TRF1_SECAO").val().split('|')[2];

        $.ajax({
            url: base_url + '/guardiao/unidadeperfil/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            beforeSend:function() {
                /*
                * Esconder o submit de Associação
                * Limpar o HTML do listBox
                * Bloquear combobox Unidade
                * Habilitar o select Subseção
                */
                $("#Salvar").css('display','none');
                $('#div_associar_perfil').empty();
                $("#combobox-input-text-UNPE_SG_SECAO").val('');
                $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                $('select#SECAO_SUBSECAO').html('');
            },
            success: function(data) {
                $('select#SECAO_SUBSECAO').html(data);
                $("select#SECAO_SUBSECAO").attr("value",valor_subsecao) ;
            },
            error: function(){
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
    }
    
    
    /*
     * Dados da Unidade
     */
    if(valor_subsecao != ""){

        secao = valor_subsecao.split('|')[0];
        lotacao = valor_subsecao.split('|')[1];
        tipolotacao = valor_subsecao.split('|')[2];

        $.ajax({
            url: base_url + '/guardiao/unidadeperfil/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            beforeSend:function() {
                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('erroInputSelect');
                $('select#UNPE_SG_SECAO').html('');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('carregandoInputSelect');
                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: -1000;');
            },
            success: function(data) {

                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).attr('value','');
                $( "#combobox-input-button-UNPE_SG_SECAO" ).removeAttr('disabled','disabled');


                $('#UNPE_SG_SECAO').html(data);
                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).focus();
            },
            error: function(){
                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('x-form-field');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).val('Erro ao carregar.');
                $( "#combobox-input-text-UNPE_SG_SECAO" ).addClass('erroInputSelect');
                $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                $('select#UNPE_SG_SECAO').html('<option>Erro ao carregar</option>');
            }
        });
    }
});

