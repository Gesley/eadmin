/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas ao cadastro de parte e vistas
 * 
 *  Depende dos scripts:
 *  + /js/sisad/mensagem.js
 *  + 
 */
$("document").ready(function() {

    parteId = null;
    parteLabel = null;
    qtdVistaInterna = 0;

    aux = $("#cadastro_partes #partes_array").val();
    if (aux !== "") {
        arrayPartes = jQuery.parseJSON(aux);
        recarregaHtmlpartes(arrayPartes);
    } else {
        arrayPartes = {"parte": {}, "vista": {}};
    }

    $("#cadastro_partes #tipo_parte").combobox({
        selected: function(event, ui) {
            limpaIdPartes();
            //chama function do javascript mensagem.js
            removeFlashMessage();
            value = ui.item.value;
            encadeiaCamposParteVista(value);
        },
        changed: function(event, ui) {
            limpaIdPartes();
            //chama function do javascript mensagem.js
            removeFlashMessage();
            value = ui.item.value;
            encadeiaCamposParteVista(value);
        }
    });
    $("#cadastro_partes #combobox-input-text-tipo_parte").attr("style", "width: 489px;");

    $("#cadastro_partes #pessoa_fisica_interna").autocomplete({
        source: base_url + "/sarh/pessoa/ajax-json-pessoas-fisicas-trf1",
        minLength: 4,
        delay: 100,
        select: function(event, ui) {
            setIdPartes({
                parteId: ui.item.pmat_cd_matricula + "-" + ui.item.pnat_id_pessoa
                , parteLabel: ui.item.label
            });
        },
        change: function(event, ui) {
            setIdPartes({
                parteId: ui.item.pmat_cd_matricula + "-" + ui.item.pnat_id_pessoa
                , parteLabel: ui.item.label
            });
        }
    });
    $("#cadastro_partes #pessoa_fisica_interna").attr("style", "width: 489px;");

    $("#cadastro_partes #pessoa_fisica_externa").autocomplete({
        source: base_url + "/sarh/pessoa/ajax-json-pessoas-fisicas-externa",
        minLength: 4,
        delay: 100,
        select: function(event, ui) {
            setIdPartes({
                parteId: ui.item.id
                , parteLabel: ui.item.label
            });
        },
        change: function(event, ui) {
            setIdPartes({
                parteId: ui.item.id
                , parteLabel: ui.item.label
            });
        }
    });
    $("#cadastro_partes #pessoa_fisica_externa").attr("style", "width: 489px;");

    $("#cadastro_partes #pessoa_juridica").autocomplete({
        source: base_url + "/sarh/pessoa/ajax-json-pessoas-juridicas",
        minLength: 4,
        delay: 100,
        select: function(event, ui) {
            setIdPartes({
                parteId: ui.item.id
                , parteLabel: ui.item.label
            });
        },
        change: function(event, ui) {
            setIdPartes({
                parteId: ui.item.id
                , parteLabel: ui.item.label
            });
        }
    });
    $("#cadastro_partes #pessoa_juridica").attr("style", "width: 489px;");

    $("#cadastro_partes #secao_parte_vista").combobox({
        selected: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            carregaSubsecao(ui.item.value);
        },
        changed: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            carregaSubsecao(ui.item.value);
        }
    });
    $("#cadastro_partes #combobox-input-text-secao_parte_vista").attr("style", "width: 489px;");

    $("#cadastro_partes #subsecao_parte_vista").combobox({
        selected: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            carregaUnidadesPorSubsecao(ui.item.value);
        },
        changed: function(event, ui) {
            //chama function do javascript mensagem.js
            removeFlashMessage();
            carregaUnidadesPorSubsecao(ui.item.value);
        }
    });
    $("#cadastro_partes #combobox-input-text-subsecao_parte_vista").attr("style", "width: 489px;");

    $("#cadastro_partes #unidade_administrativa").combobox({
        selected: function(event, ui) {
            aux = ui.item.value.split("|");
            setIdPartes({
                parteId: aux[0] + "-" + aux[1]
                , parteLabel: ui.item.label
            });
            //chama function do javascript mensagem.js
            removeFlashMessage();
        },
        changed: function(event, ui) {
            aux = ui.item.value.split("|");
            setIdPartes({
                parteId: aux[0] + "-" + aux[1]
                , parteLabel: ui.item.label
            });
            //chama function do javascript mensagem.js
            removeFlashMessage();
        }
    });
    $("#cadastro_partes #combobox-input-text-unidade_administrativa").attr("style", "width: 489px;");
    $("#cadastro_partes #combobox-input-button-unidade_administrativa").attr("style", "display: none;");

    $("#cadastro_partes #tipo_pessoa_parte").combobox();
    $("#cadastro_partes #combobox-input-text-tipo_pessoa_parte").attr("style", "width: 489px;");

    $("#cadastro_partes #adicionar_parte_vista").click(function() {
        if (isEmptyIdPartes()) {
            /**
             * Alerta para adicionar partes ou vistas vazio
             * alert('Selecione um valor para adicionar como parte ou vista.');
            */
            return;
        }
        aux = {
            "parte_vista": {
                "parte": true,
                "vista": true
            }
            , "parte": {
                "parte": true,
                "vista": false
            }
            , "vista": {
                "parte": false,
                "vista": true
            }
        };

        tipoAdicionarComo = $("#cadastro_partes #tipo_pessoa_parte").val();
        adicionarComo = aux[tipoAdicionarComo];

        tipoParte = $("#cadastro_partes #tipo_parte").val();

        aux = {
            "pessoa_fisica_interna": "partes_pessoa_trf",
            "pessoa_fisica_externa": "partes_pess_ext",
            "pessoa_juridica": "partes_pess_jur",
            "unidade_administrativa": "partes_unidade"
        };

        idElemento = aux[tipoParte];

        json = $("#cadastro_partes #" + idElemento).val();
        json = jQuery.parseJSON(json);

        if (json === null) {
            json = {};
        }


        if (adicionarComo["parte"]) {
            valor = parteId + "-" + "1";
            json[valor] = valor;
            arrayPartes["parte"][valor] = parteLabel;
        }
        if (adicionarComo["vista"]) {
            valor = parteId + "-" + "3";
            json[valor] = valor;
            arrayPartes["vista"][valor] = parteLabel;
        }
        $("#cadastro_partes #" + idElemento).val(JSON.stringify(json));
        
        limpaIdPartes();
        limpaCamposTextoParteVista();
        recarregaHtmlpartes(arrayPartes);
    });


    $("#cadastro_partes").delegate(".removeParteVista", "click", function(event) {
        id = $(this).attr("id");
        tipoParte = $(this).closest("div").attr("id");
        if (tipoParte == "partes") {
            delete arrayPartes["parte"][id];
            $(this).closest("li").remove();
        } else if (tipoParte == "vistas") {
            delete arrayPartes["vista"][id];
            $(this).closest("li").remove();
        }
        recarregaHtmlpartes(arrayPartes);
        event.preventDefault();
    });

    if ($("#cadastro_partes #tipo_parte").val() != "") {
        encadeiaCamposParteVista($("#cadastro_partes #tipo_parte").val());
    } else {
        //esconde os campos de parte
        escondeCamposTipoPartes();
        escondeCamposAdicaoPartes();
    }
});

// FUNCTIONS MANIPULAÇÃO DE VISIBILIDADE

function encadeiaCamposParteVista(value) {
    if (value === "pessoa_fisica_interna") {
        exibePessoaFisicaInterna();
    } else if (value === "pessoa_fisica_externa") {
        exibePessoaFisicaExterna();
    } else if (value === "pessoa_juridica") {
        exibePessoaJuridica();
    } else if (value === "unidade_administrativa") {
        exibeUnidadeAdministrativa();
    }
}

function recarregaHtmlpartes(array) {
    htmlAux = "";
    $.each(array["parte"], function(index, value) {
        htmlAux += '<li><a id="' + index + '" href="#" class="removeParteVista">Remover </a>' + value + '</li>';
    });
    $("#listaPartesVistas #partes ul").html(htmlAux);

    htmlAux = "";
    $.each(array["vista"], function(index, value) {
        htmlAux += '<li><a id="' + index + '" href="#" class="removeParteVista">Remover </a>' + value + '</li>';
    });
    $("#listaPartesVistas #vistas ul").html(htmlAux);

    $("#cadastro_partes #partes_array").val(JSON.stringify(array));
}

function exibePessoaFisicaInterna() {
    escondeCamposTipoPartes();
    escondeCamposAdicaoPartes();

    exibeCamposAdicaoPartes();
    trocaParaPessoaFisicaInterna();

}

function exibePessoaFisicaExterna() {
    escondeCamposTipoPartes();
    escondeCamposAdicaoPartes();

    exibeCamposAdicaoPartes();
    trocaParaPessoaFisicaExterna();

}

function exibePessoaJuridica() {
    escondeCamposTipoPartes();
    escondeCamposAdicaoPartes();

    exibeCamposAdicaoPartes();
    trocaParaPessoaJuridica();
}

function exibeUnidadeAdministrativa() {
    escondeCamposTipoPartes();
    escondeCamposAdicaoPartes();

    exibeCamposAdicaoPartes();
    trocaParaUnidadeAdministrativa();
}

function escondeCamposTipoPartes() {
    escondeCampo("pessoa_fisica_interna");
    escondeCampo("pessoa_fisica_externa");
    escondeCampo("pessoa_juridica");
    escondeCampo("secao_parte_vista");
    escondeCampo("subsecao_parte_vista");
    escondeCampo("unidade_administrativa");


}

function escondeCamposAdicaoPartes() {
    escondeCampo("tipo_pessoa_parte");
    escondeCampo("adicionar_parte_vista");
    $("#cadastro_partes #listaPartesVistas").hide();
}

function exibeCamposAdicaoPartes() {
    exibeCampo("tipo_pessoa_parte");
    exibeCampo("adicionar_parte_vista");
    $("#cadastro_partes #listaPartesVistas").show();
}

function trocaParaPessoaFisicaInterna() {
    escondeCamposTipoPartes();
    exibeCampo("pessoa_fisica_interna");
}

function trocaParaPessoaFisicaExterna() {
    escondeCamposTipoPartes();
    exibeCampo("pessoa_fisica_externa");
}

function trocaParaPessoaJuridica() {
    escondeCamposTipoPartes();
    exibeCampo("pessoa_juridica");
}

function trocaParaUnidadeAdministrativa() {
    escondeCamposTipoPartes();
    exibeCampo("secao_parte_vista");
    exibeCampo("unidade_administrativa");
    exibeCampo("subsecao_parte_vista");
}

// FUNCTIONS MANIPULAÇÃO DE DADOS

/**
 * inclui valor nas variaveis de controle dos id das partes
 * @param {type} objetoJavascript
 * @returns {undefined}
 */
function setIdPartes(objetoJavascript) {
    parteId = (objetoJavascript.parteId !== "undefined" ? objetoJavascript.parteId : null);
    parteLabel = (objetoJavascript.parteLabel !== "undefined" ? objetoJavascript.parteLabel : null);
}
function limpaIdPartes() {
    parteId = null;
    parteLabel = null;
    
}
function isEmptyIdPartes() {
    return (parteId === null || parteLabel === null);
}

function carregaSubsecao(idSecao) {
    $.ajax({
        url: base_url + "/sarh/lotacao/ajax-combo-subsecao-por-secao",
        dataType: "html",
        type: "POST",
        data: "id_secao=" + idSecao,
        beforeSend: function() {
            $("#cadastro_partes #subsecao_parte_vista").html("");
            $("#cadastro_partes #combobox-input-text-subsecao_parte_vista").val("");

            $("#cadastro_partes #unidade_administrativa").html("");
            $("#cadastro_partes #combobox-input-text-unidade_administrativa").val("");

        },
        success: function(data) {
            if (data === '') {
                //chama function do javascript mensagem.js
                mostraFlashMessage("A seção selecionada não possui subseções", "notice");
            } else {
                $("#cadastro_partes #subsecao_parte_vista").html(data);
            }
        },
        complete: function() {

        },
        error: function(jqXHR, textStatus, errorThrown) {
            mostraFlashMessage("Ocorreu o seguinte erro no ajax da seção: " + textStatus, "error");
        }
    });
}

function carregaUnidadesPorSubsecao(idSubsecao) {
    $.ajax({
        url: base_url + "/sarh/lotacao/ajax-combo-lotacoes-por-subsecao",
        dataType: "html",
        type: "POST",
        data: "id_subsecao=" + idSubsecao,
        beforeSend: function() {
            $("#cadastro_partes #unidade_administrativa").html("");
            $("#cadastro_partes #combobox-input-text-unidade_administrativa").val("");
        },
        success: function(data) {
            if (data === '') {
                //chama function do javascript mensagem.js
                mostraFlashMessage("A subseção selecionada não possui subseções", "notice");
            } else {
                $("#cadastro_partes #unidade_administrativa").html(data);
            }
        },
        complete: function() {

        },
        error: function(jqXHR, textStatus, errorThrown) {
            mostraFlashMessage("Ocorreu o seguinte erro no ajax da subseção: " + textStatus, "error");
        }
    });
}

function limpaCamposTextoParteVista(){
    $("#pessoa_fisica_interna").val('');
    $("#pessoa_fisica_externa").val('');
    $("#pessoa_juridica").val('');
    $("#combobox-input-text-unidade_administrativa").val('');   
}

function validaConfidencialidade(event) {
    idConfidencialidade = $('#DOCM_ID_CONFIDENCIALIDADE').val();
    if (jQuery.inArray("" + idConfidencialidade, ["0", "1"]) != -1 && jQuery.isEmptyObject(arrayPartes["parte"])) {
        mostraFlashMessage("É necessário cadastrar pelo menos uma parte.", "notice");
        event.preventDefault();
        return false;
    } else if (jQuery.inArray("" + idConfidencialidade, ["0", "1"]) == -1 && (jQuery.isEmptyObject(arrayPartes["parte"]) || jQuery.isEmptyObject(arrayPartes["vista"]))) {
        mostraFlashMessage("É necessário cadastrar pelo menos uma parte e uma vista.", "notice");
        event.preventDefault();
        return false;
    } else if (jQuery.inArray("" + idConfidencialidade, ["0", "1"]) == -1) {
        parteVistaInterna = $("#partes_pessoa_trf").val();
        if (parteVistaInterna == null) {
            mostraFlashMessage("É necessário cadastrar pelo menos uma pessoa física interna como vista.", "notice");
            event.preventDefault();
            return false;
        }
        json = jQuery.parseJSON(parteVistaInterna);
        qtdVistas = 0;
        $.each(json, function(index, value) {
            aux = json[index];
            valor = aux.charAt(aux.length - 1);
            if (valor == 3) {
                qtdVistas++;
            }
        });
        if (qtdVistas == 0) {
            mostraFlashMessage("É necessário cadastrar pelo menos uma pessoa física interna como vista.", "notice");
            event.preventDefault();
            return false;
        }
    }
    return true;
}