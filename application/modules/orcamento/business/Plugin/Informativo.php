<?php
/**
 * Contém chamadas a métodos via plugins
 *
 * e-Admin
 * e-Orçamento
 * Business - Plugin
 *
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém plugin específico para chamada do bootstrap
 *
 * @category Orcamento
 * @package Orcamento_Business_Plugin_Informativo
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Plugin_Informativo extends Zend_Controller_Plugin_Abstract
{

    /**
     * preDispatch: Função deste Plugin que fará chamada para método negocial
     * que verificará se o usuário (responsável) tem informativos para ler, e se
     * aplicável, redirecionar para tela em questão.
     *
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     * @return array com níveis de permissão e lotação / responsáveis
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function preDispatch ( Zend_Controller_Request_Abstract $requisicao )
    {
        // Perfil Desenvolvedor
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();

        //Zend_Debug::dump($perfilFull);
        
        
            // Variáveis da requisição
            $module = strtolower ( $requisicao->getModuleName () );
            $controller = strtolower ( $requisicao->getControllerName () );
            $action = strtolower ( $requisicao->getActionName () );

            $ehPost = $requisicao->isPost ();
            $ehGet = $requisicao->isGet ();

            // Evita execução fora do orçamento
            if ( $module == Trf1_Orcamento_Definicoes::NOME_MODULO ) {
                // Regra de negocial do informativo
                $classe = 'Orcamento_Business_Negocio_Informativo';

                    if ( class_exists ( $classe ) ) {
                        // Instancia a regra negocial
                        $info = new $classe ();
                        // Procura por informativos
                        $informativos = $info->retornaInformativos ();
                        // Verifica se tem informativos
                        if ( count ( $informativos ) > 0 && ( $controller != 'informativo' ) ) {
                            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
                            $redirector->gotoUrl('/orcamento/informativo/listagem');
                        }

                    }
            }

    }

}
