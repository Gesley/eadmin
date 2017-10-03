<?php
/**
 * @category	        TRF1
 * @package		Service_Sosti_TipoServico
 * @copyright	        Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * TRF1, Classe negocial sobre Respostas Padrões do Sistema
 */
class Services_Sosti_TipoServico {
    
    private $trf1_TipoServico;
    
    /**
     * 
     * Função construtora
     * @author Daniel Rodrigues
     * 
     */
    function __construct() {
        
        $this->trf1_TipoServico = new Trf1_Sosti_Negocio_TipoServico();
    }

    
    /**
     * Função que busca os tipos de serviço do sistema
     * @param type $idGrupo Define o ID do grupo para a busca dos valores
     * @return Array Dados selecionados da base de dados
     */
    public function getTipoServicoByGrupo($idGrupo){
        
        return $this->trf1_TipoServico->getTipoServicoByGrupo($idGrupo);
        
    }
    
    /**
     * Função que busca os tipos de serviço do sistema
     * @param type $idGrupo Define o ID do grupo para a busca dos valores
     * @return Array Dados selecionados da base de dados
     */
    public function getTipoServicoByGrupos($idGrupos){
        
        return $this->trf1_TipoServico->getTipoServicoByGrupos($idGrupos);
        
    }
    
}

?>
