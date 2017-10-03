<?php

class App_View_Helper_Mesportugues extends Zend_View_Helper_Abstract
{

	public function mesportugues($translator = NULL)
	{
            switch ($translator) {
                case 'January':
                    return 'Janeiro';
                    break;
                case 'February':
                    return 'Fevereiro';
                    break;
                case 'March':
                    return 'Março';
                    break;
                case 'April':
                    return 'Abril';
                    break;
                case 'May':
                    return 'Maio';
                    break;
                case 'June':
                    return 'Junho';
                    break;
                case 'July':
                    return 'Julho';
                    break;
                case 'August':
                    return 'Agosto';
                    break;
                case 'September':
                    return 'Setembro';
                    break;
                case 'October':
                    return 'Outubro';
                    break;
                case 'November':
                    return 'Novembro';
                    break;
                case 'December':
                    return 'Dezembro';
                    break;
                default:
                    return 'Mês não identificado, entrar em contato com o Administrador do Sistema';
                    break;
            }
		

	}
}