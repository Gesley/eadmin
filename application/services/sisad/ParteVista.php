<?php

/**
 * @category	Services
 * @package		Services_Sisad_ParteVista
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço de partes e vistas
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
class Services_Sisad_ParteVista {

    /**
     * Armazena o objeto do adaptador
     *
     * @var Zend_Db_Table_Abstract $_db
     */
    protected $_db;

    /**
     * Armazena dados de sessao do usuário logado
     *
     * @var Zend_Session_Namespace $_userNs
     */
    private $_userNs;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $this->_userNs = new Zend_Session_Namespace('userNs');
    }

    /**
     * Adiciona ou completa as partes e vistas de um documento
     * @param array $documento
     * @param array $data
     * @param bool $commit
     * @return array
     */
    public function add($documento, $data, $commit = true) {
        $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        return $rn_parteVistas->addParteVistas($documento, $data, $commit);
    }

    public function desativaVistas($documento, $commit = true) {
        $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        try {
            if ($rn_parteVistas->desativaVistas($documento) > 0) {
                return array('validado' => true, 'mensagem' => 'Vistas desativadas com sucesso.');
            } else {
                return array('validado' => false, 'mensagem' => 'Aconteceu um problema ao desativar as vistas. Provavelmente o documento não foi encontrado.');
            }
        } catch (Exception $e) {
            return array('validado' => false, 'mensagem' => $e->getMessage());
        }
    }

    public function isVisivelAoUsuario($documento) {
        $rn_parteVistas = new Trf1_Sisad_Negocio_ParteVistas();
        $visivel = $rn_parteVistas->statusSigiloVista($documento, $this->_userNs->matricula);
        $documento['CONF_ID_CONFIDENCIALIDADE'] = (isset($documento['DOCM_ID_CONFIDENCIALIDADE']) ? $documento['DOCM_ID_CONFIDENCIALIDADE'] : $documento['CONF_ID_CONFIDENCIALIDADE']);
        //Se processo é público OU documento pode ser visto pelo usuário
        return ($documento['CONF_ID_CONFIDENCIALIDADE'] == Trf1_Sisad_Definicoes::CONFIDENCIALIDADE_PUBLICO || $visivel['tem_vista'] || $visivel['sigiloso'] == 'N');
    }

}