/**
 *
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
    $("select#TRF1_SECAO").focus(function() {
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
    
    $("select#TRF1_SECAO").change( function () {
            
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
            error: function() {
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
        /** Carrega os grupos de serviços */
        var uf = $(this).val().split("|",1);
        var url = base_url + '/sosti/relatoriossolicitacoes/ajaxcaixaentrada/sigla/'+uf;  
        $.ajax({
            url: url,
            dataType: 'html',
            processData: false, 
             beforeSend:function() {
                $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('erroInputSelect');
                $('select#SGRS_ID_GRUPO').html('');
                $( "#combobox-input-text-SGRS_ID_GRUPO" ).addClass('carregandoInputSelect');
                $( "#combobox-input-button-SGRS_ID_GRUPO" ).attr('style',aux_button_style+' z-index: -1000;');
            },
            success: function(data) {
                $( "#combobox-input-text-SGRS_ID_GRUPO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SGRS_ID_GRUPO" ).attr('value','');
                $( "#combobox-input-button-SGRS_ID_GRUPO" ).removeAttr('disabled','disabled');
                $('#SGRS_ID_GRUPO').html(data);
                $( "#combobox-input-text-SGRS_ID_GRUPO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-button-SGRS_ID_GRUPO" ).attr('style',aux_button_style+' z-index: 0;');
                $( "#combobox-input-text-SGRS_ID_GRUPO" ).focus();
            }
        });
    });
        
    /**
    * Escolha da SubSeção 
    */
    $("select#SECAO_SUBSECAO").focus(function() {
        /*
        * Se existir o campo da validacao, entao perguntar se o usuario qer salvar as alteracoes
        */
        if($("#form_validator").length) {     
            /**
            * 
            * Dialog de confirmação
            */
            $("#confirma").dialog({
                resizable: false,
                modal: true,
                title: "Alerta",
                buttons:{
                    Sim:function() {
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
                            error: function() {
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
    
    $("select#SECAO_SUBSECAO").change( function() {
            /*
             * Esconder o submit de Associação
             */
            $("#Salvar").css('display','none');
            $('#div_associar_perfil').empty();
        
            /**
             * Verifica se a subseção é vazia
             */
            if($("select#SECAO_SUBSECAO").val() != "") {
            
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
                        $('#UNPE_SG_SECAO').html('<option value="" >Selecione</option>' + data);
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).removeClass('carregandoInputSelect');
                        $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' z-index: 0;');
                        $( "#combobox-input-text-UNPE_SG_SECAO" ).focus();
                    },
                    error: function() {
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
        
    $("select#SGRS_ID_GRUPO").change( function() {
        /** Carrega os serviços */
        var cx = $(this).val().split("|",1);
        var url = base_url + '/sosti/relatoriossolicitacoes/ajaxservicocaixa/cx/'+cx;  
        $.ajax({
            url: url,
            dataType: 'html',
            processData: false, 
             beforeSend:function() {
                $( "#combobox-input-text-SSER_ID_SERVICO" ).removeClass('erroInputSelect');
                $('select#SSER_ID_SERVICO').html('');
                $( "#combobox-input-text-SSER_ID_SERVICO" ).addClass('carregandoInputSelect');
                $( "#combobox-input-button-SSER_ID_SERVICO" ).attr('style',aux_button_style+' z-index: -1000;');
            },
            success: function(data) {
                $( "#combobox-input-text-SSER_ID_SERVICO" ).removeAttr('disabled','disabled');
                $( "#combobox-input-text-SSER_ID_SERVICO" ).attr('value','');
                $( "#combobox-input-button-SSER_ID_SERVICO" ).removeAttr('disabled','disabled');
                $('#SSER_ID_SERVICO').html(data);
                $( "#combobox-input-text-SSER_ID_SERVICO" ).removeClass('carregandoInputSelect');
                $( "#combobox-input-button-SSER_ID_SERVICO" ).attr('style',aux_button_style+' z-index: 0;');
                $( "#combobox-input-text-SSER_ID_SERVICO" ).focus();
            }
        });
    });  
    
    /**
     * Configuração do combobox 
     */
    $("#combobox-input-text-UNPE_SG_SECAO").attr('style','width: 492px;');
    $("#combobox-input-text-UNPE_SG_SECAO").css('text-transform','uppercase');
    aux_button_style =  $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style');
    $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style',aux_button_style+' left: -20px; top: 5px;');
    aux_button_style =  $( "#combobox-input-button-UNPE_SG_SECAO" ).attr('style');

    $(function() {
        $('#DATA_INICIAL').datetimepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
                                 'Outubro','Novembro','Dezembro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            numberOfMonths: 1,
            changeMonth: true,
            changeYear: true,
            changeMonth: true,
            showSecond: true,
            timeFormat: 'hh:mm:ss',
            timeOnlyTitle: 'Escolha o intervalo de tempo',
            timeText: 'Tempo',
            hourText: 'Hora',
            minuteText: 'Minutos',
            secondText: 'Segundos',
            currentText: 'Agora',
            closeText: 'OK',
            onClose: function(dateText, inst) {
                var endDateTextBox = $('#DATA_FINAL');
                if (endDateTextBox.val() != '') {
                    var testStartDate = new Date(dateText);
                    var testEndDate = new Date(endDateTextBox.val());
                    if (testStartDate > testEndDate)
                        endDateTextBox.val((dateText)?(dateText.substring(0,11) + '23:59:59'):(''));
                    }
                    else {
                        endDateTextBox.val((dateText)?(dateText.substring(0,11) + '23:59:59'):(''));
                    }
            },
            onSelect: function (selectedDateTime){
                var start = $(this).datetimepicker('getDate');
                $('#DATA_FINAL').datetimepicker('option', 'minDate', new Date(start.getTime()));
            }
        });
        $('#DATA_FINAL').datetimepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
            dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
            dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
                                 'Outubro','Novembro','Dezembro'],
            monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            numberOfMonths: 1,
            changeMonth: true,
            changeYear: true,
            changeMonth: true,
            showSecond: true,
            timeFormat: 'hh:mm:ss',
            timeOnlyTitle: 'Escolha o intervalo de tempo',
            timeText: 'Tempo',
            hourText: 'Hora',
            minuteText: 'Minutos',
            secondText: 'Segundos',
            currentText: 'Agora',
            closeText: 'OK'
        });
    });
    
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
    
    $("#SOLICITANTE").autocomplete({
        source: base_url+'/sosti/solicitacao/ajaxpessoasporordemde',
        minLength: 3,
        delay: 100
    });
    
    $("#Pesquisar").click( function() {
        $("#pesq_div").hide();
        /** Url da grid do relatório */
        var url = base_url + '/sosti/relatoriossolicitacoes/gridsolicitacoessetor';  
        $.ajax({
            url: url,
            dataType: 'html',
            processData: false,
            data: $('#form').serialize(),
            type: 'POST',
            success: function(data) {
                $('#grid').html(data);
            },
            error: function () {
                $('#flashMessagesView').attr('class', 'error');
                $('#flashMessagesView').html("<strong>Erro: </strong> Não foi possível carregar o relatório");
            }
        });
        return false;
    });
    
    $("#botao_ajuda_recolhe").click( function() {
        $("#pesq_div").hide();
    });
    
    $("#pesquisar").click( function() {
        $("#pesq_div").show();
    });
    
});