<?php
class Sosti_Form_recebimentoEquipamento extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');

       
        
        $matricula = new Zend_Form_Element_Text('COU_COD_MATRICULA');
        $matricula->addValidator('NotEmpty')
        ->setLabel('Matrícula:')
        ->setAttrib('size', '25')
        ->setAttrib('readonly', 'readonly')
        ->setRequired(true)
        ->addValidator('Alnum')
        ->addFilter('HtmlEntities')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', false, array(4, 11))
        ->setOptions(array('maxLength' => 11));
        
        $password = new Zend_Form_Element_Password('COU_COD_PASSWORD');
             $password->addValidator('NotEmpty')
        ->setLabel('Senha:')
        ->setDescription('Atenção: O sistema faz distinção entre MAIÙSCULAS e minúsculas e usa a senha de login no e-Admin.')
        ->setAttrib('size', '26')
        ->setRequired(true)
        ->addFilter('HtmlEntities')
        ->addFilter('StringTrim');
        
        $descricaoRecebimento = new Zend_Form_Element_Textarea('DESCRICAO_RECEBIMENTO');
        $descricaoRecebimento->setRequired(true)
                         ->setLabel('Descrição do recebimento:')
                         ->setAttrib('style', 'width: 400px; height: 60px;')
                         ->addValidator('StringLength', false, array(5, 4000))
                         ->addValidator('NotEmpty')
                         ->addFilter('StripTags')
                         ->addFilter('StringTrim')
                         ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
		
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('verificarRecebimento');
        
        $retornoController = new Zend_Form_Element_Hidden('RetornoController');
        $retornoController->removeDecorator('Label')
        ->removeDecorator('HtmlTag');
        
        $retornoAcao = new Zend_Form_Element_Hidden('RetornoAcao');
        $retornoAcao->removeDecorator('Label')
        ->removeDecorator('HtmlTag');
        
        $submit = new Zend_Form_Element_Submit('Receber');

        $this->addElements(array($matricula,$password, $descricaoRecebimento,$acao,$submit,$retornoController,$retornoAcao));
    }

}