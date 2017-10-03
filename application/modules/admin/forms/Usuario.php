<?php
class Admin_Form_Usuario extends Zend_Form 
{
	public function _init()
	{
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
        $this->setName('role')
        	 ->setMethod('post')
        	 ->setElementFilters(array('StripTags','StringTrim'));
        
        /*$query = Doctrine_Query::create()
                    ->select( 'r.role_id, r.role_name' )
                    ->from('Role r');
        $values = $query->execute( array(), Doctrine::HYDRATE_ARRAY );*/
        
        $role = new Role();
        $values = $role->getFormSelectOptions('role_id','role_name');
        
			
        /**
		 * Montando o form
		 */
		$this->addElements(array(
			new Zend_Form_Element_Text('user_name', array(
	            'required'   => true,
	            'label'      => 'Nome',
	            'filters'    => array('StringTrim','StripTags'),
	            'validators' => array(
	            	'NotEmpty',
					array('StringLength', false, array(3, 100)),
				),
	            //'decorators' => array($this->dojoDecorators),
            )),
			new Zend_Form_Element_Password('user_senha', array(
	            'required'   => true,
	            'label'      => 'Senha',
	            'filters'    => array('StringTrim','StripTags'),
	            'validators' => array(
	            	'NotEmpty',
					array('StringLength', false, array(6, 20)),
				),
	            //'decorators' => array($this->dojoDecorators),
            )),
			new Zend_Form_Element_Text('user_email', array(
	            'required'   => true,
	            'label'      => 'E-mail',
	            'filters'    => array('StringTrim','StripTags'),
	            'validators' => array(
	            	'NotEmpty',
					'EmailAddress'
				),
	            //'decorators' => array($this->dojoDecorators),
            )),
            new Zend_Form_Element_Select('user_fl_tivo',array(
        		'label'      => 'Usuário está ativo?',
        		'multiOptions' => array('N'=>'Não','S'=>'Sim'),
        		'filters'    => array('StringTrim','StripTags'),
	            'validators' => array('NotEmpty'),
        	)),
            new Zend_Form_Element_Select('role_id',array(
        		'label'      => 'Grupo',
        		'multiOptions' => $values,
        		'filters'    => array('StringTrim','StripTags'),
	            'validators' => array('NotEmpty'),
        	)),
            new Zend_Form_Element_Hash('hash','csrf',array(
            	'ignore' => true,
            	'decorators' => array('ViewHelper'),
            )),
            new Zend_Form_Element_Hidden('user_id', array(
            	'decorators' => array('ViewHelper'),
            )),
            new Zend_Form_Element_Submit('submit', array(
            	'attribs' => array('id'=> 'submitbutton'),
            	'ignore'  => true,
            	'label'   => 'Enviar',
            	'decorators' => array(
            		'ViewHelper',
            		'Errors',
	        		array('HtmlTag', array('tag' => 'div', 'class' => 'buttons')),
	        	)
            )),
        ));
    }
}