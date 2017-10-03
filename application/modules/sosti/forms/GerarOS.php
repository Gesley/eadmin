<?php
class Sosti_Form_GerarOS extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setAttrib('enctype', 'multipart/form-data')
             ->setMethod('post');
        
        $osContratos = new Zend_Form_Element_Text('OS_CONTRATOS');
        $osContratos->setRequired(false)
                                   ->setLabel('Adicionar Contratos: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $osSistemas = new Zend_Form_Element_Text('OS_SISTEMAS');
        $osSistemas->setRequired(false)
                                   ->setLabel('Adicionar Sistemas: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $osFuncionalidades = new Zend_Form_Element_Text('OS_FUNCIONALIDADE');
        $osFuncionalidades->setRequired(false)
                                   ->setLabel('Adicionar Funcionalidades: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $osArtefatos = new Zend_Form_Element_Text('OS_ARTEFATOS');
        $osArtefatos->setRequired(false)
                                   ->setLabel('Adicionar Artefatos: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
        $tipoDemanda = new Zend_Form_Element_Text('TIPO_DEMANDA');
        $tipoDemanda->setRequired(false)
                                   ->setLabel('Tipo de Demanda: ')
                                   ->setAttrib('style', 'width: 540px;')
                                   ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma parte que a mesma será adicionada à lista. Ex.: Maria');
        
       /* Radio button para escolha do tipo de atendimento: CAUSA ou PROBLEMA */
        $osExecução = new Zend_Form_Element_Radio('TIPO_EXECUCAO');
        $osExecução->setLabel('*Local de Execução:')
                    ->setValue(2)
                    ->setRequired(false)
                    ->addMultiOptions(array(
                                            '2' => 'Interno',
                                            '1' => 'Externo',
                                            )); 
        
       $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
       $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
       $osDescrição = new Zend_Form_Element_Textarea('SSOL_DS_OBSERVACAO');
       $osDescrição->setLabel('Descrição:')
                           ->addFilter('StripTags')
                           ->addFilter('StringTrim')
                           ->setAttrib('style', 'width: 800px; height: 30px;')
                           ->addValidator('NotEmpty')
                           ->addValidator('StringLength', false, array(5, 500))
                           ->addFilter($Zend_Filter_HtmlEntities);
        
       $osEstimativa = new Zend_Form_Element_Checkbox('ESTIMATIVA_PF');
        $osEstimativa->setLabel('Estimar Ponto de Função:')
                ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                ->addDecorator('HtmlTag', array('tag' => 'div', 'style' => 'clear:both'))
//                ->setAttrib(array('style' => 'float: left;'))
                ->setRequired(false)
                ->setCheckedValue('S')
                ->setUncheckedValue('N');
       
        
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios')
                    ->setAttrib('style', 'display: none;');

        $consultar = new Zend_Form_Element_Submit('Consultar');      
        $this->addElements(array($osContratos,
                                 $osSistemas,
                                 $osFuncionalidades, 
                                 $osArtefatos,
                                 $tipoDemanda,
                                 $osExecução,
                                 $osDescrição , 
                                 $osEstimativa,
                                 $obrigatorio,
                                 $consultar));
    }

}