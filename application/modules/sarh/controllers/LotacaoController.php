<?php

/**
 * @category	TRF1
 * @package		Sarh_LotacaoController
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison.sb@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe controladora para manipulação de lotações ou unidades
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Sarh_LotacaoController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->_redirector('index', 'index', 'admin');
    }

    public function ajaxComboSubsecaoPorSecaoAction()
    {
        $termo = $this->_getParam('id_secao', '');
        $serviceLotacao = new Services_Rh_Lotacao();
        $this->view->array = $serviceLotacao->retornaComboSubsecoes($termo);
        
    }

    public function ajaxComboLotacoesPorSubsecaoAction()
    {
        $termo = $this->_getParam('id_subsecao', '');
        $serviceLotacao = new Services_Rh_Lotacao();
        $this->view->array = $serviceLotacao->retornaComboUnidadesPorSubsecao($termo);
    }

    private function trataSendJson($array)
    {
        $fim = count($array);
        for ($i = 0; $i < $fim; $i++) {
            $array[$i] = array_change_key_case($array [$i], CASE_LOWER);
        }
        return $array;
    }

}
