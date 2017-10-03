<?php
class Admin_Form_Resource extends Zend_Dojo_Form 
{
	public function _init()
	{
		/*
		$this->setElementDecorators(array(
			'DijitElement',
			'Errors',
			array('HtmlTag', array('tag' => 'div', 'class' => 'x-form-element')),
			array('Label',   array('tag' => 'div', 'class' => 'form_item_label_top')),
		),array('module_id','resource_name'),true);
		
		$this->setDecorators(array(
			'FormElements',
			array('HtmlTag',array('tag'=>'div','placement'=>'REPLACE')),
			'DijitForm'
		));
		
		$this->addDecorator('DijitElement')
             ->addDecorator('Errors')
             ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
             ->addDecorator('HtmlTag', array('tag' => 'div'))
             ->addDecorator('Label', array('tag' => 'div', 'class' => 'form_item_label_top'));
		
		$hiddenDecorators = array(
	        'ViewHelper',
	        'Errors',
	        array('Label',array('tag','')),
	        array('HtmlTag', array('tag' => '')),
	    );
	    */ 
	}
	
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('resource')
        	 ->setMethod('post')
        	 ->setAttrib('id', 'resource')
        	 ->setElementFilters(array('StripTags','StringTrim'));
        	 
        Zend_Dojo::enableForm($this); 
        /*
		$this->getView()->validateDojoForm($this->getName());
		$this->getView()->sendDojoForm($this->getAttrib('id'),$formAction);
		*/
		$formAction = $this->getView()->baseUrl() . '/admin/resource/add/format/json'; 
		$this->getView()->dojoForm($this->getAttrib('id'),$formAction,true);
      	/*        	 
       	$module = new Module();
        $values = $module->getFormSelectOptions('module_id','module_name');
        */
		$values = array(0 =>'Wilton',1=>'teste');
        /**
		 * Montando o form
		 */
		$this->addElements(array(
			new Zend_Dojo_Form_Element_FilteringSelect('module_id',array(
				'required'   => true,
        		'label'        => 'Modulo',
				'value'        => 1,
		        'autocomplete' => false,
        		'multiOptions' => $values,
        		'filters'      => array('StringTrim','StripTags'),
	            'validators'   => array('NotEmpty'),
	            'description'  => 'insira o nome do modulo',
				'decorator'    => array('Label',array('class'=>'x')),
        	)),
			new Zend_Dojo_Form_Element_ValidationTextBox('resource_name', array(
	            'required'   => true,
	            'label'      => 'Recurso',
	            'filters'    => array('StringTrim','StripTags'),
	            'validators' => array(
	            	'NotEmpty',
					array('StringLength', false, array(2, 255)),
				),
	            //'decorators' => array($this->dojoDecorators),
            )),
            /*
            new Zend_Form_Element_Hash('hash','csrf',array(
            	'ignore' => true,
            	'decorators' => array('ViewHelper'),
            )),
            */
            new Zend_Form_Element_Hidden('resource_id', array(
            	'decorators' => array('ViewHelper'),
            )),
            new Zend_Dojo_Form_Element_SubmitButton('submit', array(
            	'attribs' => array('id'=> 'submitbutton'),
            	'ignore'  => true,
            	'label'   => 'Enviar',
            )),
        ));
        
        $this->setElementDecorators(array(
			'DijitElement',
			'Errors',
        	'Description',
			array('HtmlTag', array('tag' => 'div', 'class' => 'x-form-element')),
			array('Label',   array('tag' => 'div')),
			array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'x-form-item')),
		),array('submit','resource_id'),false);
        
        /*
        $this->addDisplayGroup(
        	array('submit'), 
        	'buttons'
        );
        
        $buttonsGroup = $this->getDisplayGroup('buttons');
        $buttonsGroup->removeDecorator('Fieldset');
  		$buttonsGroup->removeDecorator('HtmlTag');
  		$buttonsGroup->removeDecorator('DtDdWrapper');
  		
  		$this->setDisplayGroupDecorators(array(
			'FormElements',
			array('HtmlTag', array('tag'=>'div')),
      	));
      	*/
    }
}