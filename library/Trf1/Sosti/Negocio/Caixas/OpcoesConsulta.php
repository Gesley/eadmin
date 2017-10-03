<?php
/**
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leonan Alves dos Anjos
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Garantia dos serviços do desenvolvimento
 * 
 * ====================================================================================================
 * LICENSA
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
class Trf1_Sosti_Negocio_Caixas_OpcoesConsulta
{
    protected $OpPrazo = false;
    protected $OpEspera = false;
    protected $OpServico = false;
    protected $OpNivel = false;
    protected $OpIdCaixa;
    
    public function __construct() {
        
	}
    
    /**
     * @abstract Indica que é necessário juntar o prazo na consulta 
     * @param boolean $flag
     */
    public function setOpPrazo($flag) {
        $this->OpPrazo = (bool) $flag;
    }
    
    /**
     * @abstract Indica que é necessário juntar o prazo na consulta 
     * @param boolean $flag
     * @return boolean OpPrazo
    */
    public function getOpPrazo() {
        return $this->OpPrazo;
    }
    
    /**
     * @abstract Indica que é necessário juntar a espera na consulta 
     * @param boolean $flag
     */
    public function setOpEspera($flag) {
        $this->OpEspera = (bool) $flag;
    }
    
    /**
     * @abstract Indica que é necessário juntar a espera na consulta 
     * @param boolean $flag
     * @return boolean OpPrazo
    */
    public function getOpEspera() {
        return $this->OpEspera;
    }
    
    /**
     * @abstract Indica que é necessário juntar o serviço na consulta 
     * @param boolean $flag
     */
    public function setOpServico($flag) {
        $this->OpServico = (bool) $flag;
    }
    
    /**
     * @abstract Indica que é necessário juntar o serviço na consulta 
     * @param boolean $flag
     * @return boolean OpPrazo
    */
    public function getOpServico() {
        return $this->OpServico;
    }
    
    /**
     * @abstract Indica que é necessário juntar o nivel na consulta 
     * @param boolean $flag
     */
    public function setOpNivel($flag) {
        $this->OpNivel = (bool) $flag;
    }
    
    /**
     * @abstract Indica que é necessário juntar o nivel na consulta 
     * @param boolean $flag
     * @return boolean OpPrazo
    */
    public function getOpNivel() {
        return $this->OpNivel;
    }
    
    /**
     * @abstract Indica qual ou quais caixas filtrar
     * @param int/array/string $flag
     */
    public function setOpIdCaixa($IdCaixa) {
        $this->OpIdCaixa = $IdCaixa;
    }
    
    /**
     * @abstract Indica qual ou quais caixas filtrar 
     * @return int/array/string  OpIdCaixa
    */
    public function getOpIdCaixa() {
        return $this->OpIdCaixa;
    }
    
}