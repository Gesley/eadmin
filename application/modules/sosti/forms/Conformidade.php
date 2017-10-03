<?php
class Sosti_Form_Conformidade extends Zend_Form
{
    public function init()
    {
        $this->setAction('salvaconformidade')
             ->setMethod('post')
             ->setName('frmConformidade');

                $CONFORMIDADE = new Zend_Form_Element_Select('SCONF_ID_TIPO');
		$CONFORMIDADE->setLabel('Selecione tipo de não conformidade:');

		$NIVEL = new Zend_Form_Element_Select('SNAT_ID_NIVEL');
		$NIVEL->setLabel('Nivel de atendimento:');
		
		//MUDAR PARA DINAMICO 
		$GRUPO = new Zend_Form_Element_Select('SGRS_ID_GRUPO');
		$GRUPO->setLabel('Selecione o grupo:');
		$GRUPO->addMultiOptions(array(
								'1'=>'Atendimento aos Usuários',
								'2'=>'ATENDIMENTO AOS USUÁRIOS ÁREA DE TI',
								'3'=>'Software - Desenvolvimento/Sustentação',
								'4'=>'Escritório de Projetos/NOC',
								'5'=>'Rede/Banco de Dados/Administração de Dados',
		));
		//MUDAR PARA DINAMICO
		
		
		$COMENTARIO =  new Zend_Form_Element_Textarea('COMENTARIO');
		$COMENTARIO->setAttrib('rows', 5);
		$COMENTARIO->setLabel('Comentário:');         
		$COMENTARIO->setRequired(true);
		$COMENTARIO->addValidator('NotEmpty');
		
		$SUBMIT = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array( $CONFORMIDADE,$COMENTARIO,$SUBMIT));
    }

}