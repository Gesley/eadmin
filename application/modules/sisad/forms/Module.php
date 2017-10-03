<?php
class Admin_Form_Module extends Zend_Dojo_Form
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
        $this->setName('module')
        	 ->setAttrib('id', 'module')
        	 ->setMethod('post')
        	 ->setElementFilters(array('StripTags','StringTrim'));
        	 
		Zend_Dojo::enableForm($this); 
		$this->getView()->validateDojoForm($this->getName());
		$this->getView()->sendDojoForm($this->getAttrib('id'),'base/public/admin/module/add');
		
        /**
		 * Montando o form
		 */
		$this->addElements(array(
			new Zend_Dojo_Form_Element_ValidationTextBox('module_name', array(
	            'required'   => true,
	            'label'      => 'Modulo',
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
            new Zend_Form_Element_Hidden('module_id', array(
            	'decorators' => array('ViewHelper'),
            )),
            new Zend_Dojo_Form_Element_SubmitButton('submit', array(
            	'attribs' => array('id'=> 'submitbutton'),
            	'ignore'  => true,
            	'label'   => 'Enviar',
            )),
        ));
    }
}