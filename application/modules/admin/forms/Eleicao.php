<?php
class Admin_Form_Eleicao extends Zend_Dojo_Form
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
        $this->setName('eleicao')
        	 ->setAttrib('id', 'eleicao')
        	 ->setMethod('post')
        	 ->setElementFilters(array('StripTags','StringTrim'));
        	 
		Zend_Dojo::enableForm($this);
		/* 
		$this->getView()->validateDojoForm($this->getName());
		$this->getView()->sendDojoForm($this->getAttrib('id'),'base/public/admin/module/add');
		*/
		$this->getView()->dojoForm($this->getAttrib('id'),'base/admin/eleicao/add', false, true);
		
        /**
		 * Montando o formulário
		 */
		$elel_codigo = new Zend_Form_Element_Hidden('ELEL_CODIGO');
        $elel_codigo->addFilter('Int')
                   ->removeDecorator('Label')
                   ->removeDecorator('HtmlTag');
                   
        $elel_compl_descricao = new Zend_Dojo_Form_Element_Textarea('ELEL_COMPL_DESCRICAO');
        $elel_compl_descricao->setRequired(true)
                             ->setOptions(array('rows'=>5, 'cols'=>50))
                             ->setLabel('Complemento da Descrição:')
                             ->addFilter('StripTags')
                             ->addFilter('StringTrim')
                             ->addValidator('NotEmpty')
                             ->addValidator('StringLength', false, array(5,500));
                   
        $elel_elte_codigo = new Zend_Form_Element_Hidden('ELEL_ELTE_CODIGO');
        $elel_elte_codigo->addFilter('Int')
                         ->removeDecorator('Label')
                         ->removeDecorator('HtmlTag');

        $data = new Zend_Dojo_Form_Element_DateTextBox('DATA');
        $data->setLabel('Data:')
             ->addValidator('NotEmpty')
             ->setRequired(true);  
             
        $hora = new Zend_Dojo_Form_Element_TimeTextBox('HORA');
        $hora->setLabel('Hora:')
             ->addValidator('NotEmpty')
             ->setRequired(true);
                           
      	$elel_func_sigla_presidente = new Zend_Dojo_Form_Element_FilteringSelect('PRESIDENTE');
        $elel_func_sigla_presidente->setRequired(true)
                                   ->setLabel('Selecione o Presidente da Eleição:')
                                   ->addMultiOption('',':: Selecione ::');
					               foreach(RH_PESSOAL::getFuncJuiz() as $d) {
					                   $elel_func_sigla_presidente->addMultiOption($d['SIGLA'].$d['COD'], $d['PESS_NOME']);
					               }
                  
        $elel_data_encerramento = new Zend_Form_Element_Hidden('ELEL_DATA_ENCERRAMENTO');
        $elel_data_encerramento->removeDecorator('Label')
                               ->removeDecorator('HtmlTag');
                                 
        $elel_cod_proc = new Zend_Dojo_Form_Element_TextBox('ELEL_COD_PROC');
        $elel_cod_proc->setRequired(true)
                      ->setLabel('N. do Processo Administrativo:')
                      ->setDescription('(Ano + Nº Processo. Ex.: 200708998)')
                      ->addValidator('NotEmpty');               
                                 
        $elel_cod_secsubsec = new Zend_Dojo_Form_Element_FilteringSelect('ELEL_COD_SECSUBSEC');
        $elel_cod_secsubsec->setRequired(true)
                           ->setLabel('Selecione a Seção/Subseção:')
                           ->addMultiOption('',':: Selecione ::');
                           foreach(P_SECAO_SUBSECAO::getSecaoSubsecao() as $d) {
                               $elel_cod_secsubsec->addMultiOption($d['SESU_CD_SECSUBSEC'], $d['SESU_DS_SECSUBSEC']);
                           }
                      
        $elel_quant_vagas = new Zend_Dojo_Form_Element_NumberSpinner('ELEL_QUANT_VAGAS');
        $elel_quant_vagas->setRequired(true)
                         ->setLabel('Quantidade de Vagas:')
                         ->setOptions(array('min'=>1, 'max'=>25, 'places'=>2))
                         ->addValidator('NotEmpty');

//        $vincularEleicao = new Zend_Dojo_Form_Element_RadioButton('dependencia');
//        $vincularEleicao->setRequired(true)
//                           ->setLabel('Deseja Vincular Esta Eleição a Outra?')
//                           ->setSeparator('')
//                           ->addValidator('NotEmpty')
//                           ->addMultiOptions(array('N'=>'Não',
//                                                   'S'=>'Sim'));
                   
        $elel_codigo_vinc = new Zend_Dojo_Form_Element_FilteringSelect('ELEL_CODIGO_VINC');
        $elel_codigo_vinc->setLabel('Codigo Eleicao Vinculada:')
                         ->addMultiOption('',':: Selecione ::')
                         ->addMultiOptions(array(1=>'el 1',
                                                 2=>'el 2'));
                   
        $elel_tipo_cedula = new Zend_Dojo_Form_Element_FilteringSelect('ELEL_TIPO_CEDULA');
        $elel_tipo_cedula->setRequired(true)
                      ->setLabel('Tipo de Cédula:')
                      ->addMultiOption('',':: Selecione ::');
                      foreach(RH_ELEI_TIPO_CEDULA::getTipoCedula() as $d) {
                         $elel_tipo_cedula->addMultiOption($d['ELTC_CODIGO'], $d['ELTC_DESCRICAO']);
                      }

        $submit = new Zend_Dojo_Form_Element_SubmitButton('Salvar');
        $submit->setIgnore(true);
       
//        $voltar = new Zend_Dojo_Form_Element_Button('Voltar');
//        $voltar->setOptions(array('onclick'=>'javascript:history.go(-1)'));
        
        $this->addElements(array($elel_elte_codigo,$elel_compl_descricao,/*$elel_func_cod_presidente,*/$elel_func_sigla_presidente,
                                 $elel_cod_secsubsec,$data,$hora,$elel_data_encerramento,$elel_cod_proc,$elel_quant_vagas,
                                 $elel_tipo_cedula,$elel_codigo_vinc,$elel_codigo,$submit));
    }
}