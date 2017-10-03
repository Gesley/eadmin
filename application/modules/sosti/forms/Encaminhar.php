<?php
class Sosti_Form_Encaminhar extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $aSolicitante = new Zend_Session_Namespace('solicitanteNs');
        
        $idDocumento = new Zend_Form_Element_Hidden('ID_DOCUMENTO');
        $idDocumento->setValue($aSolicitante->iddocumento)
                    ->removeDecorator('Label')
                    ->removeDecorator('HtmlTag');;
        
        $movi_id_movimentacao = new Zend_Form_Element_Hidden('MOVI_ID_MOVIMENTACAO');
        $movi_id_movimentacao->setValue($aSolicitante->idmovimentacao)
                             ->removeDecorator('Label')
                             ->removeDecorator('HtmlTag');;
        
        $unidadeh = new Zend_Form_Element_Hidden('UNIDADEH');
        $unidadeh->setValue($aSolicitante->unidadeh)
                 ->removeDecorator('Label')
                 ->removeDecorator('HtmlTag');;
        
        $nome = new Zend_Form_Element_Text('NOME');
        $nome->setLabel('Solicitante:')
             ->addFilter('StripTags')
             ->setAttrib('readonly', 'readonly')
             ->setValue($aSolicitante->matricula.' - '.$aSolicitante->cadastrante)
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty');
        
        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade->setLabel('Unidade solicitante:')
                ->addFilter('StripTags')
                ->setAttrib('readonly', 'readonly')
                ->setValue($aSolicitante->unidade)
                ->setOptions(array('style' => 'width:500px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');
        
        $atendimento = new Zend_Form_Element_Text('ATENDIMENTO');
        $atendimento->setLabel('Local de atendimento:')
                    ->setValue($aSolicitante->localizacao)
                    ->addFilter('StripTags')
                    ->setAttrib('readonly', 'readonly')
                    ->setOptions(array('style' => 'width:500px'))
                    ->addFilter('StringTrim')
                    ->addValidator('NotEmpty');
        
        $email = new Zend_Form_Element_Text('EMAIL');
        $email->setLabel('E-mail:')
              ->addFilter('StripTags')
              ->setAttrib('readonly', 'readonly')
              ->setValue($aSolicitante->email)
              ->setOptions(array('style' => 'width:200px'))
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty');
        
        $telefone = new Zend_Form_Element_Text('TELEFONE');
        $telefone->setLabel('Telefone:')
                 ->addFilter('StripTags')
//                 ->addValidator('Alnum')
                 //->addFilter('Alnum')
                 ->setValue($aSolicitante->telefone)
                 ->setOptions(array('style' => 'width:200px'))
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty');
        
        $descricaoSolicitacao = new Zend_Form_Element_Textarea('DESCRICAOSOLICITACAO');
        $descricaoSolicitacao->setLabel('Descrição do serviço:')
                             ->addFilter('StripTags')
                             ->setAttrib('readonly', 'readonly')
                             ->setAttrib('style', 'width: 800px; height: 30px;')
                             ->setValue($aSolicitante->servico)
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty');
        
        $descricaoEncaminhamento = new Zend_Form_Element_Textarea('DESCRICAOENCAMINHAMENTO');
        $descricaoEncaminhamento->setRequired(true)
                                ->setLabel('Descrição do encaminhamento:')
                                 ->setAttrib('style', 'width: 800px; height: 30px;')
                                 ->addValidator('StringLength', false, array(5, 4000))
                                 ->addValidator('NotEmpty')
                                 ->addFilter('StripTags')
                                 ->addFilter('StringTrim')
                                 ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
        $dsServico = new Zend_Form_Element_Text('DSSERVICO');
        $dsServico->setLabel('Serviço:')
                  ->addFilter('StripTags')
//                  ->setAttrib('disabled', 'disabled')
                  ->setValue($aSolicitante->dsservico)
                  ->setOptions(array('style' => 'width:800px'))
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($idDocumento,$movi_id_movimentacao, $nome, $unidade, $email, $atendimento, $telefone, 
                                 $dsServico, $descricaoSolicitacao, $descricaoEncaminhamento, $cpf, $submit,
                                 $unidadeh));
    }

}