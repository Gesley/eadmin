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
var label_unidade;
var valor_pesquisa;
var pupe_matricula;
var pmat_matricula;
var resp_matricula;

/**
*
* Carrega os valores das variaveis via JSON   
*/
function carregaValores(valores){
    if(valores.secao != ""){
        valor_secao = valores.secao;
    }
    if(valores.subsecao != ""){
        valor_subsecao = valores.subsecao;
    }
    if(valores.unidade != ""){
        valor_unidade = valores.unidade;
    }
    if(valores.labelunidade != ""){
        label_unidade = valores.labelunidade;
    }
    if(valores.pesquisa != ""){
        valor_pesquisa = valores.pesquisa;
    }
    if(valores.pupe_matricula != ""){
        pupe_matricula = valores.pupe_matricula;
    }
    if(valores.pmat_matricula != ""){
        pmat_matricula = valores.pmat_matricula;
    }
    if(valores.resp_matricula != ""){
        resp_matricula = valores.resp_matricula;
    }
}


$(document).ready(function() {
            
    /*
     * Esconde Dialog de confirmação
     */
    $("#confirma").css('display','none');
    $('#historico').css('display', 'none');
    
    /**
    * Campo Pesquisa de Pessoas #######################################################################################
    * 
    */
    $("#PMAT_CD_MATRICULA").attr("style","width: 500px;");
    $("#PMAT_CD_MATRICULA").autocomplete({
        source: base_url+"/guardiao/perfilpessoaadm/ajaxpessoastribunal",
        minLength: 3,
        delay: 500,
        select: function( event, ui ) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $("#PMAT_CD_MATRICULA").blur();
        }
    });
    $("#PMAT_CD_MATRICULA").css('text-transform','uppercase');

    $("#RESPCAIXA_CD_MATRICULA").combobox({
        selected: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#RESPCAIXA_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").blur();
        },
        changed: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#RESPCAIXA_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").blur();
        } 
    });
    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr("style","width: 492px;");
    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").css('text-transform','uppercase');

    $("#PUPE_CD_MATRICULA").combobox({
        selected: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#PUPE_CD_MATRICULA').val(ui.item.value);
            $("#combobox-input-text-PUPE_CD_MATRICULA").blur();
        },
        changed: function(event, ui) {
            buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
            $('#PUPE_CD_MATRICULA').val(ui.item.value); 
            $("#combobox-input-text-PUPE_CD_MATRICULA").blur();
        } 
    });
    $("#combobox-input-text-PUPE_CD_MATRICULA").attr("style","width: 492px;");
    $("#combobox-input-text-PUPE_CD_MATRICULA").css('text-transform','uppercase');
    
    /**
    * Escolha da Seção #################################################################################################
    */
    $("select#TRF1_SECAO").change(
        function () {
            
            var secao = $(this).val().split('|')[0];
            var lotacao = $(this).val().split('|')[1];
            var tipolotacao = $(this).val().split('|')[2];

            $.ajax({
                url: base_url + '/guardiao/perfilpessoaadm/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                dataType : 'html',
                beforeSend:function() {
                    /*
                     * Esconder o submit de Associação
                     * Limpar o HTML do listBox
                     * Bloquear combobox Unidade
                     * Habilitar o select Subseção
                     */
                    $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                    $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                    $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).attr('value','');
                    $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('erroInputSelect');
                    $( "select#SECAO_SUBSECAO").html('');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
                    $("#GRUPOPESSOAS").val('');
                    $("#GRUPOPESSOAS").attr('disabled','disabled');
                    
                },
                success: function(data) {
                    $("#PMAT_CD_MATRICULA-element").css('display','none');
                    $("#PMAT_CD_MATRICULA-label").css('display','none');
                    $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                    $("#SECAO_CD_MATRICULA-element").css('display','none');
                    $("#SECAO_CD_MATRICULA-label").css('display','none');
                    $("#PUPE_CD_MATRICULA-element").css('display','block');
                    $("#PUPE_CD_MATRICULA-label").css('display','block');
                    $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                    $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                    $('select#SECAO_SUBSECAO').html(data);
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('carregandoInputSelect');
                    $( "#combobox-input-text-SECAO_SUBSECAO" ).focus();
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
        
    /*
     * Campo de pesquisa de Pessoas da Secao Judiciária
     * Está aqui em baixo por causa do carregamento da variavel do parametro da Secao
     */
    $("#SECAO_CD_MATRICULA").attr("style","width: 500px;");
    $("#SECAO_CD_MATRICULA").focus(function(){
        var sg = $('#TRF1_SECAO').val().split('|')[0];  
        $("#SECAO_CD_MATRICULA").autocomplete({
            source: base_url+"/guardiao/perfilpessoaadm/ajaxpessoassecao/secao/"+sg+"/",
            minLength: 3,
            delay: 500,
            select: function( event, ui ) {
                buscaPermissoes($('#LOTA_COD_LOTACAO').val(), ui.item.value);
                $("#SECAO_CD_MATRICULA").blur();
            }
        });
    });
    $("#SECAO_CD_MATRICULA").css('text-transform','uppercase');
        
    /**
    * Escolha da SubSeção #################################################################################################
    */
    $("select#SECAO_SUBSECAO").change(
        function () {
            
            /*
            * Verifica se a subseção é vazia
            */
            if($("select#SECAO_SUBSECAO").val() != ""){
                
                secao = $(this).val().split('|')[0];
                lotacao = $(this).val().split('|')[1];
                tipolotacao = $(this).val().split('|')[2];
    
                $.ajax({
                    url: base_url + '/guardiao/perfilpessoaadm/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
                    dataType : 'html',
                    beforeSend:function() {
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('erroInputSelect');
                        $('select#LOTA_COD_LOTACAO').html('');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).addClass('carregandoInputSelect');
                        $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: -1000;');
                    },
                    success: function(data) {
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).attr('value','');
                        $( "#combobox-input-button-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');
                        $( "#LOTA_COD_LOTACAO" ).html(data);
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('carregandoInputSelect');
                        $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: 0;');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).focus();
                    },
                    error: function(){
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('x-form-field');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).val('Erro ao carregar.');
                        $( "#combobox-input-text-LOTA_COD_LOTACAO" ).addClass('erroInputSelect');
                        $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: 0;');
                        $('select#LOTA_COD_LOTACAO').html('<option>Erro ao carregar</option>');
                    }
                });
            }else{
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).attr('disabled','disabled');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).val('');
                $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('disabled','disabled');
                $("#PMAT_CD_MATRICULA-element").css('display','none');
                $("#PMAT_CD_MATRICULA-label").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                $("#SECAO_CD_MATRICULA-element").css('display','none');
                $("#SECAO_CD_MATRICULA-label").css('display','none');
                $("#PUPE_CD_MATRICULA-element").css('display','block');
                $("#PUPE_CD_MATRICULA-label").css('display','block');
                $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                $("#GRUPOPESSOAS").val('');
                $("#GRUPOPESSOAS").attr('disabled','disabled');
            }
        });
        
        
    /**
    * Configuração do campo da Unidade ############################################################################
    */
    $("select#LOTA_COD_LOTACAO").combobox({
        selected: function(event, ui) {
    
            var secao = $('#TRF1_SECAO').val();
            var subsecao = $('#SECAO_SUBSECAO').val();
            var unidade = $('#LOTA_COD_LOTACAO').val();
    
            if((secao != '') && (subsecao != '') && (unidade != '')){
                $("#GRUPOPESSOAS").removeAttr('disabled','disabled');
                $("#GRUPOPESSOAS").attr('value','');
                $("#PMAT_CD_MATRICULA-element").css('display','none');
                $("#PMAT_CD_MATRICULA-label").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                $("#SECAO_CD_MATRICULA-element").css('display','none');
                $("#SECAO_CD_MATRICULA-label").css('display','none');
                $("#PUPE_CD_MATRICULA-element").css('display','block');
                $("#PUPE_CD_MATRICULA-label").css('display','block');
                $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
            }else{
                return false;
            }
        },
        changed: function(event, ui) {
    
            var secao = $('#TRF1_SECAO').val();
            var subsecao = $('#SECAO_SUBSECAO').val();
            var unidade = $('#LOTA_COD_LOTACAO').val();
    
            if((secao != '') && (subsecao != '') && (unidade != '')){
                $("#GRUPOPESSOAS").removeAttr('disabled','disabled');
                $("#GRUPOPESSOAS").attr('value','');
                $("#PMAT_CD_MATRICULA-element").css('display','none');
                $("#PMAT_CD_MATRICULA-label").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                $("#SECAO_CD_MATRICULA-element").css('display','none');
                $("#SECAO_CD_MATRICULA-label").css('display','none');
                $("#PUPE_CD_MATRICULA-element").css('display','block');
                $("#PUPE_CD_MATRICULA-label").css('display','block');
                $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
            }else{
                return false;
            }
        }
    });//fim da combobox
    
    /**
    * Configuração do combobox  UNIDADE
    */
    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('style','width: 492px;');
    $("#combobox-input-text-LOTA_COD_LOTACAO").css('text-transform','uppercase');
    aux_button_style =  $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style');
    $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' left: -20px; top: 5px;');
    aux_button_style =  $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style');
        
    /**
    * Tipo de pesquisa ##########################################################################################
    */
    $("#GRUPOPESSOAS").change(function(){
        var tipo_pesquisa = $(this).val();
        /**
        * Se o tipo de pesquisa for escolhido, sleciona o ajax a ser utilizado
        */
        if(tipo_pesquisa != ""){
                    
            if(tipo_pesquisa == "pessoasunidade"){
                        
                unidade = $("#LOTA_COD_LOTACAO").val();
                $.ajax({
                    url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/",
                    data: {
                        "unidade":unidade
                    },
                    beforeSend:function() {
                    },
                    success: function(data) {
                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
            
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA").attr('value','');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA").attr('value','');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA").attr('value','');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#combobox-input-text-PUPE_CD_MATRICULA").removeAttr('disabled','disabled');
                        $("#PUPE_CD_MATRICULA").html(data);
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                        $("#combobox-input-button-PUPE_CD_MATRICULA").removeAttr('disabled','disabled');
                    },
                    error: function(){
                    }
                }); 
                        
            }
            if(tipo_pesquisa == "pessoaacesso"){
                        
                unidade = $("#LOTA_COD_LOTACAO").val();
                $.ajax({
                    url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasresponsaveiscaixa/",
                    data: {
                        "unidade":unidade
                    },
                    beforeSend:function() {
                    },
                    success: function(data) {
                        $("#PUPE_CD_MATRICULA-element").css('display','none');
                        $("#PUPE_CD_MATRICULA").attr('value','');
                        $("#PUPE_CD_MATRICULA-label").css('display','none');
            
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA").attr('value','');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','block');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','block');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA").attr('value','');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA").html(data);
                        $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").removeAttr('disabled','disabled');
                        $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value','');
                        $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus();
                        $("#combobox-input-button-RESPCAIXA_CD_MATRICULA").removeAttr('disabled','disabled');
                    },
                    error: function(){
                    }
                }); 
            }
            if(tipo_pesquisa == "pessoastribunal"){
                $("#PUPE_CD_MATRICULA-element").css('display','none');
                $("#PUPE_CD_MATRICULA").attr('value','');
                $("#PUPE_CD_MATRICULA-label").css('display','none');
            
                $("#PMAT_CD_MATRICULA-element").css('display','block');
                $("#PMAT_CD_MATRICULA-label").css('display','block');
   
                $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA").attr('value','');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                $("#SECAO_CD_MATRICULA-element").css('display','none');
                $("#SECAO_CD_MATRICULA").attr('value','');
                $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                $("#PMAT_CD_MATRICULA").attr('value','');
                $("#PMAT_CD_MATRICULA").val('');
                $("#PMAT_CD_MATRICULA").focus();
            }
            if(tipo_pesquisa == "pessoassecao"){    
                $("#PUPE_CD_MATRICULA-element").css('display','none');
                $("#PUPE_CD_MATRICULA").attr('value','');
                $("#PUPE_CD_MATRICULA-label").css('display','none');
                    
                $("#PMAT_CD_MATRICULA-element").css('display','none');
                $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                $("#RESPCAIXA_CD_MATRICULA").attr('value','');
                $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                
                $("#SECAO_CD_MATRICULA-element").css('display','block');
                $("#SECAO_CD_MATRICULA").attr('value','');
                $("#SECAO_CD_MATRICULA-label").css('display','block');
   
                $("#SECAO_CD_MATRICULA").attr('value','');
                $("#SECAO_CD_MATRICULA").val('');
                $("#SECAO_CD_MATRICULA").focus();
            }
                    
        }else{
            $("#PUPE_CD_MATRICULA-element").css('display','block');
            $("#PUPE_CD_MATRICULA-label").css('display','block');
            
            $("#PMAT_CD_MATRICULA-element").css('display','none');
            $("#PMAT_CD_MATRICULA-label").css('display','none');
   
            $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
            $("#SECAO_CD_MATRICULA-element").css('display','none');
            $("#SECAO_CD_MATRICULA").attr('value','');
            $("#SECAO_CD_MATRICULA-label").css('display','none');
   
            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
            $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
            $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
        }
    });
        
        
    /**
    * Função que busca os perfis da pessoa em determinada Unidade 
    * Parâmetros: Unidade e Matricula do usuário
    * Retornará as ListBox preenchidas
    */
    function buscaPermissoes(unidade, matricula){
        if(unidade != '' && matricula != ''){
            url = base_url + '/guardiao/perfilpessoaadm/ajaxperfilpessoaadm/unidade/'+unidade+'/matricula/'+matricula;  
            $.ajax({
                url: url,
                dataType: 'html',
                processData: false, 
                success: function(data) {
                    /**
                    * Carega os campos de perfis
                    * Configura a listbox
                    * Configura os botões
                    */
                    $('#div_associar_perfil').html(data);
                    $('#historico').css('display', 'block');
                    $.configureBoxes();
                    $("#Salvar").focus();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                }
            });    
        }else{
            $('#Associar').css('display', 'none');
            $('#historico').css('display', 'none');
            return false;
        }
    }
      
    /**
    * #################################################################################################################
    * Ao clicar no botão Desfazer, voltam as configurações originais dos perfis daquela Unidade
    */
    $("#desfazer").live('click', function(){
        /**
        * Captura os valores
        */
        matricula = "";
        unidade = $("#LOTA_COD_LOTACAO").val();
        if($('#GRUPOPESSOAS').val() == "pessoasunidade"){
            matricula = $('#combobox-input-text-PUPE_CD_MATRICULA').val();
        }
        if($('#GRUPOPESSOAS').val() == "pessoaacesso"){
            matricula = $('#combobox-input-text-RESPCAIXA_CD_MATRICULA').val();
        }
        if($('#GRUPOPESSOAS').val() == "pessoastribunal"){
            matricula = $('#PMAT_CD_MATRICULA').val();
        }
    
        if(unidade != '' && matricula != ''){
            url = base_url + '/guardiao/perfilpessoaadm/ajaxperfilpessoaadm/unidade/'+unidade+'/matricula/'+matricula;  
            $.ajax({
                url: url,
                dataType: 'html',
                processData: false, 
                success: function(data) {
                    /**
                    * Carega os campos de perfis
                    * Configura a listbox
                    * Configura os botões
                    */
                    $('#div_associar_perfil').html(data);
                    $.configureBoxes();
                    $('#to1,#to2,#allTo1,#allTo2,#desfazer').button().css('width','30px').css('height','28px').css('margin-top','5px');
                }
            });    
        }else{
            $('#Associar').css('display', 'none');
            $('#historico').css('display', 'none');
            return false;
        }
            
    });
       
    /*
     * Selecionar os perfis ao submeter
     */
    $("#form").submit(function(){
        $("#box2View option").attr("selected","selected");
        return true;  
    });
    
    
    /*
    * Configurações iniciais da página 
    *         
    */
   
    //Tipo de pesquisa
    $("#GRUPOPESSOAS").val('');
    $("#GRUPOPESSOAS").attr('disabled','disabled');

    $("#PMAT_CD_MATRICULA-element").css('display','none');
    $("#PMAT_CD_MATRICULA-label").css('display','none');
   
    $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
    $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
    $("#SECAO_CD_MATRICULA-element").css('display','none');
    $("#SECAO_CD_MATRICULA-label").css('display','none');
   
    $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
    $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
    
    $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
    $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
    $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
    
    if($("select#SECAO_SUBSECAO").val() != ""){
        $("#combobox-input-text-LOTA_COD_LOTACAO").removeAttr('disabled','disabled');
        $("#combobox-input-button-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');
    } 
    
    /*
     * Confirmação de SAVE #############################################################################
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
                        $("select#TRF1_SECAO").val('');
                        $("select#SECAO_SUBSECAO").attr('disabled','disabled');
                        $("select#SECAO_SUBSECAO").val('');
                        $('#historico').css('display', 'none');
                        
                        //Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');
                        $("#GRUPOPESSOAS").attr('disabled','disabled');

                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        
                        $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                        $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                        $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                    }
                }
            });
        }
    });
    
      
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
                        $("select#SECAO_SUBSECAO").val('');
                        $('#historico').css('display', 'none');
                        
                        //Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');
                        $("#GRUPOPESSOAS").attr('disabled','disabled');

                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        
                        $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                        $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                        $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                    }
                }
            });
        }
    });
    
    
    $("#combobox-input-text-LOTA_COD_LOTACAO").focus(function(){
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
                        $('#historico').css('display', 'none');
                        
                        //Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');
                        $("#GRUPOPESSOAS").attr('disabled','disabled');

                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        
                        $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                    }
                }
            });
        }
    });
    
    $("#GRUPOPESSOAS").focus(function(){
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
                        $('#historico').css('display', 'none');
                        
                        //Tipo de pesquisa
                        $("#GRUPOPESSOAS").val('');

                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('disabled','disabled');
                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                        $("#combobox-input-button-PUPE_CD_MATRICULA").attr('disabled','disabled');
                    }
                }
            });
        }
    });
    
    $("#PMAT_CD_MATRICULA").focus(function(){
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
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display','none');
                        $("#PUPE_CD_MATRICULA-label").css('display','none');
                        
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','block');
                        $("#PMAT_CD_MATRICULA-label").css('display','block');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');

                        $("#PMAT_CD_MATRICULA").attr('value','');

                    }
                }
            });
        }
    });
    
    $("#SECAO_CD_MATRICULA").focus(function(){
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
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display','none');
                        $("#PUPE_CD_MATRICULA-label").css('display','none');
                        
                        $("#SECAO_CD_MATRICULA-element").css('display','block');
                        $("#SECAO_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');

                        $("#SECAO_CD_MATRICULA").attr('value','');

                    }
                }
            });
        }
    });
    
    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus(function(){
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
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display','none');
                        $("#PUPE_CD_MATRICULA-label").css('display','none');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','block');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','block');

                        $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value','');

                    }
                }
            });
        }
    });
    
    $("#combobox-input-text-PUPE_CD_MATRICULA").focus(function(){
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
                        $('#historico').css('display', 'none');

                        $("#PUPE_CD_MATRICULA-element").css('display','block');
                        $("#PUPE_CD_MATRICULA-label").css('display','block');
                        
                        $("#PMAT_CD_MATRICULA-element").css('display','none');
                        $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                        $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                        $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');

                        $("#SECAO_CD_MATRICULA-element").css('display','none');
                        $("#SECAO_CD_MATRICULA-label").css('display','none');

                        $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');

                    }
                }
            });
        }
    });
    
    /*
     * Carrega As Subsecoes Caso o formulario ja venha preenchido (Ex: Usuários das Seções)
     */
    if($("select#TRF1_SECAO").val() != ""){
        
        var secao = $("select#TRF1_SECAO").val().split('|')[0];
        var lotacao = $("select#TRF1_SECAO").val().split('|')[1];
        var tipolotacao = $("select#TRF1_SECAO").val().split('|')[2];

        $.ajax({
            url: base_url + '/guardiao/perfilpessoaadm/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            beforeSend:function() {
                /*
                * Esconder o submit de Associação
                * Limpar o HTML do listBox
                * Bloquear combobox Unidade
                * Habilitar o select Subseção
                */
                $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).attr('value','');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('erroInputSelect');
                $( "select#SECAO_SUBSECAO").html('');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
                $( "#GRUPOPESSOAS" ).attr('disabled','disabled');
            },
            success: function(data) {
                $('select#SECAO_SUBSECAO').html(data);
                $("select#SECAO_SUBSECAO").attr("value",valor_subsecao) ;
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).focus();
                
            },
            error: function(){
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('x-form-field');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).val('Erro ao carregar.');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('erroInputSelect');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: 0;');
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
    }
    
    /*
     * Manter ultima pesquisa ####################################################################
     */
    if(valor_secao != ""){
        
        var secao = valor_secao.split('|')[0];
        var lotacao = valor_secao.split('|')[1];
        var tipolotacao = valor_secao.split('|')[2];

        $.ajax({
            url: base_url + '/guardiao/perfilpessoaadm/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            beforeSend:function() {
                /*
                * Esconder o submit de Associação
                * Limpar o HTML do listBox
                * Bloquear combobox Unidade
                * Habilitar o select Subseção
                */
                $("#combobox-input-text-LOTA_COD_LOTACAO").val('');
                $("#combobox-input-text-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $("#combobox-input-button-LOTA_COD_LOTACAO").attr('disabled','disabled');
                $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).attr('value','');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('erroInputSelect');
                $( "select#SECAO_SUBSECAO").html('');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('carregandoInputSelect');
                $( "#GRUPOPESSOAS" ).attr('disabled','disabled');
                    
            },
            success: function(data) {
                $('select#SECAO_SUBSECAO').html(data);
                $("select#SECAO_SUBSECAO").attr("value",valor_subsecao) ;
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).focus();
                
            },
            error: function(){
                $( "#combobox-input-text-SECAO_SUBSECAO" ).removeClass('x-form-field');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).val('Erro ao carregar.');
                $( "#combobox-input-text-SECAO_SUBSECAO" ).addClass('erroInputSelect');
                $( "#combobox-input-button-SECAO_SUBSECAO" ).attr('style',aux_button_style+' z-index: 0;');
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
    }
    
    if(valor_subsecao != ""){
        
        secao = valor_subsecao.split('|')[0];
        lotacao = valor_subsecao.split('|')[1];
        tipolotacao = valor_subsecao.split('|')[2];
    
        $.ajax({
            url: base_url + '/guardiao/perfilpessoaadm/ajaxunidadebysecao/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            beforeSend:function() {
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('erroInputSelect');
                $('select#LOTA_COD_LOTACAO').html('');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).addClass('carregandoInputSelect');
                $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: -1000;');
            },
            success: function(data) {
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).attr('value','');
                $( "#combobox-input-button-LOTA_COD_LOTACAO" ).removeAttr('disabled','disabled');
                $( "#LOTA_COD_LOTACAO" ).html(data);
                if(label_unidade != ""){
                    $( "#combobox-input-text-LOTA_COD_LOTACAO" ).val(label_unidade);
                    $( "#LOTA_COD_LOTACAO" ).val(valor_unidade);
                    $( "#GRUPOPESSOAS" ).removeAttr('disabled','disabled');
                }
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: 0;');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).focus();
            },
            error: function(){
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).removeClass('x-form-field');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).val('Erro ao carregar.');
                $( "#combobox-input-text-LOTA_COD_LOTACAO" ).addClass('erroInputSelect');
                $( "#combobox-input-button-LOTA_COD_LOTACAO" ).attr('style',aux_button_style+' z-index: 0;');
                $('select#LOTA_COD_LOTACAO').html('<option>Erro ao carregar</option>');
            }
        });
    }
    
    if(valor_subsecao != ""){
        $("#GRUPOPESSOAS").removeAttr('disabled','disabled');
        $("#GRUPOPESSOAS").attr('value',valor_pesquisa);
    }
      
    if(valor_pesquisa != ""){

        if(valor_pesquisa == "pessoasunidade"){
                        
            unidade = valor_unidade;

            $.ajax({
                url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasdaunidade/",
                data: {
                    "unidade":unidade
                },
                beforeSend:function() {
                },
                success: function(data) {
                    $("#PUPE_CD_MATRICULA-element").css('display','block');
                    $("#PUPE_CD_MATRICULA-label").css('display','block');
            
                    $("#PMAT_CD_MATRICULA-element").css('display','none');
                    $("#PMAT_CD_MATRICULA").attr('value','');
                    $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                    $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
                    $("#RESPCAIXA_CD_MATRICULA").attr('value','');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                        
                    $("#SECAO_CD_MATRICULA-element").css('display','none');
                    $("#SECAO_CD_MATRICULA").attr('value','');
                    $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                    $("#combobox-input-text-PUPE_CD_MATRICULA").removeAttr('disabled','disabled');
                    $("#PUPE_CD_MATRICULA").html(data);
                    $("#combobox-input-text-PUPE_CD_MATRICULA").attr('value','');
                    $("#combobox-input-text-PUPE_CD_MATRICULA").focus();
                    $("#combobox-input-button-PUPE_CD_MATRICULA").removeAttr('disabled','disabled');
                },
                error: function(){
                }
            });
                        
        }
        if(valor_pesquisa == "pessoaacesso"){
                        
            unidade = valor_unidade;
            
            $.ajax({
                url: base_url + "/guardiao/perfilpessoaadm/ajaxpessoasresponsaveiscaixa/",
                data: {
                    "unidade":unidade
                },
                beforeSend:function() {
                },
                success: function(data) {
                    $("#PUPE_CD_MATRICULA-element").css('display','none');
                    $("#PUPE_CD_MATRICULA").attr('value','');
                    $("#PUPE_CD_MATRICULA-label").css('display','none');
            
                    $("#PMAT_CD_MATRICULA-element").css('display','none');
                    $("#PMAT_CD_MATRICULA").attr('value','');
                    $("#PMAT_CD_MATRICULA-label").css('display','none');
   
                    $("#RESPCAIXA_CD_MATRICULA-element").css('display','block');
                    $("#RESPCAIXA_CD_MATRICULA-label").css('display','block');
                        
                    $("#SECAO_CD_MATRICULA-element").css('display','none');
                    $("#SECAO_CD_MATRICULA").attr('value','');
                    $("#SECAO_CD_MATRICULA-label").css('display','none');
   
                    $("#RESPCAIXA_CD_MATRICULA").html(data);
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").removeAttr('disabled','disabled');
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").attr('value','');
                    $("#combobox-input-text-RESPCAIXA_CD_MATRICULA").focus();
                    $("#combobox-input-button-RESPCAIXA_CD_MATRICULA").removeAttr('disabled','disabled');
                },
                error: function(){
                }
            }); 
        }
        if(valor_pesquisa == "pessoastribunal"){
            $("#PUPE_CD_MATRICULA-element").css('display','none');
            $("#PUPE_CD_MATRICULA").attr('value','');
            $("#PUPE_CD_MATRICULA-label").css('display','none');
            
            $("#PMAT_CD_MATRICULA-element").css('display','block');
            $("#PMAT_CD_MATRICULA-label").css('display','block');
   
            $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
            $("#RESPCAIXA_CD_MATRICULA").attr('value','');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                
            $("#SECAO_CD_MATRICULA-element").css('display','none');
            $("#SECAO_CD_MATRICULA").attr('value','');
            $("#SECAO_CD_MATRICULA-label").css('display','none');
   
            $("#PMAT_CD_MATRICULA").attr('value','');
            $("#PMAT_CD_MATRICULA").val('');
            $("#PMAT_CD_MATRICULA").focus();
        }
        if(valor_pesquisa == "pessoassecao"){    
            $("#PUPE_CD_MATRICULA-element").css('display','none');
            $("#PUPE_CD_MATRICULA").attr('value','');
            $("#PUPE_CD_MATRICULA-label").css('display','none');
            
            $("#PMAT_CD_MATRICULA-element").css('display','none');
            $("#PMAT_CD_MATRICULA-label").css('display','none');
   
            $("#RESPCAIXA_CD_MATRICULA-element").css('display','none');
            $("#RESPCAIXA_CD_MATRICULA").attr('value','');
            $("#RESPCAIXA_CD_MATRICULA-label").css('display','none');
                
            $("#SECAO_CD_MATRICULA-element").css('display','block');
            $("#SECAO_CD_MATRICULA").attr('value','');
            $("#SECAO_CD_MATRICULA-label").css('display','block');
   
            $("#SECAO_CD_MATRICULA").attr('value','');
            $("#SECAO_CD_MATRICULA").val('');
            $("#SECAO_CD_MATRICULA").focus();
        }
        
    }
      
});