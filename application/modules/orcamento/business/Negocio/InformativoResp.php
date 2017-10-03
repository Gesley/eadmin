<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre informativo resposta
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Informativoresposta
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_InformativoResp extends Orcamento_Business_Negocio_Base
{

    public function init ()
    {
        // Define a negocio
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Infr ();

        // Define a negocio
        $this->_negocio = 'informativoresposta';

    }

    /*
     * Atualiza o responsavel pelo informativo
     */
    public function editarResposavel($codigo, $dados){
        $dadosResp = array(
                'INFR_CD_INFORMATIVO' => $codigo["codigo"],
                'INFR_CD_RESPONSAVEL' => $dados['INFR_CD_RESPONSAVEL'],
        );

        // informativo a ser atualizado
        $where = "INFR_CD_INFORMATIVO =". $dados['INFO_NR_INFORMATIVO'];

        return $resultado = $this->_model->update( $dadosResp, $where);
    }


}
