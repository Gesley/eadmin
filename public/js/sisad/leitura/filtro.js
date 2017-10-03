/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações ao filtro de documentos
 * 
 * Depende dos scripts:
 *  + carrega_documento.js
 *  + botoes.js
 *  + tabs.js
 */
$(function() {
    //** DECLARAÇÃO DE VARIAVEIS -------------------------------------------------*/

    //* CARREGAMENTO DEFAULT ------------------------------------------------------*/
    carregaJqueryCampos();
    //* USO DINÂMICO -------------------------------------------------------------*/


    $("#tabs").delegate(".expandir_filtro", "click", function() {
        var pesq_div = $(this).closest("fieldset").find("#pesq_div")
        if (pesq_div.css('display') == "none") {
            pesq_div.show('');
            $(this).attr('value', 'Recolher Filtro');
        } else {
            pesq_div.hide('');
            $(this).attr('value', 'Expandir Filtro');
        }
    });

    $("#tabs").delegate(".remover_filtro", "click", function() {
        div_tabela = $(this).closest(".tabs_documentos_juntados").find(".div_tabela_documentos");
        console.log(div_tabela);
        //function criada no javascript tabs.js
        id_documento_principal = getIdDocumentoPorElemento(div_tabela);
        //function criada no javascript carrega_documentos.js
        var pesq_div = $(this).closest("fieldset").find('#pesq_div');
        pesq_div.find('form')[0].reset();
        pesq_div.hide('');
        $(this).closest("fieldset").find(".label_filtro").html("&emsp;&emsp;Filtro Inativo");
        $(this).closest("fieldset").find(".expandir_filtro").attr('value', 'Expandir Filtro');

        $id_tabs_juntados = $(this).closest(".tabs_documentos_juntados").attr("id");
        array = $id_tabs_juntados.split("-");
        if (array[0] == 'tabs_anexos') {
            //function criada no javascript carrega_documentos.js
            carrega_anexados(div_tabela, id_documento_principal, []);
        } else if (array[0] == 'tabs_apensos') {
            //function criada no javascript carrega_documentos.js
            carrega_apensados(div_tabela, id_documento_principal, []);
        } else if (array[0] == 'tabs_vinculos') {
            //function criada no javascript carrega_documentos.js
            carrega_vinculados(div_tabela, id_documento_principal, []);
        }

    });

    $("#tabs").delegate(".pesquisar", "click", function() {
        div_tabela = $(this).closest(".tabs_documentos_juntados").find(".div_tabela_documentos");
        //function criada no javascript tabs.js
        id_documento_principal = getIdDocumentoPorElemento(div_tabela);
        dadosForm = $(this).closest("form").serializeArray();

        $(this).closest(".tabs_documentos_juntados").find(".label_filtro").html("&emsp;&emsp;Filtro Ativo");
        $(this).closest("fieldset").find(".expandir_filtro").attr('value', 'Expandir Filtro');

        $id_tabs_juntados = $(this).closest(".tabs_documentos_juntados").attr("id");
        array = $id_tabs_juntados.split("-");

        if (array[0] == 'tabs_anexos') {
            //function criada no javascript carrega_documentos.js
            carrega_anexados(div_tabela, id_documento_principal, dadosForm);
        } else if (array[0] == 'tabs_apensos') {
            //function criada no javascript carrega_documentos.js
            carrega_apensados(div_tabela, id_documento_principal, dadosForm);
        } else if (array[0] == 'tabs_vinculos') {
            //function criada no javascript carrega_documentos.js
            carrega_vinculados(div_tabela, id_documento_principal, dadosForm);
        }

    });
});
//* FUNCTIONS ----------------------------------------------------------------*/
function carregaJqueryCampos() {
    $(".datepicker").datepicker();
    $(".datepicker").datepicker("option", "dateFormat", "dd/mm/yy");

    $(".DOCM_ID_PCTT").autocomplete({
        source: base_url + "/sisad/cadastrodcmto/ajaxassuntodocm",
        minLength: 3,
        delay: 500,
        select: function(event, ui) {
            if (ui.item.value != null) {
                $(this).val(ui.item.label);
            }
        },
        change: function(event, ui) {
            if (ui.item.value != null) {
                $(this).val(ui.item.label);
            }
        }
    }).keyup(function() {
        if (this.value == "") {
            $(this).val('');
        }
    });

    $(".pesquisar_autor_juntada").autocomplete({
        //source: "sosti/solicitacao/ajaxnomesolicitante",
        source: base_url + "/sosti/solicitacao/ajaxnomesolicitante",
        minLength: 3,
        delay: 300
    });
}