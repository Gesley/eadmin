/*
 * ###########################   Atenção   ###################################
 * este javascript pode estar sendo utilizado por diversas páginas
 * Favor verificar sua utilização antes de modificar
 */

var GLOBAL_indice_abas =  0;
var xhr_abrir_documento; 
      
var grid_tbody_tr;
$(function(){

    grid_tbody_tr = $("table.grid > tbody > tr");
    grid_tbody_tr.click(
        function(){
            grid_tbody_tr.removeClass('hover_nav');
                
            var this_tr = $(this);
            var is_checked_tr = $(this).attr('marcado');
                
            var input_check_box = $(this).find('input[type=checkbox]');
            var is_checked_input = input_check_box.attr('checked');
                
            if( (is_checked_input == undefined && is_checked_tr == undefined) || (is_checked_input != undefined && is_checked_tr == undefined) ){
                input_check_box.attr('checked','checked');
                this_tr.attr('marcado','marcado');
                this_tr.addClass('hover');
            }else{
                input_check_box.removeAttr('checked');
                this_tr.removeAttr('marcado');
                this_tr.removeClass('hover');
            }
            input_check_box.focus();
        }
        );
    grid_tbody_tr.dblclick(
        function(){
            var this_tr = $(this);
            var input_check_box = $(this).find('input');
                
            grid_tbody_tr.each(
                function(){
                    var this_tr = $(this);
                    var input_check_box = $(this).find('input');
                        
                    input_check_box.removeAttr('checked');
                    this_tr.removeAttr('marcado');
                    this_tr.removeClass('hover');
                }
                );
                
            var div_dialog_by_id =  $("#dialog-documentos_detalhe");
            value_input_check_box = input_check_box.val();
            input_check_box.attr('checked', 'checked');
            this_tr.attr('marcado','marcado');
            this_tr.addClass('hover');
                
            if (xhr_abrir_documento) {
                xhr_abrir_documento.abort();
            }
                
            url = base_url + '/sosti/detalhesolicitacao/detalhesol';
            xhr_abrir_documento = $.ajax({
                url: url,
                dataType: 'html',
                type: 'POST',
                data: value_input_check_box,
                contentType: 'application/json',
                processData: false, 
                beforeSend:function() {
                    if(! div_dialog_by_id.dialog( "isOpen" )){
                        div_dialog_by_id.dialog("open");
                    }
                },
                success: function(data) {
                    div_dialog_by_id.html(data);
                    
                },
                complete: function(){
                    
                },
                error : function(){
                    
                }
            });
        }
        ); 
    $("input[type=checkbox][name=input_check_all_grid]").click(
        function(){
            if($(this).attr('checked')){
                $(".nav_check_boxes").attr('checked','checked');
                $("tr[name=rowList]").addClass('hover');
            }else{
                $(".nav_check_boxes").removeAttr('checked');
                $("tr[name=rowList]").removeClass('hover');
            }
        }
        );
    var form_valido = false;
    $('input[name=acao]').click(
        function(){
            var acao = this.value;
            var formhelpdesk = $('form[name=helpdesk]');
            if(acao == 'Encaminhar'){
                formhelpdesk.attr('action',base_url + '/sosti/gestaodemandasinfraestrutura/encaminhar');
            }else if(acao == 'Baixar'){
                formhelpdesk.attr('action',base_url + '/sosti/gestaodemandasinfraestrutura/baixarcaixa');
            }else if(acao == 'Espera'){
                formhelpdesk.attr('action',base_url + '/sosti/gestaodemandasinfraestrutura/esperacaixa');
            }else if(acao == 'Solicitar Informação'){
                formhelpdesk.attr('action',base_url + '/sosti/solicitacao/solicitarinformacao');
            }else if(acao == 'Trocar Serviço'){
                formhelpdesk.attr('action',base_url + '/sosti/solicitacao/trocarservico');
            }else if(acao == 'Parecer'){
                formhelpdesk.attr('action',base_url + '/sosti/solicitacao/parecer');
            }else if(acao == 'Inserir não Conformidade'){
                formhelpdesk.attr('action',base_url + '/sosti/gestaodemandasinfraestrutura/inserirconformidade');
            }else if(acao == 'Remover Conformidade'){
                formhelpdesk.attr('action',base_url + '/sosti/gestaodemandasinfraestrutura/removerconformidade');
            }else if(acao == 'Gerenciar Extensão de Prazo'){
                formhelpdesk.attr('action',base_url + '/sosti/sla/saveautorizaextensaoprazo');
            }else if(acao == 'Excel'){
                formhelpdesk.attr('action',base_url + '/sosti/detalhesolicitacao/detalhesolexportacao/param/detalhexls');
            }else if(acao == 'PDF'){
                formhelpdesk.attr('action',base_url + '/sosti/detalhesolicitacao/detalhesolexportacao/param/detalhepdf');
            }
        }
        );
        
            
    $('form[name=helpdesk]').submit(
        function(){          
                    
            if(form_valido){
                return true;
            }
                   
            var solictacaoSelecionada = $("input[type=checkbox][name=solicitacao[]]:checked").val();
            if (solictacaoSelecionada == undefined){ 
                var mensagem = "<div class='notice'><strong>Alerta:</strong> Escolha uma solicitação!</div>";
                $('#flashMessages').html(mensagem);
                return false;
            }else{
                return true;
            }
                    
        }
        );
        
    function getselectedSolicitacoes(elemento)
    {
        elemento.each(
            function(i,element) 
            {
                element = $(this);
                if($(element).is(':checked')){
                    if($(element).attr('id')!= 'input_check_all_grid')
                    {
                        //var valor = $(element).val();
                        //alert(valor);
									
                        var obj = $.parseJSON(element.val());
                        var docmrs = obj.DOCM_NR_DOCUMENTO;
                        var docID =  obj.SSOL_ID_DOCUMENTO;
                        var movimentacaoID = obj.MOFA_ID_MOVIMENTACAO;
                        docsSelected += docmrs + "<br>";
                        docsSelectedPost += docmrs+":"+ docID+":"+movimentacaoID+ ","; // numero do documento : id do documento : id da movimentação.
                    }
                }
            });
           				
        console.log(docsSelectedPost);
        $('#documentosSelecionados').val(docsSelectedPost);
        $('#docsSelecionados').html(docsSelected);
    }
       
       
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

    $('#Salvar').click(function(){
        $('#frmConformidade').submit();
    })
});

$(function() {
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



    $('#pesquisar')
    .click(function() {
        pesq_div = $("#pesq_div");

        if(pesq_div.css('display') == "none"){
            pesq_div.show('');
        }else{
            pesq_div.hide('');
        }
    });

    $('#Filtrar').button();

    $("#botao_ajuda_recolhe").click(
        function(){
            $("#pesq_div").hide();
            $("#pesquisar").show();
        });
});
$(function() {
    var dates = $( "#DATA_INICIAL, #DATA_FINAL" ).datepicker({
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
        onSelect: function( selectedDate ) {
            var option = this.id == "DATA_INICIAL" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" );
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
            dates.not( this ).datepicker( "option", option, date );
        }
    });
                
    var dates_cadastro = $( "#DATA_INICIAL_CADASTRO, #DATA_FINAL_CADASTRO" ).datepicker({
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
        onSelect: function( selectedDate ) {
            var option = this.id == "DATA_INICIAL_CADASTRO" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" );
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
            dates_cadastro.not( this ).datepicker( "option", option, date );
        }
    });
}); 
$(function() {
    $('#SGRS_ID_GRUPO').change(
        function(){
            $("#SSER_ID_SERVICO").removeAttr('disabled');
            $.ajax({
                url: base_url + '/sosti/solicitacao/ajaxservicos',
                dataType: 'html',
                type: 'POST',
                data: this.value,
                contentType: 'application/json',
                processData: false,
                beforeSend:function() {
                    $("#SSER_ID_SERVICO").removeClass('erroInputSelect');
                    $("#SSER_ID_SERVICO").html('');
                    $("#SSER_ID_SERVICO").addClass('carregandoInputSelect');
                },
                success: function(data) {
                    $("#SSER_ID_SERVICO").html(data);
                    $("#SSER_ID_SERVICO").removeClass('carregandoInputSelect');
                    $("#SSER_ID_SERVICO").focus();
                },
                error: function(){
                    $("#SSER_ID_SERVICO").removeClass('x-form-field');
                    $("#SSER_ID_SERVICO").val('Erro ao carregar.');
                    $("#SSER_ID_SERVICO").addClass('erroInputSelect');
                    $("#SSER_ID_SERVICO").html('<option>Erro ao carregar</option>');
                }
            });  
        }
        );
    $("#SSOL_NR_TOMBO-label").hide();
    $("#SSOL_NR_TOMBO-element").hide();
    $("#DE_MAT-label").hide();
    $("#DE_MAT-element").hide();
            
            
    $('#SSER_ID_SERVICO').change(
        function(){
            var unidade = $(this).val().split('|')[1];
            if(unidade == 'S'){
                $("#SSOL_NR_TOMBO-label").show();
                $("#SSOL_NR_TOMBO-element").show();
                $("#DE_MAT-label").show();
                $("#DE_MAT-element").show();
            }else{
                $("#SSOL_NR_TOMBO-label").hide();
                $("#SSOL_NR_TOMBO-element").hide();
                $("#DE_MAT-label").hide();
                $("#DE_MAT-element").hide();
            }
        }
        );
    $('#SSOL_NR_TOMBO').focusout(
        function(){
            $.ajax({
                //url: "sosti/solicitacao/ajaxdesctombo/id/"+this.value,
                url: "ajaxdesctombo/id/"+this.value,
                beforeSend:function() {
                    $("#DE_MAT").removeClass('erroInputTextArea');
                    $("#DE_MAT").val('');
                    $("#DE_MAT").removeClass('x-form-field');
                    $("#DE_MAT").addClass('carregandoTextArea');
                },
                success: function(data) {
                    $("#DE_MAT").val(data);
                    $("#DE_MAT").removeClass('carregandoInputTextArea');
                    $("#DE_MAT").addClass('x-form-field');
                    $("#DE_MAT").focus();
                },
                error: function(){
                    $("#DE_MAT").removeClass('carregandoInputTextArea');
                    $("#DE_MAT").removeClass('x-form-field');
                    $("#DE_MAT").val('Erro ao carregar.');
                    $("#DE_MAT").addClass('erroInputTextArea');
                }
            });  
        }
        );
            
    if( $('#SERVICO-nomecompleto').is(':checked') == true){
                
        $('#SSER_DS_SERVICO').hide();
        $('#SSER_DS_SERVICO-label').hide();
        $('#SSER_DS_SERVICO').attr('disabled', 'disabled');
                
        $('#SSER_ID_SERVICO').show();
        $('#SSER_ID_SERVICO-label').show();
        $('#SSER_ID_SERVICO').removeAttr('disabled');
        var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
        description_obj.show();
               
                
    }else if ( $('#SERVICO-partenome').is(':checked') == true) {
                
                
        $('#SSER_ID_SERVICO').hide();
        $('#SSER_ID_SERVICO-label').hide();
        $('#SSER_ID_SERVICO').attr('disabled', 'disabled');
        var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
        description_obj.hide();
                
        $('#SSER_DS_SERVICO').show();
        $('#SSER_DS_SERVICO-label').show();
        $('#SSER_DS_SERVICO').removeAttr('disabled');
                
            
                
    } 

            
    $('input[type=radio][name=SERVICO]').click(
        function(){
            if(this.value == 'nomecompleto'){
                    
                $('#SSER_DS_SERVICO').hide();
                $('#SSER_DS_SERVICO').val("");
                $('#SSER_DS_SERVICO-label').hide();
                $('#SSER_DS_SERVICO').attr('disabled', 'disabled');

                $('#SSER_ID_SERVICO').show();
                $('#SSER_ID_SERVICO-label').show();
                $('#SSER_ID_SERVICO').removeAttr('disabled');
                var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
                description_obj.show();
                    
            }else if (this.value == 'partenome'){
                $('#SSER_ID_SERVICO').hide();
                $('#SSER_ID_SERVICO').val("");
                $('#SSER_ID_SERVICO-label').hide();
                $('#SSER_ID_SERVICO').attr('disabled', 'disabled');
                var description_obj = $('#SSER_ID_SERVICO-element').find('.description');
                description_obj.hide();

                $('#SSER_DS_SERVICO').show();
                $('#SSER_DS_SERVICO-label').show();
                $('#SSER_DS_SERVICO').removeAttr('disabled');
            }
        //});    
        });
    //MOSTRA A DIV DAS NÃO CONFORMIDADE
    $('.conformidadeshowDescricao').hover(function(){
        var currentID = $(this).attr('id'); //ID DA TD
        var conteudo =  $('#tooltip_'+ currentID).html();
        if(conteudo != ''){
            $('#tooltip_'+currentID).css('visibility','visible');
        }
    });
    $('.conformidadeshowDescricao').mouseleave(function(){
        var currentID = $(this).attr('id');
			    
        $('#tooltip_'+currentID).css('visibility','hidden');
    ;
    })
    //ACOES  DO BOTAO SUBMIT LOCALIZADO NO TOPO DO FORMULÁRIO 
    $('#Filtrar2').click(
        function(){
            $('#filtroForm').submit();
        }
        );
			
    $('#Filtrar2').hover(
        function(){
            $('#Filtrar2').addClass('ui-state-hover');
        },
        function(){
            $('#Filtrar2').removeClass('ui-state-hover');
        }
        );
            
//FIM
});