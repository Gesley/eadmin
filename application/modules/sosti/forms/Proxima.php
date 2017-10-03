<?php
class Sosti_Form_Proxima extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

        $savi_id_aviso = new Zend_Form_Element_Hidden('SAVI_ID_AVISO');
        $savi_id_aviso->addFilter('Int')
                      ->removeDecorator('Label')
                      ->removeDecorator('HtmlTag');

        $aSolicitante = new Zend_Session_Namespace('solicitanteNs');
       
        $nome = new Zend_Form_Element_Text('NOME');
        $nome->setLabel('Solicitante:')
             ->addFilter('StripTags')
             ->setAttrib('disabled', 'disabled')
             ->setValue($aSolicitante->cadastrante)
             ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
             ->setOptions(array('style' => 'width:500px'))
             ->addFilter('StringTrim')
             ->addValidator('NotEmpty');
        
        $unidade = new Zend_Form_Element_Text('UNIDADE');
        $unidade->setLabel('Unidade solicitante:')
                ->addFilter('StripTags')
                ->setAttrib('disabled', 'disabled')
                ->setValue($aSolicitante->unidade)
                ->setOptions(array('style' => 'width:500px'))
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty');
        
        $atendimento = new Zend_Form_Element_Text('ATENDIMENTO');
        $atendimento->setLabel('Local de atendimento:')
                    ->addFilter('StripTags')
                    ->setValue($aSolicitante->localizacao)
                    ->setAttrib('disabled', 'disabled')
                    ->setOptions(array('style' => 'width:500px'))
                    ->addFilter('StringTrim')
                    ->addValidator('NotEmpty');
        
        $email = new Zend_Form_Element_Text('EMAIL');
        $email->setLabel('E-mail:')
              ->addFilter('StripTags')
              ->setAttrib('disabled', 'disabled')
              ->setValue($aSolicitante->email)
              ->setOptions(array('style' => 'width:200px'))
              ->addFilter('StringTrim')
              ->addValidator('NotEmpty');
        
        $telefone = new Zend_Form_Element_Text('TELEFONE');
        $telefone->setLabel('Ramal/telefone:')
                 ->addFilter('StripTags')
                 ->setAttrib('disabled', 'disabled')
                 ->setValue($aSolicitante->telefone)
                 ->setOptions(array('style' => 'width:200px'))
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty');
        
        $descricaoSolicitacao = new Zend_Form_Element_Textarea('DESCRICAOSOLICITACAO');
        $descricaoSolicitacao->setLabel('Descrição do serviço:')
                             ->addFilter('StripTags')
                             ->setAttrib('disabled', 'disabled')
                             ->setValue($aSolicitante->servico)
                             ->setAttrib('style', 'width: 800px; height: 30px;')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(5, 4000));;
        
        $dsServico = new Zend_Form_Element_Text('DSSERVICO');
        $dsServico->setLabel('Serviço:')
                  ->addFilter('StripTags')
                  ->setAttrib('disabled', 'disabled')
                  ->setValue($aSolicitante->dsservico)
                  ->setOptions(array('style' => 'width:800px'))
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty');

        $this->addElements(array($nome, $unidade, $atendimento, $telefone, $email, $dsServico,
                                 $descricaoSolicitacao, $cpf));
    }

}