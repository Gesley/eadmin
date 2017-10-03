<?php
class Sosti_Form_FechamentoDsv extends Zend_Form
{
    public function init()
    {
        $this
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post')
                ->setAction('fechamento')
                ->setName('fechamentoform');
        $userNs = new Zend_Session_Namespace('userNs'); 
        $formVerify = new Sisad_Form_Verify();
        
        $AcessoCaixaUnidade = new App_Controller_Plugin_AcessoCaixaUnidade();
        $CaixasUnidadeAcesso = $AcessoCaixaUnidade->getAcessoCaixaUnidadePessoal($userNs->matricula);
        
        $unidade = new Zend_Form_Element_Select('UNIDADE');
        $unidade->setRequired(true)
                ->addValidator('NotEmpty')
                 ->setLabel('*Cadastrar e Enviar a unidade:')
                 ->setAttrib('style', 'width: 628px;');
        $unidade->addMultiOptions(array(''=>''));
       if( !empty($CaixasUnidadeAcesso) ){
            foreach ($CaixasUnidadeAcesso as $CaixaUnidade):
                $unidade->addMultiOptions(array(Zend_Json::encode($CaixaUnidade) => $CaixaUnidade["LOTA_SIGLA_LOTACAO"].' - '.$CaixaUnidade["LOTA_DSC_LOTACAO"].' - '.$CaixaUnidade["LOTA_COD_LOTACAO"].' - '.$CaixaUnidade["LOTA_SIGLA_SECAO"] ));
            endforeach;
        }
        
       $docm_ds_assunto_doc_planilha = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC_PLANILHA');
       $docm_ds_assunto_doc_planilha->setRequired(true)
                            ->setLabel('*Descrição do Documento Planilha:')
                            ->setDescription('Digite no mínimo 5 caracteres e no máximo 4000 caracteres.')
                            ->setAttrib('style', 'width: 400px; height: 30px;')
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->setValue("Planilha de base de Cálculo para indicador do SLA.");
       
       $docm_ds_assunto_doc_relatorio = new Zend_Form_Element_Textarea('DOCM_DS_ASSUNTO_DOC_RELATORIO');
       $docm_ds_assunto_doc_relatorio->setRequired(true)
                            ->setLabel('*Descrição do Documento Relatório:')
                            ->setDescription('Digite no mínimo 5 caracteres e no máximo 4000 caracteres.')
                            ->setAttrib('style', 'width: 400px; height: 30px;')
                            ->addValidator('StringLength', false, array(5, 4000))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->setValue("Relatório de Glosas gerado a partir da planilha.");
        
        $submit = new Zend_Form_Element_Submit('Confirmar');
        $submit->setAttrib('class', 'botao')
                ->removeDecorator('label');
        
        
        $this->addElements(array(
                                $unidade,
                                $docm_ds_assunto_doc_planilha,
                                $docm_ds_assunto_doc_relatorio,
                                $formVerify->COU_COD_PASSWORD,
                                $submit
                ));
    }

}