<?php
/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 *
 * e-Admin
 * e-Orçamento
 * Facade
 *
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre esfera, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Logdados
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class  Orcamento_Facade_Logdados extends Orcamento_Facade_Base
{

    public function init ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Logdados ();

        // Define a controle desta action
        $this->_controle = 'logdados';
    }


    public function retornaSqlnclusaolog($dados)
    {
        $sql = "
            INSERT INTO CEO.CEO_TB_LOG_DADOS
                (
                    LOG_DT_DATA,
                    LOG_TP_ACAO,
                    LOG_DS_UNIDADE_GESTORA,
                    LOG_CD_MATRICULA_USUARIO,
                    LOG_DS_FUNCIONALIDADE,
                    LOG_DS_DESCRICAO,
                    LOG_ID_DADOS
                )VALUES(
                    ".$dados['LOG_DT_DATA'].",
                    ".$dados['LOG_TP_ACAO'].",
                    '".$dados['LOG_DS_UNIDADE_GESTORA']."',
                    '".$dados['LOG_CD_MATRICULA_USUARIO']."',
                    '".$dados['LOG_DS_FUNCIONALIDADE']."',
                    '".$dados['LOG_DS_DESCRICAO']."',
                    CEO_SQ_TB_LOG.NEXTVAL
                )
        ";

        Zend_debug::dump($sql); die;

        $banco = Zend_Db_Table::getDefaultAdapter ();
        return  $banco->query ( $sql );
    }


    public function retornaLogDescricaoFuncionalidade( $controller, $acao)
    {
        $sql = "
            SELECT
                   PAPL.PAPL_NM_PAPEL ||' - '||PAPL_DS_FINALIDADE as LABEL
            FROM
                  OCS_TB_CTRL_CONTROLE_SISTEMA CTRL
            LEFT JOIN
                  OCS.OCS_TB_ACAO_ACAO_SISTEMA ACAO ON ACAO_ID_CONTROLE_SISTEMA = CTRL_ID_CONTROLE_SISTEMA
            LEFT JOIN
                  OCS_TB_PAPL_PAPEL PAPL ON PAPL_ID_ACAO_SISTEMA = ACAO_ID_ACAO_SISTEMA
            WHERE
                  CTRL_NM_CONTROLE_SISTEMA = '$controller'
                  AND ACAO_NM_ACAO_SISTEMA = '$acao'
        ";
        
        $banco = Zend_Db_Table::getDefaultAdapter ();

        return $banco->fetchRow ( $sql );

    }

    public function retornaLogDescricaoAcao($controller, $acao, $codigo)
    {
        // se codigos for um array
        // tratar aqui
        switch ($acao) {
            case 'incluir':
                $resposta =  "Incluiu um(a) $controller codigo: $codigo";
                break;
            case 'editar':
                $resposta =  "Editou o(a) $controller codigo: $codigo";
                break;
            case 'excluir':
                $resposta =  "Excluiu o(as) $controller(s): $codigo";
                break;
            case 'restaurar':
                $resposta =  "Restaurou o(as) $controller(s): $codigo";
                break;            
            default: $resposta = null;
        }

        return $resposta;
    }
}