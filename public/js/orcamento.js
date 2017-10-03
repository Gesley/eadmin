//Começa o calendario
var msg_ajuda_by_id;
var botao_ajuda_by_id;
var flashMessages_by_id;
var botao_ajuda_recolhe_by_id;
var msg_informacao_by_id;
var botao_informacao_by_id;

$(document).ajaxStart(function() {
    $('div#loading').show();
}).ajaxStop(function() {
    window.setTimeout(function() {
        $('div#loading').hide('slow');
    }, '10');
});

$(function() {
    $("#accordion").accordion();
    $("#tabs, .tabs").tabs();
    $(".datepicker").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        nextText: 'Próximo',
        prevText: 'Anterior',
        numberOfMonths: 1,
        changeMonth: true,
        changeYear: true,
        regional: 'pt_BR'
    });

    $("#dialog").dialog({
        resizable: true,
        // height:140,
        // modal: truCTRD_CPFCNPJ_DESPESAe,
        buttons: {
            "Delete all items": function() {
                $(this).dialog("close");
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        }
    });

    $(".ordemDESC").button({
        icons: {
            primary: "ui-icon-triangle-1-s"
        }
    });

    $(".ordemASC").button({
        icons: {
            primary: "ui-icon-triangle-1-n"
        }
    });

    $(".abrirAnexo").button({
        icons: {
            primary: "ui-icon-folder-open"
        }
    }).attr('style', 'width: 40px; height: 16px;');

    $(".alertaButton").button({
        icons: {
            primary: "ui-icon-alert"
        }
    }).attr('style', 'width: 40px; height: 16px;');

    $(".historico").button({
        icons: {
            primary: "ui-icon-clock"
        }
    }).attr('style', 'width: 40px; height: 16px;');

    $(".sair").button({
        icons: {
            primary: "ui-icon-power"
        }
    });

    /***************************************************************************
     * ASmR - Novos botões NOTA: Todos os nomes foram alterados para ceo_...
     * devido a recentes mudanças em outras telas / sistemas que
     * descaracterizaram o projeto!
     **************************************************************************/
    $(".ceo_novo").button({
        icons: {
            primary: "ui-icon-document"
        }
    });

    $(".ceo_detalhar").button({
        icons: {
            primary: "ui-icon-contact"
        }
    });

    $(".ceo_editar").button({
        icons: {
            primary: "ui-icon-pencil"
        }
    });

    $(".ceo_excluir").button({
        icons: {
            primary: "ui-icon-trash"
        }
    });

    $(".ceo_salvar").button({
        icons: {
            primary: "ui-icon-disk"
        }
    });

    $(".ceo_importar").button({
        icons: {
            primary: "ui-icon-plus"
        }
    });

    $(".ceo_confirmar").button({
        icons: {
            primary: "ui-icon-check"
        }
    });

    $(".ceo_negar").button({
        icons: {
            primary: "ui-icon-close"
        }
    });

    $(".ceo_consultar").button({
        icons: {
            primary: "ui-icon-search"
        }
    });

    $(".ceo_pesquisar").button({
        icons: {
            primary: "ui-icon-gear"
        }
    });

    $(".ceo_cancelar").button({
        icons: {
            primary: "ui-icon-cancel"
        }
    });

    $(".ceo_voltar").button({
        icons: {
            primary: "ui-icon-arrowreturnthick-1-w"
        }
    });

    $(".ceo_relatorio").button({
        icons: {
            primary: "ui-icon-print"
        }
    });

    $(".ceo_janela").button({
        icons: {
            primary: "ui-icon-newwin"
        }
    });

    // duplicação do button ceo_pesquisar para compatibilidade do BVB
    $(".pesquisar").button({
        icons: {
            primary: "ui-icon-gear"
        }
    });

    // duplicação do button ceo_cancelar para compatibilidade do BVB
    $(".cancelar").button({
        icons: {
            primary: "ui-icon-cancel"
        }
    });

    $(".ceo_readonly").replaceWith(function() {
        $elem = $(this);
        if ($elem.is('select')) {
            value = $("option:selected", $elem).text();
            size = $("option:selected", $elem).size();
        } else {
            value = $elem.val();
            size = $elem.size();
        }
        // return "<span>" + value + "</span>";
        // return "<input type='text' value='" + value + "' size='" + size + "'
        // disabled='true' style='background: #CDCDCD; font-weight: bold;
        // text-align: center;' />";
        // return "<input type='text' value='" + value + "' disabled='true'
        // style='background: #CDCDCD; font-weight: bold; text-align: center;'
        // />";

        return "<span class=''>" + value + "</span>";
    });

    $(".painel").buttonset();

    $("button, input:submit, a", ".dema").button();
    $("a", ".paginationControl").button();
    // $( "span.disabled", ".paginationControl" ).button({disabled:true});
    $("span.disabled").button({
        disabled: true
    });
    $("a", ".dema").click(function() {
        location.href = this.href;
        return false;
    });

    $(".navigation").wijmenu();
    $("input:text").addClass("x-form-text");
    // $("input:text").addClass("x-form-text");
    $("select").addClass("x-form-text");
    $("input.datepicker").datepicker($.datepicker.regional["pt_BR"]);
    $("textarea").addClass("x-form-field");
    $(".grid").addClass("ui-widget ui-widget-content");
    $("thead tr", ".grid").addClass("ui-widget-header");
    $('a.icoAdminlink').wmenu({
        itemShowid: '#mudarUsuario',
        selectedClass: 'icoAdminlink_click'
    });

    // Ajuda do e-Admin - INÍCIO
    $("#botao_ajuda")
            .button({
                icons: {
                    primary: "ui-icon-help"
                }
            })
            .attr('style',
                    'position: absolute; right: 0px; width: 28px; height: 16px; display: none;');

    $("#botao_ajuda_recolhe")
            .button({
                icons: {
                    primary: "ui-icon-arrowstop-1-n"
                }
            })
            .attr('style',
                    'position: relative; left: 350px; bottom: -10px; width: 28px; height: 16px;')
            .attr('title', 'Recolher ajuda');

    flashMessages_by_id = $('#flashMessages');
    msg_ajuda_by_id = $('#msg_ajuda');
    botao_ajuda_by_id = $('#botao_ajuda');
    botao_ajuda_recolhe_by_id = $('#botao_ajuda_recolhe');
    botao_ajuda_by_id.click(function() {
        if (msg_ajuda_by_id.css('display') == 'block') {
            msg_ajuda_by_id.hide("blind");
        } else {
            msg_ajuda_by_id.show("blind");
        }
    });

    botao_ajuda_recolhe_by_id.click(function() {
        msg_ajuda_by_id.hide("blind");
    });
    // Ajuda do e-Admin - TÉRMINO

    // Informação do e-Admin - INÍCIO
    msg_informacao_by_id = $('#msg_informacao');
    botao_informacao_by_id = $('#botao_informacao');
    botao_informacao_by_id.click(function() {
        if (msg_informacao_by_id.css('display') == 'block') {
            msg_informacao_by_id.hide("blind");
        } else {
            msg_informacao_by_id.show("blind");
        }
    });

    $("#botao_informacao")
            .button({
                icons: {
                    primary: "ui-icon-notice"
                }
            })
            .attr('style',
                    'position: absolute; right: 30px; width: 28px; height: 16px; display: none; ');
    // Informação do e-Admin - TÉRMINO
});

$(document).ready(function() {
    // Função para colorir os campos Readyonly
    $("[readonly=readonly]").css('background', '#DEDEDE');

    //Verifica ambiente
    var url = window.location;
    var urlString = url.toString();
    var verificacao = urlString.indexOf('sistemas.trf1.jus.br');
    var ambiente = 1; //0 desenvolvimento , 1 producao
    if (verificacao == -1) {
        ambiente = 0;
    }

    var grid_tbody_tr;
    // grid_tbody_tr = $("table.grid > tbody > tr:not(:first)");
    // TRATAMENTO DO DUPLO CLICK
    grid_tbody_tr = $("table.grid > tbody > tr");

    //Detalhar
    grid_tbody_tr.dblclick(function() {
        $('input[type=checkbox]').attr('checked', false);
        var $this = $(this).find('input[type=checkbox]').attr('value');
        $(this).find('input[type=checkbox]').attr('checked', true);
        var detalhar = $(".rodape .acaoVer").attr('href').concat($this);
        window.location = detalhar;
    });

    //Marcar e desmarcar checkbox
    grid_tbody_tr.click(function() {
        grid_tbody_tr.removeClass('hover_nav');
        var this_tr = $(this);
        var is_checked_tr = $(this).attr('marcado');
        var input_check_box = $(this).find('input');
        var is_checked_input = input_check_box.attr('checked');

        if ((is_checked_input == undefined && is_checked_tr == undefined) || (is_checked_input != undefined && is_checked_tr == undefined)) {
            input_check_box.attr('checked', 'checked');
            this_tr.attr('marcado', 'marcado');
            this_tr.addClass('hover');
            atualizachk();
        } else {
            this_tr.removeClass('hover');
            this_tr.removeAttr('marcado');
            input_check_box.removeAttr('checked');
            atualizachk();
        }
    });

    //Marca ou Desmarca todos
    $('.selecionar').click(function() {
        var marcado = $(this).attr('checked');
        if (marcado) {
            grid_tbody_tr.addClass('hover');
            atualizachk();
        } else {
            grid_tbody_tr.removeClass('hover');
            atualizachk();
        }
    });

    //Inicio mascara do contrato
    $('.nrcontrato').mask('9999/9999');

    //tamanho maximo de numeros no campo (cnpj) 14
    $("#CTRD_CPFCNPJ_DESPESA").attr('maxlength', '14');

    //inibi a inclusão de caracteres não numéricos no campo de cpf/cnpj
    $("#CTRD_CPFCNPJ_DESPESA").bind("keyup blur focus", function(e) {
        e.preventDefault();
        var expre = /[^0-9]/g;
        // remove os caracteres da expressao acima
        if ($(this).val().match(expre))
            $(this).val($(this).val().replace(expre, ''));
    });

    //Projecao Calculo, Validacao e Situacao orcamentaria
    var execucao = [];
    var projecao = [];
    $('.execucao').each(function(i, val) {
        var type = $(this).attr('type');
        if (type != undefined) {
            var $this = $(this).val();
            if ($this == '') {
                $(this).val('0,00');
            }
        } else {
            var $this = $(this).text().slice(0, 2);
            if ($this == 'R$') {
                $this = $(this).text().substr(3);
                if ($this == '') {
                    $(this).text('R$ 0,00');
                }
            } else {
                $this = $(this).text();
                if ($this == '') {
                    $(this).text('0,00');
                }
            }
        }
        var $formatado = formataNumero($this);
        execucao.push($formatado);
    });

    $('.projecao').each(function(i, val) {
        var type = $(this).attr('type');
        if (type != undefined) {
            var $this = $(this).val();
        } else {
            var $this = $(this).text().slice(0, 2);
            if ($this == 'R$') {
                $this = $(this).text().substr(3);
            } else {
                $this = $(this).text();
            }
        }
        var $formatado = formataNumero($this);
        projecao.push($formatado);
    });
    situacaoOrcamentaria(projecao, execucao);

    $('.projecao').blur(function() {
        var projecao = [];
        var total = 0;
        $('.projecao').each(function(i, val) {
            $(this).css('border-color', '#ccc');
            var $this = $(this).val();
            if ($this != '') {
                var numero = parseFloat($this);
                if (!isNaN(numero)) {
                    $(this).css('border-color', '#ccc');
                    var $formatado = formataNumero($this);
                    projecao.push($formatado);
                    var $mostrar = formataMoeda($formatado);
                    $(this).val($mostrar);
                } else {
                    $(this).css('border-color', 'red');
                }
            }
        });
        situacaoOrcamentaria(projecao, execucao);
    });

    /*
     $('.valordespesa').each(function(i, val) {
     $(this).css('border-color', '#ccc');
     var type = $(this).attr('type');
     if(type != undefined){
     var $this = $(this).val();
     $this = trim($this);
     if($this != ''){
     var erro = $('.errors').length;
     if (erro > 0) {
     $this = $this.replace(".", "");
     }else{
     $this = $this.replace(".", ",");
     }
     if($this[0] == ','){
     $this = "0"+$this;
     }
     if($this.slice(0,2) == '-,'){
     $this = "-0,"+$this.slice(2,$this.length);
     }
     }
     }else{
     var $this = $(this).text();
     $this = trim($this);
     if($this != ''){
     if($this.charAt(0) == ","){
     $this = "0"+$this;
     }
     if($this.slice(0,2) == '-,'){
     $this = "-0,"+$this.slice(2,$this.length);
     }
     }
     }
     if($this != '') {
     var naonumericos = naonumerico($this);
     if(naonumericos == true){
     $(this).css('border-color', 'red');
     }else{
     var negativo = false;
     $this = trim($this);
     if($this.charAt(0) == '-'){
     negativo = true;
     var qtdchar = $this.length;
     $this = $this.slice(1, qtdchar);
     }
     var numero = parseFloat($this);
     $(this).css('border-color', '#ccc');
     var $formatado = formataNumero($this);
     var $mostrar = formataMoeda($formatado);
     if(negativo == true){
     $mostrar = "-"+$mostrar;
     }
     if(type != undefined){
     $(this).val($mostrar);
     }else{
     $(this).text($mostrar);
     }
     }
     }
     });
     */
    $('.valordespesa').blur(function() {
        $(this).css('border-color', '#ccc');
        var $this = $(this).val();
        if ($this != '') {
            var naonumericos = naonumerico($this);
            if (naonumericos == true) {
                $(this).css('border-color', 'red');
            } else {
                var negativo = false;
                $this = trim($this);
                if ($this.charAt(0) == '-') {
                    negativo = true;
                    var qtdchar = $this.length;
                    $this = $this.slice(1, qtdchar);
                }
                var numero = parseFloat($this);
                $(this).css('border-color', '#ccc');
                var $formatado = formataNumero($this);
                var $mostrar = formataMoeda($formatado);
                if (negativo == true) {
                    $mostrar = "-" + $mostrar;
                }
                $(this).val($mostrar);

            }
        }
    });

    //Formatação na Grid
    /*
     $('table.grid > tbody > tr:not(:first) > td.valorgrid').each(function(i, val) {
     $(this).css('border-color', '#ccc');
     var negativo = false;
     var type = $(this).attr('type');
     if(type != undefined){
     var erro = $('.errors').length;
     if (erro > 0) {
     $this = $this.replace(".", "");
     }else{
     $this = $this.replace(".", ",");
     }
     if($this[0] == ','){
     $this = "0"+$this;
     }
     }else{
     var $this = $(this).text();
     if($this.charAt(0) == "-"){
     negativo = true;
     $this = $this.replace("-", "");
     }

     if($this.charAt(0) == ","){
     $this = "0"+$this;
     }
     }
     if($this != '') {
     var numero = parseFloat($this);
     if(!isNaN(numero)) {
     $(this).css('border-color', '#ccc');
     var $formatado = formataNumero($this);
     var $mostrar = formataMoeda($formatado);
     if(negativo == true){
     $mostrar = "-"+$mostrar;
     }
     if(type != undefined){
     $(this).val($mostrar);
     }else{
     $(this).text($mostrar);
     }

     }
     }
     }); */


    $('.valorgrid').blur(function() {
        $(this).css('border-color', '#ccc');
        var $this = $(this).val();
        $this = $this.replace(" ", "");
        if ($this != '') {
            if (!isNaN($this)) {
                var $formatado = formataNumero($this);
                var $mostrar = formataMoeda($formatado);
                $(this).val($mostrar);
            } else {
                var parametro = filtroGrid($this);
                if (parametro == 'erro') {
                    $(this).css('border-color', 'red');
                } else {
                    parametro = parametro.split('-');
                    var valor = $this.slice(parametro[0], parametro[1]);
                    valor = formataNumero(valor);
                    valor = formataMoeda(valor);
                    var inicio = $this.slice($this.charAt(0), parametro[0]);
                    var fim = $this.slice(parametro[1], $this.length);
                    $(this).val(inicio + valor + fim);
                }
            }
        }

    });


    function filtroGrid(filtro) {
        var contador = filtro.length;
        var permitidos = new Array();
        permitidos[0] = "=";
        permitidos[1] = "*";
        permitidos[2] = "<";
        permitidos[3] = ">";
        permitidos[4] = "<=";
        permitidos[5] = ">=";
        permitidos[6] = "<>";
        permitidos[7] = "-";
        permitidos[8] = "=-";
        permitidos[9] = ">-";
        permitidos[10] = "<-";
        permitidos[11] = "*-";
        permitidos[12] = "<=-";
        permitidos[13] = ">=-";

        //primeiro caracter númerico
        for (i = 0; i < contador; i++) {
            var caracter = filtro.charAt(i);
            if (!isNaN(caracter)) {
                var primeironumero = i;
                break;
            }
        }
        //ultimo caracter númerico
        for (j = 0; j < contador; j++) {
            var caracter = filtro.charAt(j);
            if (!isNaN(caracter)) {
                var ultimonumero = j;
            }
        }


        if (primeironumero == undefined) {
            return 'erro';
        }
        if (ultimonumero == undefined) {
            return 'erro';
        }
        var parametro = primeironumero + '-' + (ultimonumero + 1);

        if (primeironumero > 0) {
            if (primeironumero > 2) {
                return 'erro';
            } else {
                if (primeironumero == 1) {
                    if (!(permitidos.indexOf(filtro.charAt(0)) != -1)) {
                        return 'erro';
                    }
                }
                if ((primeironumero == 2) || (primeironumero == 3)) {
                    var composto = filtro.charAt(0) + filtro.charAt(1);
                    if (!(permitidos.indexOf(composto) != -1)) {
                        return 'erro';
                    }
                }
            }
        }

        if (!(ultimonumero == (contador - 1))) {
            if (((contador - 1) - ultimonumero) > 1) {
                return 'erro';
            } else {
                if (filtro.charAt(contador - 1) != '*') {
                    return 'erro';
                } else {
                    var numerico = isNaN(filtro.charAt(0));
                    if ((numerico == true) && (filtro.charAt(0) != '*')) {
                        return 'erro';
                    }
                }
            }

        }

        return parametro;

    }

    //Fim do grid


    // Funções para calculo de valores
    function formataNumero(num) {

        num = num.replace("R", "");
        num = num.replace("$", "");
        num = num.replace("-", "");
        num = num.replace(".", "");
        num = num.replace(".", "");
        num = num.replace(".", "");
        num = num.replace(".", "");
        num = num.replace(".", "");
        num = num.replace(",", ".");
        // Arredondamento de centavos
        var numero = Math.round(num * Math.pow(10, 2)) / Math.pow(10, 2);
        num = parseFloat(numero).toFixed(2);
        return num;
    }

    //Função para retirar espaços
    function trim(vlr) {
        while (vlr.indexOf(" ") != -1) {
            vlr = vlr.replace(" ", "");
        }
        return vlr;
    }

    //Função para verificar se existe caracter não numerico
    function naonumerico(filtro) {
        var contador = filtro.length;
        var erro = false;
        var permitidos = new Array();
        permitidos[0] = "-";
        permitidos[1] = ".";
        permitidos[2] = ",";
        for (j = 0; j < contador; j++) {
            var caracter = filtro.charAt(j);
            if (isNaN(caracter)) {
                if (!(permitidos.indexOf(caracter.charAt(0)) != -1)) {
                    erro = true;
                }
            }
        }
        return erro;
    }

    //Função para conversão de valor
    function formataMoeda(num) {
        x = 0;
        if (num < 0) {
            num = Math.abs(num);
            x = 1;
        }
        if (isNaN(num))
            num = "0,00";
        //Transforma tudo em centavos
        cents = Math.floor((num * 100 + 0.5) % 100);
        num = Math.floor((num * 100 + 0.5) / 100).toString();

        if (cents < 10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
            // A cada 3 casas acrescenta um ponto
            num = num.substring(0, num.length - (4 * i + 3)) + '.' + num.substring(num.length - (4 * i + 3));
        ret = num + ',' + cents;
        if (x == 1)
            ret = ' - ' + ret;
        return ret;
    }

    function situacaoOrcamentaria(projecao, execucao) {
        var data = new Date();
//        var mes = data.getMonth();
        var mes = 0;
        var futura = [];
        var atual = [];
        var anterior = [];
        var contanterior = 0;
        var contfut = 0;
        var contatual = 0;
        var totalfuturo = 0;
        var totalexecucao = 0;
        var totalatual = 0;
        var totalprojecao = 0;
        var type = $('#PROJ_VR_TOTAL').attr('type');

        for (var i = 0; i < projecao.length; i++) {
            // Preenchendo o total
            totalprojecao = parseFloat(totalprojecao) + parseFloat(projecao[i]);

            // Buscando os valores dos meses futuros
            if (i >= mes) {
                futura[contfut] = projecao[i];
                contfut++;
            }

            // Buscando os valores do mes corrente
            if (i == mes) {
                atual[contatual] = projecao[i];
                contatual++;
            }
        }

        for (var i = 0; i < futura.length; i++) {
            // Total de projecoes futuras
            totalfuturo = parseFloat(totalfuturo) + parseFloat(futura[i]);
        }

        for (var i = 0; i < execucao.length; i++) {
            if (i < mes) {
                anterior[contanterior] = execucao[i];
                contanterior++;
            }

            if (i == mes) {
                atual[1] = execucao[i];
            }
        }

        if (parseFloat(atual[0]) >= parseFloat(atual[1])) {
            totalatual = atual[0];
        } else {
            totalatual = atual[1];
        }

        for (var i = 0; i < anterior.length; i++) {
            // Total de execucoes anteriores
            totalexecucao = parseFloat(totalexecucao) + parseFloat(anterior[i]);
        }

        var totalaprovado = $('#propostaaprovada').text().slice(0, 2);

        if (totalaprovado == 'R$') {
            totalaprovado = $('#propostaaprovada').text().substr(3);
        } else {
            totalaprovado = $('#propostaaprovada').text();
        }

        totalaprovado = formataNumero(totalaprovado);
        var total = parseFloat(totalfuturo) + parseFloat(totalexecucao) + parseFloat(totalatual);
        var percentual = 0;
        var situacao = totalaprovado - totalfuturo;

        if (situacao > 0) {
            var resultado = totalaprovado - parseFloat(totalfuturo);
            percentual = resultado;

            $('#situacaoorcamentaria').removeClass('valorNegativo');
            $('#situacaoorcamentaria').text('R$ ' + formataMoeda(situacao));
            $('#situacaoorcamentaria').addClass('valorPositivo');
            $('#percentualreajuste').removeClass('valorNegativo');
            $('#percentualreajuste').addClass('valorPositivo');
        } else {
            var resultado = total - parseFloat(totalfuturo);
            percentual = percentual - resultado;

            $('#situacaoorcamentaria').removeClass('valorPositivo');
            $('#situacaoorcamentaria').text('R$ ' + formataMoeda(situacao));
            $('#situacaoorcamentaria').addClass('valorNegativo');
            $('#percentualreajuste').removeClass('valorPositivo');
            $('#percentualreajuste').addClass('valorNegativo');

        }

        // calculo do percentual de ajuste orçamentário
        percentualajuste = (situacao / totalaprovado) * 100;

        totalaprovado = formataMoeda(totalaprovado);
        totalfuturo = formataMoeda(totalfuturo);
        totalexecucao = formataMoeda(totalexecucao);
        totalatual = formataMoeda(totalatual);
        totalprojecao = formataMoeda(totalprojecao);
        percentualajuste = formataMoeda(percentualajuste);

        $('#dotacaofinal').text('R$ ' + totalaprovado);
        $('#futuro').text('R$ ' + totalfuturo);
        $('#executado').text('R$ ' + totalexecucao);
        $('#atual').text('R$ ' + totalatual);
        $('#percentualreajuste').text(percentualajuste + '%');



        if (type != undefined) {
            $('#PROJ_VR_TOTAL').val(totalprojecao);
        } else {
            $('#PROJ_VR_TOTAL').text(totalprojecao);
        }

    } // fim da função situação orçamentaria e valor total projecao

    //Inicio do oculta/ mostra detalhes
    $('#tabs').hide();
    $('#detalheDespesa').click(function() {
        $('#despesaBasico').toggle();
        $('#tabs').toggle();
    });
    //Fim do oculta/mostra detalhe

    /*Ano corrente - Temporario */
    var anoselecionado = $('#ANOSELECIONADO').val();
    if (anoselecionado != undefined) {
        $('#Anocorrente option').each(function(i, val) {
            if ($(this).text() == anoselecionado) {
                $(this).attr('selected', 'selected');
            } else {
                $(this).removeAttr('selected', 'selected');
            }
        });
        $("#Anocorrente").change(function() {
            $('#ANOSELECIONADO').empty();
            $('#ANOSELECIONADO').val($("#Anocorrente option:selected").text());
        });
    } /*Fim do ano corrente*/

//Fim do Jquery
});

function atualizachk() {
    var selecionados = '';
    var cont = 0;
    $("input[type=checkbox][class='chk']:checked").each(function() {
        selecionados += this.value + ',';
        cont++;
    });
    selecionados = selecionados.slice(0, -1);
    $('.qtde').html(cont);
    $('.registros').val(selecionados);
}
