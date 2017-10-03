    
    var GLOBAL_indice_abas = 0;
    var xhr_abrir_documento;
    
$(document).ready(function() {
    
    
var $grid_tbody_tr = $("table.grid > tbody > tr");
    
$grid_tbody_tr.click( function(){

                    $grid_tbody_tr.removeClass('hover_nav');

                    var $this_tr = $(this);
                    var $is_checked_tr = $(this).attr('marcado');

                    var $input_check_box = $(this).find('input[type=checkbox]');
                    var $is_checked_input = $input_check_box.attr('checked');

                    if( ($is_checked_input == undefined && $is_checked_tr == undefined) || ($is_checked_input != undefined && $is_checked_tr == undefined) ){
                            $input_check_box.attr('checked','checked');
                            $this_tr.attr('marcado','marcado').addClass('hover');
                    }else{
                            $input_check_box.removeAttr('checked');
                            $this_tr.removeAttr('marcado').removeClass('hover');
                    }
                    $input_check_box.focus();
});
    
$grid_tbody_tr.dblclick( function(){
  //  alert($(this).val());
    var $this_tr = $(this), 
        $input_check_box = $(this).find('input');

        $grid_tbody_tr.each( function(){
            $(this).find('input').removeAttr('checked').removeAttr('marcado').removeClass('hover');
        });

        var $div_dialog =  $("#dialog-documentos_detalhe"),
        //transformo o value do input para um objeto JSON para ser enviado para a detalhe soliictaçao 
        $value_input_check_box = ' {"SSOL_ID_DOCUMENTO" : "' + $input_check_box.val()+ '"} ';
        $input_check_box.attr('checked', 'checked');
        $this_tr.attr('marcado','marcado').addClass('hover');


        if (xhr_abrir_documento) {
                xhr_abrir_documento.abort();
        }

        url = base_url+'/sosti/detalhesolicitacao/detalhesol/idcaixa/37/tipoSolicitacao/272';
        xhr_abrir_documento = $.ajax({
                                        url: url,
                                        dataType: 'html',
                                        type: 'POST',
                                        data: $value_input_check_box,
                                        contentType: 'application/json',
                                        processData: false, 
                                        beforeSend:function() {
                                            if(! $div_dialog.dialog( "isOpen" )){
                                                $div_dialog.dialog("open");
                                            }
                                        },
                                        success: function(data) {
                                            $div_dialog.html(data);
                                           
                                        },
                                        complete: function(){
                                        },
                                        error : function(){

                                        }
                                    });
                                    
    } ); 


    $("#dialog-documentos_detalhe").dialog({
        title    : 'Detalhe',
                    autoOpen : false,
                    modal    : false,
                    show: 'fold',
                    hide: 'fold',
                    resizable: true,
                    width: 800,
                    position: [580,140,0,0],
                    buttons : {
                            Ok: function() {
                                    $(this).dialog("close");
                            }
                    }
    });
        
$("input[type=checkbox][name=input_check_all_grid]").click( function(){
        if($(this).attr('checked')){
                $(".nav_check_boxes").attr('checked','checked');
                $("tr[name=rowList]").addClass('hover');
        }else{
                $(".nav_check_boxes").removeAttr('checked');
                $("tr[name=rowList]").removeClass('hover');
        }
});
        
$("#SSOL_CD_MATRICULA_ATENDENTE").autocomplete({
            source: base_url+"/sosti/solicitacao/ajaxnomesolicitante",
            minLength: 3,
            delay: 300,
            select: function( event, ui ) {
                    if( ui.item.value != null ){ 
                            $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val(ui.item.value);
                    }
            },
            change: function( event, ui ) {
                    if( ui.item.value != null ){ 
                            $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val(ui.item.value);
                    }
            }

    }).keyup(
    function(){
            if(this.value == ""){
                    $("#SSOL_CD_MATRICULA_ATENDENTE_VALUE").val('');
            }
});

$("#DOCM_CD_MATRICULA_CADASTRO").autocomplete({
                source: base_url+"/sosti/solicitacao/ajaxnomesolicitante",
                minLength: 3,
                delay: 300,
                select: function( event, ui ) {
                        if( ui.item.value != null ){ 
                                $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val(ui.item.value);
                        }
                },
                change: function( event, ui ) {
                        if( ui.item.value != null ){ 
                                $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val(ui.item.value);
                        }
                }

        }).keyup(
        function(){
                if(this.value == ""){
                        $("#DOCM_CD_MATRICULA_CADASTRO_VALUE").val('');
                }
});

$("#DOCM_CD_LOTACAO_GERADORA").autocomplete({
                source: base_url+"/sosti/solicitacao/ajaxunidade",
                minLength: 3,
                delay: 500,
                select: function( event, ui ) {
                        if( ui.item.value != null ){ 
                                $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val(ui.item.value);
                        }
                },
                change: function( event, ui ) {
                        if( ui.item.value != null ){  
                                $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val(ui.item.value);
                        }
                }
        }).keyup(
        function(){
                if(this.value == ""){
                        $("#DOCM_CD_LOTACAO_GERADORA_VALUE").val('');
                }
});



    
$('#pesquisar').click(function() {
        var pesq_div = $("#pesq_div")
        if(pesq_div.css('display') == "none"){
                pesq_div.show('');
        }else{
                pesq_div.hide('');
        }
});

$('#Filtrar').button();

$("#botao_ajuda_recolhe").click( function(){
        $("#pesq_div").hide();
        $("#pesquisar").show();
});

$("#SERVICO-partenome, label > SERVICO-partenome").hide();

$('.tooltip').tooltipster({
    fixedWidth: 650,
    position: 'bottom-left'
});

$(".tooltip").each(function() {
    $(this).attr("data-oldhref", $(this).attr("href"));
    $(this).removeAttr("href");
});

$('input[name=acao]').click( function(){
        var acao = $(this).val();
        var formservicosgraficos = $('form[name=servicosgraficos]');
        if(acao == 'Encaminhar'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/encaminhar');
        }else if(acao == 'Baixar'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/baixar');
        }else if(acao == 'Trocar Serviço'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/trocarservico');
        }else if(acao == 'Parecer'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/parecer');
        }else if(acao == 'Categorias'){
            formservicosgraficos.attr('action',base_url+'/soseg/categorias/categorizar');
        }else if(acao == 'Solicitar Informação'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/solicitarinformacao');
        }else if(acao == 'Cancelar'){
            formservicosgraficos.attr('action',base_url+'/soseg/solicitacaoservicosgraficos/cancelar');
        }
        }
);
    
$('form[name=servicosgraficos]').submit( function(){
                
            var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]:checked").val();
            if (solictacaoSelecionada == undefined){ 
                    var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
                    $('#flashMessages').html(mensagem);
                    return false;
            }else{
                    return true;
            }

});

var dates = $( "#DATA_INICIAL_CADASTRO, #DATA_FINAL_CADASTRO" ).datepicker({
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
            changeYear: true,
            onSelect: function( selectedDate ) {
                    var option = this.id == "DATA_INICIAL_CADASTRO" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" );
                    date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
                    dates.not( this ).datepicker( "option", option, date );
            }
});
     
    
    
});


