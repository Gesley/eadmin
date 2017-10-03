<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Distribuicao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Sisad-Distribuição de Processos Administrativos
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
class Trf1_Sisad_Negocio_OrgaoJulgador {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Retorna os orgão julgadores que possuiem apenas juízes
     * 
     * @param	none
     * @return  array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getOrgaoEspecial(){
        return array(1000 => 'Plenário', 2000 => 'Corte Especial Administrativa', 3000 => 'Conselho de Administração');
    }
    
    /**
     * verifica se orgão julgadores possui apenas juízes
     * 
     * @param	int $idOrgao
     * @return  boolean
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function isOrgaoEspecial($idOrgao){
        $arrayOrgaosEspeciais = $this->getOrgaoEspecial();
        return isset($arrayOrgaosEspeciais[$idOrgao]);
    }
}