<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Negocio_Confidencialidade
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre confidencialidade de documentos no sisad
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
class Trf1_Sisad_Negocio_Confidencialidade
{

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Adapter_Abstract $_db
     */
    protected $_db;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct()
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * Retorna os tipos de confidencialidade administrativas no sisad
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function getConfidencialidadesAdministrativas()
    {
        $dbTableSisadConf = new Application_Model_DbTable_Sisad_SadTbConfConfidencialidade();
        //enquanto não existir as confidencialidades judiciais usar o codigo abaixo
        $confidencialidadesExcluidas = array(
            Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_RESTRITO_A_SUBGRUPO_INTRANET
        );
        return $dbTableSisadConf->fetchAll('CONF_ID_CONFIDENCIALIDADE NOT IN(' . implode(',', $confidencialidadesExcluidas) . ')', 'CONF_ID_CONFIDENCIALIDADE ASC')->toArray();
    }

    /**
     * Retorna os tipos de confidencialidade judiciais no sisad
     * @return array
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     * trocar o autor ao desenvolver a function
     */
    public function getConfidencialidadesJudiciais()
    {
        return null;
    }

}
