<?php
class Admin_Form_TipoElei extends Zend_Dojo_Form
{
	public function _init()
	{
		$this->setElementDecorators(array(
			'DijitElement',
			'Errors',
			array('HtmlTag', array('tag' => 'div', 'class' => 'x-form-element')),
			array('Label',   array('tag' => 'div')),
		));
		
		$this->setDecorators(array(
			'FormElements',
			array('HtmlTag',array('tag'=>'div','placement'=>'REPLACE')),
			'DijitForm'
		));
		
		$this->addDecorator('DijitElement')
             ->addDecorator('Errors')
             ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
             ->addDecorator('HtmlTag', array('tag' => 'div'))
             ->addDecorator('Label', array('tag' => 'div', 'class' => 'form-item-label-top'));
		
		$hiddenDecorators = array(
	        'ViewHelper',
	        'Errors',
	        array('Label',array('tag','')),
	        array('HtmlTag', array('tag' => '')),
	    ); 
	}
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('tipo-elei')
        	 ->setAttrib('id', 'tipo-elei')
        	 ->setMethod('post')
        	 ->setElementFilters(array('StripTags','StringTrim'));
        	 
		Zend_Dojo::enableForm($this);
		/* 
		$this->getView()->validateDojoForm($this->getName());
		$this->getView()->sendDojoForm($this->getAttrib('id'),'base/public/admin/module/add');
		*/
		$this->getView()->dojoForm($this->getAttrib('id'),'base/admin/tipo-elei/add', false, true);
			 
        $elteCodigo = new Zend_Form_Element_Hidden('ELTE_CODIGO');
        $elteCodigo->addFilter('Int')
                   ->removeDecorator('Label')
                   ->removeDecorator('HtmlTag');
                   
        $suplente = new Zend_Form_Element_Hidden('ELTE_SUPLENTE');
        $suplente->setValue('N')
                 ->removeDecorator('Label')
                 ->removeDecorator('HtmlTag');
                   
        $quantCedulas = new Zend_Form_Element_Hidden('ELTE_QUANT_CEDULAS');
        $quantCedulas->addFilter('Int')
                     ->setValue(1)
                     ->removeDecorator('Label')
                     ->removeDecorator('HtmlTag');

        $descricao = new Zend_Dojo_Form_Element_Textarea('ELTE_DESCRICAO');
        $descricao->setRequired(true)
                  ->setOptions(array('rows'=>5, 'cols'=>50))
                  ->setLabel('Descrição ')
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addValidator('NotEmpty')
                  ->addValidator('StringLength', false, array(5,500));
                  
        $tipoVotacao = new Zend_Dojo_Form_Element_FilteringSelect('ELTE_ELTV_CODIGO');
        $tipoVotacao->setRequired(true)
                    ->setLabel('Tipo de Votação:')
                    ->addMultiOption('',':: Selecione ::');
                    foreach(RH_ELEI_TIPO_VOTACAO::getTipoVotacao() as $d) {
                        $tipoVotacao->addMultiOption($d['ELTV_CODIGO'], $d['ELTV_DESCRICAO']);
                    }
                    
		$tabelafuncJuiz = new RH_FUNCOES_JUIZES();
		$funcJuiz = new Zend_Dojo_Form_Element_FilteringSelect('ELTE_FUJU_COD_FUNC');
        $funcJuiz->setRequired(true)
                 ->setLabel('Orgão Julgador:')
                 ->addMultiOption('',':: Selecione ::');
                 foreach(RH_FUNCOES_JUIZES::getFuncJuizes() as $j) {
                     $funcJuiz->addMultiOption($j['FUJU_COD_FUNC_JUIZ'], $j['FUJU_DSCR_FUNC_JUIZ']);
                 }
             
        $quantVagas = new Zend_Dojo_Form_Element_NumberSpinner('ELTE_QUANT_VAGAS');
        $quantVagas->setRequired(true)
                   ->setLabel('Quantidade de Vagas:')
                   ->setOptions(array('min'=>1, 'max'=>25, 'places'=>2))
                   ->addValidator('NotEmpty');
             
        $quantVotos = new Zend_Dojo_Form_Element_NumberSpinner('ELTE_QUANT_VOTOS');
        $quantVotos->setRequired(true)
                   ->setLabel('Quantidade de Votos:')
                   ->setOptions(array('min'=>1, 'max'=>25, 'places'=>2))
                   ->addValidator('NotEmpty');
             
        $elteTipoCandidato = new Zend_Dojo_Form_Element_RadioButton('ELTE_TIPO_CANDIDATO');
        $elteTipoCandidato->setRequired(true)
                          ->setLabel('Tipo de Candidato:')
                          ->setSeparator('')
                          ->addValidator('NotEmpty')
                          ->setMultiOptions(array('DS'=>'Desembargador',
                                                  'JU'=>'Juiz'));
             
        $eltePresidentePart = new Zend_Dojo_Form_Element_RadioButton('ELTE_PRESIDENTE_PART');
        $eltePresidentePart->setRequired(true)
                           ->setLabel('Presidente Candidato:')
                           ->setSeparator('')
                           ->addValidator('NotEmpty')
                           ->addMultiOptions(array('N'=>'Não', 'S'=>'Sim'));
             
        $elteVicePresidPart = new Zend_Dojo_Form_Element_RadioButton('ELTE_VICE_PRESID_PART');
        $elteVicePresidPart->setRequired(true)
                           ->setLabel('Vice Presidente Candidato:')
                           ->setSeparator('')
                           ->addValidator('NotEmpty')
                           ->addMultiOptions(array('N'=>'Não', 'S'=>'Sim'));
             
        $elteCorregedorPart = new Zend_Dojo_Form_Element_RadioButton('ELTE_CORREGEDOR_PART');
        $elteCorregedorPart->setRequired(true)
                           ->setLabel('Corregedor Candidato:')
                           ->setSeparator('')
                           ->addValidator('NotEmpty')
                           ->addMultiOptions(array('N'=>'Não',
                                                   'S'=>'Sim'));
             
        $elteQuorum = new Zend_Dojo_Form_Element_FilteringSelect('ELTE_QUORUM');
        $elteQuorum->setRequired(true)
                   ->setLabel('Quorum:')
                   ->addMultiOptions(array(''=>':: Selecione ::',
                                           '1'=>'Maioria Simples',
                                           '2'=>'Maioria Absoluta',
                                           '3'=>'Dois Terços'));
             
        $elteTipoResultado = new Zend_Dojo_Form_Element_FilteringSelect('ELTE_TIPO_RESULTADO');
        $elteTipoResultado->setRequired(true)
                          ->setLabel('Tipo Resultado:')
                          ->addMultiOptions(array(''=>':: Selecione ::',
                                                  '1'=>'Maioria Simples',
                                                  '2'=>'Maioria Absoluta'));
                          
        $data = new Zend_Dojo_Form_Element_DateTextBox('DATA');
        $data->setLabel('Data:')
             ->addValidator('NotEmpty')
             ->setRequired(true);  
             
        $hora = new Zend_Dojo_Form_Element_TimeTextBox('HORA');
        $hora->setLabel('Hora:')
             ->addValidator('NotEmpty')
             ->setRequired(true);
      
        $submit = new Zend_Dojo_Form_Element_SubmitButton('Salvar');
        $voltar = new Zend_Dojo_Form_Element_Button('Voltar');
        $voltar->setOptions(array('onclick'=>'javascript:history.go(-1)'));

        $this->addElements(array($elteCodigo, $descricao, $tipoVotacao, $funcJuiz, $quantVagas, $quantVotos, $quantCedulas,
                                 $suplente, $elteTipoCandidato, $eltePresidentePart, $elteVicePresidPart, $elteCorregedorPart,
                                 $elteQuorum, $elteTipoResultado, $data, $hora, $submit, $voltar));
                          
    }
}