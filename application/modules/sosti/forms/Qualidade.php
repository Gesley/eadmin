<?php
class Sosti_Form_Qualidade extends Zend_Form
{
    public function init()
    {

        $this->setAction('');

        $this->setAction('add')

             ->setMethod('post');

		$indicadorObj = new Application_Model_DbTable_SosTbSinsIndicNivelServ ();

		

        $NOME_INDICADOR = new Zend_Form_Element_Text('TIDE_NM_DEFEITO');
        $NOME_INDICADOR->setLabel('Nome do defeito')
        			   ->setAttrib('style', 'width: 200px; text-transform: uppercase;')
        			   ->setRequired(true);
        

       $row = $indicadorObj->getIndicadorDefeito();
       
       $INDICADOR_SERVICO = new Zend_Form_Element_Select('TIDE_ID_INDICADOR');
       $INDICADOR_SERVICO->setLabel('Indicador de nível de serviço');
       foreach ($row as $grupo) {
           $INDICADOR_SERVICO->addMultiOptions(array($grupo['SINS_ID_INDICADOR']=>$grupo['SINS_DS_INDICADOR']));
       }
       


       $DESCRICAO_DEFEITO = new Zend_Form_Element_Textarea('TIDE_DS_DEFEITO');
       $DESCRICAO_DEFEITO->setLabel('Descrição do defeito')
       					 ->setAttrib('style', 'width: 540px; height: 30px; text-transform: uppercase;')
       					 ->setRequired(true);
       
       
	   $IC_ATIVO = new Zend_Form_Element_Select('TIDE_IC_ATIVO');
	   $IC_ATIVO->setLabel('Ativo?')
			    ->setRequired(true)
			    ->addMultiOptions(array('S' => 'Sim', 'N' => 'Não'));;
		
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($NOME_INDICADOR,
								 $DESCRICAO_DEFEITO, 
                				 $INDICADOR_SERVICO,
								 $IC_ATIVO,
								 $submit));
    }

}