/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var GLOBAL_indice_abas = 0;
var xhr_abrir_documento;

var grid_tbody_tr;
$(function() {
    /*Carrega a linha da tabela*/
    grid_tbody_tr = $("table.grid > tbody > tr");
    /*Ações quando clica-se uma vez na linha*/
    grid_tbody_tr.click(
        function() {
            grid_tbody_tr.removeClass('hover_nav');

            var this_tr = $(this);
            var is_checked_tr = $(this).attr('marcado');

            var input_check_box = $(this).find('input[type=checkbox]');
            var is_checked_input = input_check_box.attr('checked');

            if ((is_checked_input == undefined && is_checked_tr == undefined) || (is_checked_input != undefined && is_checked_tr == undefined)) {
                input_check_box.attr('checked', 'checked');
                this_tr.attr('marcado', 'marcado');
                this_tr.addClass('hover');
            } else {
                input_check_box.removeAttr('checked');
                this_tr.removeAttr('marcado');
                this_tr.removeClass('hover');
            }
            input_check_box.focus();
        }
        );
    /*Ações quando clica-se duas vezes sobre a linha (abrir detalhe do documento)*/        
    grid_tbody_tr.dblclick(
        function() {
            var this_tr = $(this);
            var input_check_box = $(this).find('input');

            var div_dialog_by_id = $("#dialog-documentos_detalhe");
            value_input_check_box = input_check_box.val();
            input_check_box.attr('checked', 'checked');
            this_tr.attr('marcado', 'marcado');
            this_tr.addClass('hover');

            if (xhr_abrir_documento) {
                xhr_abrir_documento.abort();
            }
            url = base_url+'/sisad/detalhedcmto/detalhedcmto';
            xhr_abrir_documento = $.ajax({
                url: url,
                dataType: 'html',
                type: 'POST',
                data: value_input_check_box,
                contentType: 'application/json',
                processData: false,
                beforeSend: function() {
                    div_dialog_by_id.dialog("open");
                },
                success: function(data) {
                    div_dialog_by_id.html(data);

                },
                complete: function() {

                },
                error: function() {

                }
            });
        }
        );
    /* Formata surgimento da dialog*/
    $("#dialog-documentos_detalhe").dialog({
        title: 'Detalhe',
        autoOpen: false,
        modal: false,
        show: 'fold',
        hide: 'fold',
        resizable: true,
        width: 800,
        position: [580, 140, 0, 0],
        buttons: {
            Ok: function() {
                $(this).dialog("close");
            }
        }
    });
});

$(function() {
    var secao;

    $("#TRF1_SECAO_1").change(
        function() {
            secao = $("#TRF1_SECAO_1").val();
            if (secao == "") {
                $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
                    source: base_url + "/sosti/solicitacao/ajaxunidade",
                    minLength: 3,
                    delay: 500
                });

                $("#DOCM_CD_LOTACAO_REDATORA").autocomplete({
                    source: base_url + "/sosti/solicitacao/ajaxunidade",
                    minLength: 3,
                    delay: 500
                });
            } else {
                secao = $("#TRF1_SECAO_1").val().split('|')[0];
                $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
                    source: base_url + "/sosti/solicitacao/ajaxunidade/secao/" + secao,
                    minLength: 3,
                    delay: 500
                });
                $("#DOCM_CD_LOTACAO_REDATORA").autocomplete({
                    source: base_url + "/sosti/solicitacao/ajaxunidade/secao/" + secao,
                    minLength: 3,
                    delay: 500
                });
            }
        }
        );

    $("#DOCM_CD_MATRICULA_CADASTRO").autocomplete({
        //source: "sosti/solicitacao/ajaxnomesolicitante",
        source: base_url + "/sosti/solicitacao/ajaxnomesolicitante",
        minLength: 3,
        delay: 300
    });

    secao = $("#TRF1_SECAO_1").val();
    if (secao == "") {
        $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxunidade",
            minLength: 3,
            delay: 500
        });

        $("#DOCM_CD_LOTACAO_REDATORA").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxunidade",
            minLength: 3,
            delay: 500
        });
    } else {
        secao = $("#TRF1_SECAO_1").val().split('|')[0];
        $("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxunidade/secao/" + secao,
            minLength: 3,
            delay: 500
        });
        $("#DOCM_CD_LOTACAO_REDATORA").autocomplete({
            source: base_url + "/sosti/solicitacao/ajaxunidade/secao/" + secao,
            minLength: 3,
            delay: 500
        });
    }

    $('#pesquisar').click(function() {
//        $("#pesq_div").hide();
//        $("#container_pagination").hide();
    });
    
    $("#botao_ajuda_recolhe").click(function() {
//        $("#pesq_div").hide();
//        $("#pesquisar").show();
    });
    
    /*Datepicker para Data Inicial e Data Final*/
    $(function() {
        var dates = $("#DATA_INICIAL, #DATA_FINAL").datepicker({
            dateFormat: 'dd/mm/yy',
            dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
            dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
            dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
            'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            nextText: 'Próximo',
            prevText: 'Anterior',
            changeMonth: true,
            numberOfMonths: 1,
            changeYear: true,
            onSelect: function(selectedDate) {
                var option = this.id == "DATA_INICIAL" ? "minDate" : "maxDate",
                instance = $(this).data("datepicker");
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
                dates.not(this).datepicker("option", option, date);
            }
        });
    });
});

$(function(){
   $('#UltPesquisa').click(function(){
     $("#pesq_div").css('display','block');
     $("#pesq_div").show();
     $("#container_pagination").hide();
   });
});

$(function() {
        /**
         * Para transformar o select em um combo de autocomplete via somente java-script
         **/
        $("#DOCM_ID_PCTT").combobox();
        $("#combobox-input-text-DOCM_ID_PCTT").attr('style', 'width: 600px;');
        $("#combobox-input-button-DOCM_ID_PCTT").attr('style', 'display: none;');
        $("#DOCM_ID_TIPO_DOC").combobox();
        $("#combobox-input-text-DOCM_ID_TIPO_DOC").attr('style', 'width: 500px;');

        /**
         * Para apresentar a Lista completa de ASSUNTOS
         */
        var botao_detalhe_pctt = $("<input type='button' name='LST_CPT_PCTT' value='Lista completa de Assuntos' />");
        botao_detalhe_pctt.css('position', 'relative');
        botao_detalhe_pctt.css('display', 'inline');
        botao_detalhe_pctt.css('float', 'right');
        botao_detalhe_pctt.css('top', '-44px');
        botao_detalhe_pctt.css('left', '-140px');
        botao_detalhe_pctt.button();
        $("#DOCM_ID_PCTT-element").append(botao_detalhe_pctt);
        botao_detalhe_pctt.click(
                function() {
                    if ($('#detalhe_pctt').attr('id') == undefined) {
                        var detalhe_pctt = $("<div id='detalhe_pctt'></div>");
                        var select = $("#DOCM_ID_PCTT");
                        select.css('display', 'block');
                        select.css('width', '680px');
                        select.css('height', '480px');
                        select.attr('size', '20');
                        detalhe_pctt.append(select);
                        $('body').append(detalhe_pctt);
                        detalhe_pctt.dialog({
                            title: 'Lista completa de Assuntos',
                            modal: true,
                            width: 700,
                            height: 600,
                            buttons: {
                                OK: function() {
                                    $("#DOCM_ID_PCTT").css('display', 'none');
                                    selected = $("#DOCM_ID_PCTT").children(":selected");
                                    $("#combobox-input-text-DOCM_ID_PCTT").val(selected.text());
                                    $("#DOCM_ID_PCTT-element").append(select);
                                    $(this).dialog('close');
                                    $("#combobox-input-text-DOCM_ID_PCTT").focus();
                                }
                            }
                        });
                        detalhe_pctt.dialog('open');
                    } else {
                        $("#DOCM_ID_PCTT").css('display', 'block');
                        $('#detalhe_pctt').append($("#DOCM_ID_PCTT"));
                        $('#detalhe_pctt').dialog('open');
                    }
                }
        );
    });