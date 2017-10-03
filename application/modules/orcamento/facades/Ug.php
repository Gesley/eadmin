<?php
/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 *
 * e-Admin
 * e-Orçamento
 * Facade
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre esfera, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Ug
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Ug extends Orcamento_Facade_Base
{

    /**
     * Método construtor da classe
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Define a controle desta action
        $this->_controle = 'ug';
    }

    /**
     * Retorna os elementos da combo de ug
     * @return array
     */
    public function retornaComboUg () {
        $sql = "
                    SELECT
                    UNGE_CD_UG,
                    UNGE_CD_UG||' - '||UNGE_DS_UG
                    FROM CEO_TB_UNGE_UNIDADE_GESTORA
                    WHERE UNGE_DH_EXCLUSAO_LOGICA IS NULL
                    ORDER BY
                        UNGE_DS_UG ASC ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchPairs($sql);

    }

}