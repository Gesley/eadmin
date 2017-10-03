/**
 * @category    GUARDIAO
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author                        Daniel Rodrigues
 * @license                       FREE, keep original copyrights
 * @version                       controlada pelo SVN
 */


var matricula = "";
var nome_matricula = "";
var valor_unidade = "";
var label_unidade = "";
var valor_pesquisa = "";
var secao = "";

/*
 * Carrega as variaveis com os valores vindos do servidor
 * Parametros passados via JSon
 */
function carregaValores(valores){
    if(valores.matricula != ""){
        nome_matricula = valores.matricula;
        matricula = valores.matricula.split(' - ')[0];
    }
    if(valores.pesquisa != ""){
        valor_pesquisa = valores.pesquisa;
        $("#tipo_pesquisa-" + valor_pesquisa).attr('checked','checked');
    }
    if(valores.unidade != ""){
        valor_unidade = valores.unidade;
        label_unidade = valores.labelunidade;
    }
    if(valores.secao != ""){
        secao = valores.secao;
    }
}

$("document").ready(function() {
    
    /*
     * CONFIGURAÇÕES INICIAIS ##########################################################################################
     */
    if(matricula != ""){
        buscaUnidade(matricula);
    }
    if(valor_unidade != ""){
        pessoasUnidade(valor_unidade);
    }
    /**
     * Esconde Dialog de confirmação
     */
    $("#confirma").css('display','none');
    /**
     * CONTROLE DE TROCA DE FORMULÁRIO
     * Inicializa a página mostrando o formulário marcado
     */
    trocarForm();
    /**
     *  Controla a mudança de formulário
     */
    $("[name=tipo_pesquisa]").click(function(){
        confirmacao();
        //$('#permissoes').html('');
        trocarForm();
    });
    
    /**
     * Quando chamada, troca o formulário da view
     */
    function trocarForm(){
        $("#formPorPessoa").hide();
        $("#formPorPessoa :input").attr("disabled", true);
        $("#formPorPessoa :input").attr("value", "");
        
        $("#formPorUnidade").hide();
        $("#formPorUnidade :input").attr("disabled", true);
        $("#formPorUnidade :input").attr("value", "");
        
        $('#Salvar').css('display', 'none');
        $('#historico').css('display', 'none');
        
        $("#" + $("[name=tipo_pesquisa]:checked").val()).show();
        $("#" + $("[name=tipo_pesquisa]:checked").val() + " :input").removeAttr("disabled");
    }
    
    /**
     * Escondendo elemento Associar, histórico e os perfis
     */
    $('#historico').css('display', 'none');
    $('#Associar').css('display', 'none');
    
    /**
     *  FORMULÁRIO POR PESSOA ##########################################################################################
     */
    $("#formPorPessoa > dd").children("#PMAT_CD_MATRICULA").autocomplete({
        source: base_url+"/guardiao/perfilpessoa/ajaxpessoassecao/secao/"+secao+"/",
        minLength: 3,
        delay: 500,
        select: function( event, ui ) {
            $('#Salvar').css('display', 'none');
            $('#historico').css('display', 'none');
            $('#permissoes').html('');
            matricula = ui.item.value.split(' - ')[0];
            /*
             * Chama function para carregar as unidades da pessoa
             */
            buscaUnidade(matricula);
        }
    });
    
    $("#formPorPessoa").find("#LOTA_COD_LOTACAO").combobox({
        
        selected: function( event, ui ){
            
            if($(this).val() == ""){
                $('#Salvar').css('display', 'none');
                $('#historico').css('display', 'none');
                $('#permissoes').html('');
            }else{
        
                /**
                 * Ao selecionar uma Unidade, buscar permissoes via AJAX
                 * Captura os valores
                 */
                var tipo_pesquisa = $("[name=tipo_pesquisa]:checked").val();
                var unidade = "";
                var matricula = "";
            
                /*
                 * Verifica qual o tipo de pesquisa
                 */
                if(tipo_pesquisa == 'formPorPessoa'){
                    matricula = $('#PMAT_CD_MATRICULA').val();
                    unidade = $("#formPorPessoa #LOTA_COD_LOTACAO").val();
                }else{
                    if(tipo_pesquisa == 'formPorUnidade'){
                        matricula = $('#PUPE_CD_MATRICULA').val();
                        unidade = $("#formPorUnidade #LOTA_COD_LOTACAO").val();
                    }
                }

                if(unidade != '' && matricula != ''){
                    url = base_url+'/guardiao/perfilpessoa/ajaxperfilpessoa/unidade/'+unidade+'/matricula/'+matricula;  
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
                            $('#permissoes').html(data);
                            $('#Salvar').focus();
                            $.configureBoxes();
                            $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                        }
                    });    
                }else{
                    $('#Salvar').css('display', 'none');
                    $('#historico').css('display', 'none');
                    return false;
                }
            }  
        }
    });
    $("#formPorPessoa").find("#combobox-input-text-LOTA_COD_LOTACAO").css('width', '500px');
    $("#formPorPessoa").find("#combobox-input-text-LOTA_COD_LOTACAO").css('text-transform','uppercase');
    
    /*
     * Ao focar no campo de Unidade, verificar se deseja salvar
     */
    $("#formPorPessoa").find("#combobox-input-text-LOTA_COD_LOTACAO").focus(function(){
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
                        $('#permissoes').empty();
                        $("#Salvar").css('display','none');
                        $("#combobox-input-text-LOTA_COD_LOTACAO").attr('value','');
                        $("#combobox-input-text-LOTA_COD_LOTACAO").focus();
                    }
                }
            });
        }else{
            $('#permissoes').html('');
            $('#Salvar').css('display','none');
            $('#historico').css('display', 'none');
        }
        
    });
    
    /**
     *  FORMULÁRIO POR UNIDADE ##########################################################################################
     *
     *  Configurando Combobox do formulário POR UNIDADE
     */
    $("#formPorUnidade > div > dd").children("#PUPE_CD_MATRICULA").combobox({
        
        selected: function( event, ui){
            /**
             * Ao selecionar uma Unidade, buscar permissoes via AJAX
             * Captura os valores
             */
            var tipo_pesquisa = $("[name=tipo_pesquisa]:checked").val();
            var unidade = "";
            var matricula = "";
            
            /*
             * Verifica qual o tipo de pesquisa
             */
            if(tipo_pesquisa == 'formPorPessoa'){
                matricula = $('#PMAT_CD_MATRICULA').val();
                unidade = $("#formPorPessoa #LOTA_COD_LOTACAO").val();
            }else{
                if(tipo_pesquisa == 'formPorUnidade'){
                    matricula = $('#PUPE_CD_MATRICULA').val();
                    unidade = $("#formPorUnidade #LOTA_COD_LOTACAO").val();
                }
            }

            if(unidade != '' && matricula != ''){
                url = base_url+'/guardiao/perfilpessoa/ajaxperfilpessoa/unidade/'+unidade+'/matricula/'+matricula;  
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
                        $('#permissoes').html(data);
                        $.configureBoxes();
                        $('#Salvar').focus();
                        $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                    }
                });    
            }else{
                $('#Salvar').css('display', 'none');
                $('#historico').css('display', 'none');
                return false;
            }
        }
    });
    $("#combobox-input-text-PUPE_CD_MATRICULA").attr("style","width: 500px;");
    $("#formPorUnidade").find("#combobox-input-text-PUPE_CD_MATRICULA").css('text-transform','uppercase');
    
    
    $("#formPorUnidade").find("#LOTA_COD_LOTACAO").combobox({
        
        selected: function( event, ui ){
            
            if($(this).val() != ''){
                unidade = $(this).val();
                $.ajax({
                    url: base_url + "/guardiao/perfilpessoa/ajaxpessoasdaunidade/",
                    data: {
                        "unidade":unidade
                    },
                    beforeSend:function() {
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr("value","");
                        $("#formPorUnidade > div > dd").children("#PUPE_CD_MATRICULA").html("");
                        $('#permissoes').empty();
                        $('#Salvar').css('display', 'none');
                    },
                    success: function(data) {
                        $("#formPorUnidade > div > dd").children("#PUPE_CD_MATRICULA").html(data);
                        $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                    },
                    error: function(){
                    }
                });   
            } 
        }
    });
    $("#formPorUnidade").find("#combobox-input-text-LOTA_COD_LOTACAO").css('width', '500px');
    $("#formPorUnidade").find("#combobox-input-text-LOTA_COD_LOTACAO").css('text-transform','uppercase');
    
    /*
     * Limpa os campos e formulário de associação, para evitar erros e problemas na inserção
     */
    $("#formPorUnidade").find("#combobox-input-text-LOTA_COD_LOTACAO").focus(function(){
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
                        $('#permissoes').empty();
                        $("#Salvar").css('display','none');
                        $("#combobox-input-text-LOTA_COD_LOTACAO").focus();
                        $("#PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                    }
                }
            });
        }else{
            $('#permissoes').html('');
            $('#Salvar').css('display','none');
            $('#historico').css('display', 'none');
        }
    });
    
    /*
     * Limpa os campos e formulário de associação, para evitar erros e problemas na inserção
     */
    $("#formPorUnidade > div > dd").children("#combobox-input-text-PUPE_CD_MATRICULA").focus(function(){
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
                        $('#permissoes').empty();
                        $("#Salvar").css('display','none');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                    }
                }
            });
        }else{
            $('#permissoes').html('');
            $('#Salvar').css('display','none');
            $('#historico').css('display', 'none');
        }
    });
    
    
    /*
     * CONFIGURAÇÕES GERAIS DO SCRIPT ##########################################################################################
     *
     */
    
    $("#PMAT_CD_MATRICULA").click(function(){
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
                        $('#permissoes').empty();
                        $("#Salvar").css('display','none');
                        $("#PMAT_CD_MATRICULA" ).attr('value','');
                        $("#PMAT_CD_MATRICULA").focus();
                        $("#combobox-input-text-LOTA_COD_LOTACAO").attr('value','');
                        $("#LOTA_COD_LOTACAO").html('');
                    }
                }
            });
        }else{
            $('#permissoes').html('');
            $('#Salvar').css('display','none');
            $('#historico').css('display', 'none');
        }
    });
    
    /**
     * Ao clicar no botão Desfazer, voltam as configurações originais dos perfis daquela Unidade ################################
     */
    $("#desfazer").live('click', function(){

        /**
         * Captura os valores
         */
        var tipo_pesquisa = $("[name=tipo_pesquisa]:checked").val();
        var unidade = "";
        var matricula = "";
            
        /*
         * Verifica qual o tipo de pesquisa
         */
        if(tipo_pesquisa == 'formPorPessoa'){
            matricula = $('#PMAT_CD_MATRICULA').val();
            unidade = $("#formPorPessoa #LOTA_COD_LOTACAO").val();
        }else{
            if(tipo_pesquisa == 'formPorUnidade'){
                matricula = $('#PUPE_CD_MATRICULA').val();
                unidade = $("#formPorUnidade #LOTA_COD_LOTACAO").val();
            }
        }

        if(unidade != '' && matricula != ''){
            url = base_url+'/guardiao/perfilpessoa/ajaxperfilpessoa/unidade/'+unidade+'/matricula/'+matricula;  
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
                    $('#permissoes').html(data);
                    $.configureBoxes();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                }
            });    
        }else{
            $('#Salvar').css('display', 'none');
            $('#historico').css('display', 'none');
            return false;
        }
        
    });
    
    /*
     * Busca todas as caixas que a matricula informada te acesso
     */
    function buscaUnidade(matricula){
        $.ajax({
            url: base_url+"/guardiao/perfilpessoa/ajaxcaixaspessoa/PMAT_CD_MATRICULA/"+matricula+'/caixa/<?=$this->caixa;?>',
            beforeSend:function() {
                $("#LOTA_COD_LOTACAO").removeClass('erroInputSelect');
                $("#LOTA_COD_LOTACAO").val("");
                $("#LOTA_COD_LOTACAO").addClass('carregandoInputSelect');
            },
            success: function(data) { 
                $("#LOTA_COD_LOTACAO").html(data);
                $("#combobox-input-text-LOTA_COD_LOTACAO").focus();
                $("#LOTA_COD_LOTACAO").removeClass('carregandoInputSelect');
            },
            error: function(){
                $("#LOTA_COD_LOTACAO").removeClass('x-form-field');
                $("#LOTA_COD_LOTACAO").val('Erro ao carregar.');
                $("#LOTA_COD_LOTACAO").addClass('erroInputSelect');
                $("#LOTA_COD_LOTACAO").html('<option>Erro ao carregar</option>');
            }
        }); 
    }
    
    /**
     * Busca as pessoas de uma Unidade informada
     */
    function pessoasUnidade(unidade){
        if(unidade != ''){
            $.ajax({
                url: base_url + "/guardiao/perfilpessoa/ajaxpessoasdaunidade/",
                data: {
                    "unidade":unidade
                },
                beforeSend:function() {
                    $("#combobox-input-text-PUPE_CD_MATRICULA").attr("value","");
                    $("#formPorUnidade > div > dd").children("#PUPE_CD_MATRICULA").html("");
                    $('#permissoes').empty();
                    $('#Salvar').css('display', 'none');
                },
                success: function(data) {
                    $("#formPorUnidade > div > dd").children("#PUPE_CD_MATRICULA").html(data);
                    $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                },
                error: function(){
                }
            });   
        } 
    }
    
    /**
     * Funcao de confirmação de SAVE geral para mudança de form
     */
    function confirmacao(){
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
                        $('#permissoes').empty();
                        $("#Salvar").css('display','none');
                    }
                }
            });
        }else{
            $('#permissoes').html('');
            $('#Salvar').css('display','none');
            $('#historico').css('display', 'none');
        }
    }
       
    /**
     * Ao submeter o formulário, seleciona todos os perfis associados 
     */
    $("#form").submit(function(){
        $("#box2View option").attr("selected","selected");
        return true;  
    });
    
    /**
     * Setando valor da ultima pesquisa ################################################################
     * Se tiver retornado matricula da view, então setar ela como ultima pesquisa
     */
    if(matricula != ""){
        $("#formPorPessoa > dd").children("#PMAT_CD_MATRICULA").val(nome_matricula);
    }
    if(valor_unidade != ""){
        $("#formPorUnidade").find("#combobox-input-text-LOTA_COD_LOTACAO").val(label_unidade);
        $("#formPorUnidade").find("#LOTA_COD_LOTACAO").val(valor_unidade);
    }
    /*
     * Foco inicial da página
     */
    $("#PMAT_CD_MATRICULA").focus();
});