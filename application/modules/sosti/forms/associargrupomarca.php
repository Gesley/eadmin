<?php
class Sosti_Form_associargrupomarca extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
             $objMarca = new Application_Model_DbTable_OcsTbMarcMarca();
             $dataMarca = $objMarca->getMarca();
     		
             
          $objGrupo = new Application_Model_DbTable_OcsTbGrupGrupo ();
          $objMarca = new Application_Model_DbTable_OcsTbMarcMarca();
        

    	$grupoSelect = new Zend_Form_Element_Select('GRMA_ID_GRUPO_MAT_SERV');
        $grupoSelect->setLabel('Grupo:')
                       ->setRequired(true);
                       //->setOptions(array('style' => 'width:237px'));
     	$dataGrupo = $objGrupo->getGrupos();
     		
     	foreach($dataGrupo as $v)
     	{
                       $grupoSelect->addMultiOptions(array($v["GRUP_ID_GRUPO_MAT_SERV"]=>$v["GRUP_DS_GRUPO_MAT_SERV"]));
        }
        
    	$marcaSelect = new Zend_Form_Element_Checkbox('GRMA_ID_MARCA');
        $marcaSelect->setLabel('Selecione as marcas:')
                       ->setRequired(true)
                       ->setRequired(true);
                       
         $checkBoxes = new Zend_Form_Element_MultiCheckbox('GRMA_ID_MARCA',array(
         	'multiOptions' => $dataMarca,
         	'separator' => PHP_EOL
         ));
         	//$checkBoxes->removeDecorator('HtmlTag');

         $submit = new Zend_Form_Element_Submit('Salvar');
        
        $this->addElements(array($grupoSelect,$checkBoxes,$submit));
    }
}