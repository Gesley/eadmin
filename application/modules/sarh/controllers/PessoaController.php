<?php

/**
 * @category	TRF1
 * @package		Sarh_PessoaController
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison.sb@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe controladora para manipulação de pessoas
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
class Sarh_PessoaController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->_redirector('index', 'index', 'admin');
    }

    public function ajaxJsonPessoasFisicasTrf1Action()
    {
        $termo = $this->_getParam('term', '');
        $servicePessoa = new Services_Rh_Pessoa();
        $retorno = $servicePessoa->retornaComboPessoasFisicasTrf1($termo);
        $fim = count($retorno);
        for ($i = 0; $i < $fim; $i++) {
            $retorno[$i] = array_change_key_case($retorno [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($retorno);
    }

    public function ajaxJsonPessoasFisicasExternaAction()
    {
        $termo = $this->_getParam('term', '');
        $servicePessoa = new Services_Rh_Pessoa();
        $retorno = $servicePessoa->retornaComboPessoasFisicasExterna($termo);
        $fim = count($retorno);
        for ($i = 0; $i < $fim; $i++) {
            $retorno[$i] = array_change_key_case($retorno [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($retorno);
    }

    public function ajaxJsonPessoasJuridicasAction()
    {
        $termo = $this->_getParam('term', '');
        $servicePessoa = new Services_Rh_Pessoa();
        $retorno = $servicePessoa->retornaComboPessoasJuridicasTrf1($termo);
        $fim = count($retorno);
        for ($i = 0; $i < $fim; $i++) {
            $retorno[$i] = array_change_key_case($retorno [$i], CASE_LOWER);
        }
        $this->_helper->json->sendJson($retorno);
    }

}
