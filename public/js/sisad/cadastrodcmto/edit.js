/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas à tela de edição de documentos
 * 
 *  Depende dos scripts:
 *  + 
 *  + 
 */

$('document').ready(function() {
    $("#DOCM_ID_PCTT").combobox();
    $("#combobox-input-text-DOCM_ID_PCTT").attr('style', 'width: 700px;');
    $("#combobox-input-button-DOCM_ID_PCTT").attr('style', 'display: none;');

    var botao_detalhe_pctt = $("<input type='button' name='LST_CPT_PCTT' value='Lista completa de Assuntos' />");
    botao_detalhe_pctt.css('position', 'relative');
    botao_detalhe_pctt.css('display', 'inline');
    botao_detalhe_pctt.css('float', 'right');
    botao_detalhe_pctt.css('top', '-58px');
    botao_detalhe_pctt.css('left', '-70px');
    botao_detalhe_pctt.button();
    $("#DOCM_ID_PCTT-element").append(botao_detalhe_pctt);

    botao_detalhe_pctt.click(function() {
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
    });

    //mostra/esconde o botao de vistas de acordo com a confidencialidade
    $confidenc = $('#DOCM_ID_CONFIDENCIALIDADE');
    if ($confidenc.val() != "0") {
        $("input[name='cadVistas']").show();
    }

    $confidenc.change(function() {
        if ($(this).val() != "0") {
            $("input[name='cadVistas']").show();
        } else {
            $("input[name='cadVistas']").hide();
        }
    });
});