/**
 * direciona para a pagina de assinatura e valida checkbox
 */
$("document").ready(function() {
   $('#form_documentos').submit(function() {
       var dados = $(this).serialize();
       $.ajax({
           type: "POST",
           url: base_url + '/sisad/documento/assinarsalvar',
           data: dados,
           error: function(){
               $('#flashMessages').html("<div class='error'><strong>Erro: </strong>Página não encontrada.</div>");
           },
           success: function(data) {
               /**
                * Verifica se a senha bate com a senha da tabela de login.
                */
               if(data.SUCESSO === false) {
                   $('#flashMessages').html("<div class='notice'><strong>Alerta:</strong> "+data.MENSAGEM+" </div>");
               } else {
                   /**
                    * Verifica se algum documento foi escolhido.
                    */
                   if ($("input[type=checkbox][name=documentos[]]:checked").val() == undefined) { 
                       $('#flashMessages').html("<div class='notice'><strong>Alerta:</strong> "+data.MENSAGEM+" </div>");
                   } else {
                       /**
                        * Manda o post para assinar os documentos selecionados.
                        */
                      $(function() {
                          $("#dialog-message").html("<div class='success'><strong>Sucesso:</strong> "+data.MENSAGEM+" </div>");
                          $("#dialog-message").dialog({
                              modal: true,
                              buttons: {
                                  Ok: function() {
                                      $( this ).dialog( "close" );
                                      top.location = base_url + '/sisad/caixaunidade/entrada';
                                  }
                              }
                          });
                      });
                   }
               }
           }
       });
       return false;
   });

   function manipulaCheckboxPorClasse(atributo, valor, classe) {
       $.each($("." + classe), function(index, checkbox) {
           $(checkbox).attr(atributo, valor);
           $('.nao_aceita_assinatura_digital').closest('tr').unbind('click');
       });
   }
   /**
    * Validação dos checkbox
    */
   function toggleAssinatura() {
       $("#SENHA").val("");
       if ($("input[name='TIPO_ASSINATURA']:checked").val() == undefined) {
           $("#TIPO_ASSINATURA-senha").attr("checked", true);
           manipulaCheckboxPorClasse('disabled', false, 'nao_aceita_assinatura_digital');
           $("#assinatura_por_senha").show();
           $("#digitMsg").hide();
       } 
       else if ($("input[name='TIPO_ASSINATURA']:checked").val() == "senha") {
           manipulaCheckboxPorClasse('disabled', false, 'nao_aceita_assinatura_digital');
           $("#assinatura_por_senha").show();
           $("#digitMsg").hide();
       } 
       else {
           /**
            * Validações quando a assinatura é por token.
            */
           manipulaCheckboxPorClasse('disabled', true, 'nao_aceita_assinatura_digital');
           manipulaCheckboxPorClasse('checked', false, 'nao_aceita_assinatura_digital');
           $("#digitMsg").show();
           $("#assinatura_por_senha").hide();
       }
   }

   toggleAssinatura();
    /**
     * Marca ou desmarca os checkbox com classe igual a check_documento 
     * de dentro de uma tabela
     */
   $(".check_todos_documentos").click(function() {
       checkboxs = $(this).closest("table").find(".check_documento");
       if ($(this).is(":checked")) {
           $.each(checkboxs, function(index, checkbox) {
               if (!$(checkbox).hasClass('nao_aceita_assinatura_digital') || $("input[name='TIPO_ASSINATURA']:checked").val() != "certificado") {
                   $(checkbox).attr("checked", true);
               }
           });
       } else {
           $.each(checkboxs, function(index, checkbox) {
               if (!$(checkbox).hasClass('nao_aceita_assinatura_digital') || $("input[name='TIPO_ASSINATURA']:checked").val() != "certificado") {
                   $(checkbox).attr("checked", false);
               }
           });
       }
   });
   $("input[name='TIPO_ASSINATURA']").change(function() {
       toggleAssinatura();
   });
});