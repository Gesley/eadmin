<?php

/**
 * @category	Services
 * @package		Services_Sisad_Leitura
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de serviço sobre a leitura de documentos e processos
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
class Services_Sisad_Leitura {

    public function getAnexados($documento) {

        $documento['DTPD_ID_TIPO_DOC'] = (isset($documento['DTPD_ID_TIPO_DOC']) ? $documento['DTPD_ID_TIPO_DOC'] : $documento['DTPD_ID_TIPO_DOC']);
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            if (!isset($documento['PRDI_ID_PROCESSO_DIGITAL'])) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $documento = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
            }
            $rn_leitura = new Trf1_Sisad_Negocio_Leitura();
            return $rn_leitura->getAnexadosAoProcesso($documento);
        } else {
            Zend_Debug::dump('não desenvolvido');
            exit;
        }
    }

    public function getAnexadosSemMetadados($documento) {
        $rn_leitura = new Trf1_Sisad_Negocio_Leitura();
        return $rn_leitura->getAnexadosSemMetadados($documento);
    }

    /**
     * Retorna os documentos anexados ao documento passado por parametro. Porém filtrados.
     * 
     * @param array $documento Documento principal na juntada
     * @param array $filtro Dados do formulario de filtro
     * @return array Documentos filtrados
     */
    public function filtroAnexados($documento, $filtro) {

        $documento['DTPD_ID_TIPO_DOC'] = (isset($documento['DTPD_ID_TIPO_DOC']) ? $documento['DTPD_ID_TIPO_DOC'] : $documento['DTPD_ID_TIPO_DOC']);
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //se não tiver dados do processo
            if (!isset($documento['PRDI_ID_PROCESSO_DIGITAL'])) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $documento = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
            }
            $rn_leitura = new Trf1_Sisad_Negocio_Leitura();
            return $rn_leitura->getAnexadosAoProcessoFiltro($documento, $filtro);
        } else {
            Zend_Debug::dump('não desenvolvido');
            exit;
        }
    }

    /**
     * Retorna os documentos anexados ao documento passado por parametro. Porém filtrados.
     * 
     * @param array $documento Documento principal na juntada
     * @param array $filtro Dados do formulario de filtro
     * @return array Documentos filtrados
     */
    public function filtroApensados($documento, $filtro) {

        $documento['DTPD_ID_TIPO_DOC'] = (isset($documento['DTPD_ID_TIPO_DOC']) ? $documento['DTPD_ID_TIPO_DOC'] : $documento['DTPD_ID_TIPO_DOC']);
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //se não tiver dados do processo
            if (!isset($documento['PRDI_ID_PROCESSO_DIGITAL'])) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $documento = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
            }
            $rn_leitura = new Trf1_Sisad_Negocio_Leitura();
            return $rn_leitura->getApensadosAtivosFiltro($documento, $filtro);
        } else {
            Zend_Debug::dump('não desenvolvido');
            exit;
        }
    }

    /**
     * Retorna os documentos anexados ao documento passado por parametro. Porém filtrados.
     * 
     * @param array $documento Documento principal na juntada
     * @param array $filtro Dados do formulario de filtro
     * @return array Documentos filtrados
     */
    public function filtroVinculos($documento, $filtro) {

        $documento['DTPD_ID_TIPO_DOC'] = (isset($documento['DTPD_ID_TIPO_DOC']) ? $documento['DTPD_ID_TIPO_DOC'] : $documento['DTPD_ID_TIPO_DOC']);
        if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
            //se não tiver dados do processo
            if (!isset($documento['PRDI_ID_PROCESSO_DIGITAL'])) {
                $rn_processo = new Trf1_Sisad_Negocio_Processo();
                $documento = $rn_processo->getProcessoPorIdDocumento($documento['DOCM_ID_DOCUMENTO']);
            }
            $rn_leitura = new Trf1_Sisad_Negocio_Leitura();
            return $rn_leitura->getVinculosAtivosFiltro($documento, $filtro);
        } else {
            Zend_Debug::dump('não desenvolvido');
            exit;
        }
    }

}