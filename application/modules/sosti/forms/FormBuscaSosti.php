<?php
class Sosti_Form_FormBuscaSosti extends Zend_Form 
{
    public function init()
    {
        $this->setAction($this->_getBaseUrl() .'/sosti/pesquisarsolicitacoes')
             ->setName('pesquisaSosti')   
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs');
        
        $docm_nr_documento  = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $servico            = new Zend_Form_Element_Hidden('SERVICO');
        $status_solicitacao = new Zend_Form_Element_Hidden('STATUS_SOLICITACAO');
        $somente_principal  = new Zend_Form_Element_Hidden('SOMENTE_PRINCIPAL');

        $this->addElements(array(   $docm_nr_documento,
                                    $status_solicitacao,
                                    $servico,
                                    $somente_principal
                                ));
    }
    
    private function _getBaseUrl()
    {
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        
        if (!$baseUrl) 
        {
          $baseUrl = rtrim(preg_replace( '/([^\/]*)$/', '', $_SERVER['PHP_SELF'] ), '/\\');
        }
        
        return $baseUrl;
    }
    
    
}
?>