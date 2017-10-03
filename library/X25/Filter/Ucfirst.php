<?php
class X25_Filter_Ucfirst implements Zend_Filter_Interface {
	
	public function filter($value) {
		return ucfirst($value);
	}
}