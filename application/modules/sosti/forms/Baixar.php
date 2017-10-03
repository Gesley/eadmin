<?php
class Sosti_Form_Baixar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $aSolicitante = new Zend_Session_Namespace('solicitanteNs');
        
        $mofa_id_movimentacao = new Zend_Form_Element_Hidden('MOFA_ID_MOVIMENTACAO');
        $mofa_id_movimentacao->addFilter('Int')
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag')
                             ->setValue($aSolicitante->idmovimentacao);
        
        $id_documento = new Zend_Form_Element_Hidden('ID_DOCUMENTO');
        $id_documento->addFilter('Int')
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag')
                     ->setValue($aSolicitante->iddocumento);
        
        $nome = new Zend_Form_Element_Text('NOME');
        $nome->setLabel('Solicitante:')
             ->addFilter('StripTags')
             ->setValue($aSolicitante->cadastrante)
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty');
        
        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade->setLabel('Unidade solicitante:')
             ->addFilter('StripTags')
             ->setValue($aSolicitante->unidade)
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty');
        
        $atendimento = new Zend_Form_Element_Text('ATENDIMENTO');
        $atendimento->setLabel('Local de atendimento:')
             ->addFilter('StripTags')
             ->setValue($aSolicitante->localizacao)
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty');
        
        $email = new Zend_Form_Element_Text('EMAIL');
        $email->setLabel('E-mail:')
              ->addFilter('StripTags')
              ->setValue($aSolicitante->email)
              ->setOptions(array('style' => 'width:200px'))
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty');
        
        $telefone = new Zend_Form_Element_Text('TELEFONE');
        $telefone->setLabel('Ramal/telefone:')
                 ->addFilter('StripTags')
                 ->setValue($aSolicitante->telefone)
                 ->setOptions(array('style' => 'width:200px'))
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty');
        
        $descricaoSolicitacao = new Zend_Form_Element_Textarea('DESCRICAOSOLICITACAO');
        $descricaoSolicitacao->setLabel('Descrição do serviço:')
                             ->addFilter('StripTags')
                             ->setValue($aSolicitante->servico)
                             ->setAttrib('style', 'width: 800px; height: 30px;')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty');
        
        $descricaoSolucao = new Zend_Form_Element_Textarea('DESCRICAOSOLUCAO');
        $descricaoSolucao->setRequired(true)
                         ->setLabel('Descrição da solução:')
                         ->setAttrib('style', 'width: 800px; height: 30px;')
                         ->addValidator('StringLength', false, array(5, 4000))
                         ->addValidator('NotEmpty')
                         ->addFilter('StripTags')
                         ->addFilter('StringTrim')
                         ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
        $dsServico = new Zend_Form_Element_Text('DSSERVICO');
        $dsServico->setLabel('Serviço:')
                             ->addFilter('StripTags')
                             ->setAttrib('disabled', 'disabled')
                             ->setValue($aSolicitante->dsservico)
                             ->setOptions(array('style' => 'width:800px'))
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($nome, $email, $telefone, $atendimento, $dsServico, $descricaoSolicitacao,
                                 $descricaoSolucao, $cpf, $mofa_id_movimentacao, $id_documento, $submit));
    }

}