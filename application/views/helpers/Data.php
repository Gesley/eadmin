<?php
/*
 * Helper que retorna o uma data em v�rios formatos diferentes.
 * ex: echo $this->Data('2009-18-11')->extenso;
 * ex: echo $this->Data('2009-18-11')->pt_br;
 * ex: echo $this->Data('2009-18-11 09:11:00')->hora;
 * @author Nivaldo Arruda - nivaldo@gmail.com
 * @see www.nivaldoarruda.com.br
 * @version 1.0
 */
class Zend_View_Helper_Data extends Zend_View_Helper_Abstract {

	public function data($data)
	{
		list($ano, $mes, $dia) = explode("-", substr($data, 0, 10));

		//$this->extenso = $this->diasemana("$ano-$mes-$dia");
		$this->diasemana = "$dia/$mes/$ano";

		return $this;
	}


}
?>