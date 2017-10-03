<?php 
class X25_Form extends Zend_Form
{

	public function __construct($options = null)
	{
		$this->addElementPrefixPath('X25_Filter','X25/Filter','filter');
		parent::__construct($options);
	}

}