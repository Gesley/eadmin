$(document).ready(function() {
    
    //Esconde as divs de opções do checklist
    $('.divOpcoesList').hide();
    
    //Opções de marca do backup
    $("#BKP_MARCA").change(function(){
        var marca = $("#BKP_MARCA");
        $.ajax({
            dataType: 'html',
            url: base_url+"/sosti/labsoftware/ajaxnometipomodelo/id/"+this.value,
            beforeSend:function() {
                
                $("#BKP_MODELO").removeClass('erroInputSelect');
                $("#BKP_MODELO").val("");
                $("#BKP_MODELO").addClass('carregandoInputSelect');
            },
            success: function(data) {
                
                $("#BKP_MODELO").html(data);
                $("#BKP_MODELO").removeClass('carregandoInputSelect');
            },
            error: function(){
                $("#BKP_MODELO").removeClass('x-form-field');
                $("#BKP_MODELO").val('Erro ao carregar.');
                $("#BKP_MODELO").addClass('erroInputSelect');
                $("#BKP_MODELO").html('<option>Erro ao carregar</option>');
            }
        });      
    });
    
    /**
     * Configurando entrada de dados no campo
     */
    $('body').delegate('.campoQtdInsert', 'keyup', function(){
        var id = $('.campoQtdInsert').attr('id');
        if(isNaN($('#'+id).val())){
            $('#'+id).val('');
        }else{
            var x = $('#'+id).val().replace(".","");
            $('#'+id).val(x);
        }
    });
   
    //Tratamento do clique do button das opcoes
    $('.buttonOpcoes').click(function(){
        var id = $(this).attr('id');
        abreDivOpcoesList(id);
    });
    
    //Tratando combobox
    $('#SERVICO').combobox({ 
        selected: function(event, ui) {
            adicionaElemento('servicos[]',ui.item.value, ui.item.label,'#Servicos tr:last', false, 'servico');
        },
        changed: function(event, ui) {
            adicionaElemento('servicos[]',ui.item.value, ui.item.label,'#Servicos tr:last', false, 'servico');
        } 
    });
    $('#combobox-input-text-SERVICO').css('width', '460px');
    
    //Tratando combobox
    $('#SOFTWARE').combobox({ 
        selected: function(event, ui) {
            adicionaElemento('softwares[]',ui.item.value, ui.item.label,'#Softwares tbody tr:last', false, 'software');
        },
        changed: function(event, ui) {
            adicionaElemento('softwares[]',ui.item.value, ui.item.label,'#Softwares tbody tr:last', false, 'software');
        } 
    });
    $('#combobox-input-text-SOFTWARE').css('width', '460px');
    
    //Tratando combobox
    $('#HARDWARE').combobox({ 
        selected: function(event, ui) {
            adicionaElemento('hardwares[]',ui.item.value, ui.item.label,'#Hardwares tbody tr:last', true, 'hardware');
        },
        changed: function(event, ui) {
            adicionaElemento('hardwares[]',ui.item.value, ui.item.label,'#Hardwares tbody tr:last', true, 'hardware');
        } 
    });
    $('#combobox-input-text-HARDWARE').css('width', '460px');
    
    
    /**
     * Ajax para o campo Seção/Subseção
     * Ao escolher uma Seção, carrega suas subseções
     */
    $("select#TRF1_SECAO").change(function () {
        var secao = $(this).val().split('|')[0];
        var lotacao = $(this).val().split('|')[1];
        var tipolotacao = $(this).val().split('|')[2];
        $.ajax({
            url: base_url + '/sosti/labhardware/ajaxsubsecoes/secao/'+secao+ '/lotacao/'+lotacao+'/tipo/'+tipolotacao,
            dataType : 'html',
            delay: 100,
            beforeSend:function() {
                $("select#SECAO_SUBSECAO").addClass('carregandoInputSelect');
                $('select#SECAO_SUBSECAO').removeAttr('disabled','disabled');
                $('select#SECAO_SUBSECAO').html('');
                $('select#HARDWARE').html('');
                $('#combobox-input-text-HARDWARE').val('');
            },
            success: function(data) {
                $("select#SECAO_SUBSECAO").removeClass('carregandoInputSelect');
                $('select#SECAO_SUBSECAO').html(data);
            },
            error: function(){
                $('select#SECAO_SUBSECAO').html('<option>Erro ao carregar</option>');
            }
        });
    });
    
    /*
     * Ao selecionar uma subsecao, carregar material 
     */
    $('select#SECAO_SUBSECAO').change(function(){
        //Carregar os hardwares dessa subsecao
        var secao = $(this).val().split('|')[0];
        var lotacao = $(this).val().split('|')[1];
        
        $.ajax({
            url: base_url + '/sosti/labhardware/ajaxhardwareporsecao/secao/'+secao+ '/subsecao/'+lotacao,
            dataType : 'html',
            delay: 100,
            beforeSend:function() {
                $("select#HARDWARE").addClass('carregandoInputSelect');
                $('select#HARDWARE').removeAttr('disabled','disabled');
                $('select#HARDWARE').html('');
            },
            success: function(data) {
                $("select#HARDWARE").removeClass('carregandoInputSelect');
                $('select#HARDWARE').html(data);
            },
            error: function(){
                $('select#HARDWARE').html('<option>Erro ao carregar</option>');
            }
        });
    });
    
    /*
     * Campo autocomplete de tombo
     */
    $("#SSOL_NR_TOMBO_PESQUISA").autocomplete({
        source: base_url + '/sosti/labhardware/ajaxgetnumerotombo',
        minLength: 2,
        delay: 100,
        select: function( event, ui ) {
            if(ui.item != null){
                $("#SSOL_NR_TOMBO").val(ui.item.id);
            }else{
                $(this).val('');
                $("#SSOL_NR_TOMBO").val('');
            }
        },
        change: function( event, ui ) {
            if(ui.item != null){
                $("#SSOL_NR_TOMBO").val(ui.item.id);
            }else{
                $(this).val('');
                $("#SSOL_NR_TOMBO").val('');
            }
        }
    });
    
    /*
     * Campo autocomplete de tombo de backup
     */
    $("#LBKP_NR_TOMBO_PESQUISA").autocomplete({
        source: base_url + '/sosti/labhardware/ajaxgetnumerotombobackup',
        minLength: 2,
        delay: 100,
        select: function( event, ui ) {
            if(ui.item != null){
                $("#LBKP_NR_TOMBO").val(ui.item.id);
            }else{
                $(this).val('');
                $("#LBKP_NR_TOMBO").val('');
            }
        },
        change: function( event, ui ) {
            if(ui.item != null){
                $("#LBKP_NR_TOMBO").val(ui.item.id);
            }else{
                $(this).val('');
                $("#LBKP_NR_TOMBO").val('');
            }
        }
    });
    
    //Ao clicar no botão remover, chama a função para remover elementos
    $('body').delegate('input.removeItem','click', function(){
        var id = $(this).attr('id');
        removeElementoLista(id);
    });
    
    /*
     *Ao inserir um valor no campo de quantidade, verificar se o valor é maior
     *do que o disponivel
     */
    $('body').delegate('.campoQtdInsert','blur', function(){
        
        //captura o id da linha
        var id = $(this).attr('id').split('-')[1];
        //captura o valor disponivel para este id
        var dispo = parseInt($('.disponivel-hardware-'+id).html(),10);
        //captura o valor digitado
        var solic = $('#'+$(this).attr('id')).val();
        
        //valida o valor
        var x = parseInt(solic);
        if(x > 0){
            $('#'+$(this).attr('id')).val(x);
            //fazer calculo basico de quantidade
            if(solic > dispo){
                alert('Valor solicitado é maior que o disponível!');
                $(this).attr('value', '');
            }
        }else{
            alert('O valor informado não é válido!');
            $(this).attr('value', '');
        }        
    });
    
    /*
     * Ao clicar no link Remove Todos, chamar a função pararemover todos os
     * elementos de um determinado tipo informado
     */
    $('.removeTodos').click(function(){
        var id = $(this).attr('id');
        removeTudo(id);
        return false;
    });
    
    /**
     * Ao submeter o formulario, enviar os Servicos, Softwares e Hardwares 
     * Selecionados
     */
    $('#Salvar').click(function(){
        var verifica = true;
        $('.campoQtdInsert').each(function(){
            if($(this).val() == ''){
                alert('Campo quantidade está vazio! \nInforme um valor e salve novamente.');
                verifica = false;
                return false;
            }
        });
        if(verifica == true){
            $('#form-checklist').submit();
        }else{
            return false;
        }
    });
    
    //Removendo classe intrusa do jquary UI
    $('.campoQtdInsert').removeClass('x-form-text');
    
    
    //Habilitando Submit, depois de preencher todos os campos obrigatorios    
    $('.campoObrig').blur(function(){
        var verifica = true;
        if($(this).data('tipo') == 'obrigatorio'){
            $('.campoObrig').each(function(){
                if($(this).val() == ''){
                    verifica = false;
                }
            });
            if(verifica == true){
                $('#Salvar').removeAttr('disabled');
            }else{
                $('#Salvar').attr('disabled','disabled'); 
            }
        }
    });
       
    
    // #### Funcoes ############################################################
    
    
    /**
     * Funcao adiciona elementos selecionados em suas respsctivas divs
     * 
     * @param nome - Name do campo hidden
     * @param valor - Valor do campo hidden
     * @param label - Texto que aparecerá na lista
     * @param depoisDe - Referencia do elemento que a funcao colocara os elementos depois
     * @param qtd - Flag que verifica se é necessário colocar o campo quantidade
     * @param tipo - Parrar o tipo (hardware ou software) para buscar a qtd disponivel
     */
    function adicionaElemento(nome, valor, label, depoisDe, qtd, tipo){
        
        var flag = true;
        /*
         * Verificar se o registro ja foi selecionado ou se ele for vazio
         * Faz a busca da linha específica pra ver se a mesma existe
         */
        if($("#linha"+tipo+"-"+valor).html() != null || valor == 0){
            flag = false;
        }
        
        //Se o elemento nao for vazio e nao tiver selecionado, incluir
        if(flag){
            var html = " <tr class='linha"+tipo+"' id='linha"+tipo+"-"+valor+"'><input type='hidden' value='"+valor+"' name='"+nome+"' /> ";
            html+= "<td width='20px'> <input type='button' title='Remover ítem' class='removeItem' id='remove"+tipo+"-"+valor+"' value='x' /></td> ";
            html+= "<td> "+label+" </td> ";
            
            //Define quando será mostrado a quantidade disponivel
            if(tipo != 'servico'){
                buscaQtdDisponivel(valor,tipo);
                html+= "<td width='120px'> Disponível: <span class='disponivel-"+tipo+"-"+valor+"'> </span> </td> ";
            }
            //Quando for o caso de informar uma quantidade
            if(qtd){
                //html+= "<td width='120px'> Quantidade: <input type='text' name='qtdHardware["+valor+"]' size='3' /> </td> ";
                html+= "<td width='120px'> *Quantidade: <input class='campoQtdInsert' id='campoQtdInsert-"+valor+"' type='text' name='qtdHardware["+valor+"]' size='3' />  </td> ";
            }    
            //fim do bloco de quantidade
            
            if(tipo != 'servico'){
                html+= "<td>Status: - </td>";
            }
            
            html+= "</tr>";
            //adiciona na lista, depois de um elemento informado
            $(depoisDe).after(html);
        }
    
    }
    
    /**
     * 
     * Funcao abre a div para selecionar os elementos necessários de acorodo
     * com o botão clicado
     * 
     * @param opt - Dependendo da opção, a funcao abrira um ID
     */
    function abreDivOpcoesList(opt){
        switch(opt){
            case 'ServicosButton':
                $('#ServicosDiv').toggle(500);
                break;
            case 'SoftwareButton':
                $('#SoftwareDiv').toggle(500);
                break;
            case 'HardwareButton':
                $('#HardwareDiv').toggle(500);
                break;
            default:
                break;
        }   
    }
    
    /** 
     * Funcao busca a quantidade disponivel de hardware ou software e escreve o 
     * valor no campo determinado
     * 
     * @param id - Id do hardware ou software
     * @param tipo - tipo de busca:  hardware ou software
     */ 
    function buscaQtdDisponivel(id, tipo){
        
        if(tipo == 'hardware'){
            //Ajax que busca qtd de hardware
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxqtdhardwaredisponivel/id/'+id,
                dataType : 'json',
                delay: 100,
                success: function(data) {
                    $('.disponivel-'+tipo+"-"+id).html(data.qtd);
                },
                error: function(){
                
                }
            });
        }else{
            //Ajax que busca qtd de licensas de software
            $.ajax({
                url: base_url + '/sosti/labhardware/ajaxqtdsoftwaredisponivel/id/'+id,
                dataType : 'json',
                delay: 100,
                success: function(data) {
                    $('.disponivel-'+tipo+"-"+id).html(data.qtd);
                },
                error: function(){
                
                }
            });
        }
    }
    
    /**
     * Funcao retira da lista um elemento especificado ao clicar no botao 
     * remover
     * 
     * @param idExclusao - ID do botao exclusao
     * @return void
     */
    function removeElementoLista(idExclusao){
        
        /*
         * Quebrar o id em tipo e valor
         * fazer swtich para o tipo e excluir a linha para cada valor
         */
        var tipo = idExclusao.split('-')[0];
        var elemento = idExclusao.split('-')[1];
        
        switch(tipo){
            case 'removeservico':
                $('#linhaservico-'+elemento).remove();
                break;
            case 'removesoftware':
                $('#linhasoftware-'+elemento).remove();
                break;
            case 'removehardware':
                $('#linhahardware-'+elemento).remove();
                break;
            default:
                break; 
        }
    }
    
    /**
     * Funcao remove todos os elementos selecionados de acordo com o tipo 
     * informado
     * 
     * @param tipo - Tipo pode ser: removeTodosServicos, 
     * removeTodosSoftware ou removeTodosHardwares
     * @return void 
     */
    function removeTudo(tipo){
        
        switch(tipo){
            case 'removeTodosServicos':
                $('.linhaservico').remove();
                break;
            case 'removeTodosSoftware':
                $('.linhasoftware').remove();
                break;
            case 'removeTodosHardwares':
                $('.linhahardware').remove();
                break;
            default:
                break; 
        }
    }
    
    
});//fim do document ready


