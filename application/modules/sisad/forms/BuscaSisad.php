<?php
class Sisad_Form_BuscaSisad extends Zend_Form 
{
    public function init()
    {
        $this->setAction($this->_getBaseUrl() .'/sisad/pesquisadcmto')
             ->setName('pesquisaSisad')   
             ->setMethod('post');
        
        $userNamespace = new Zend_Session_Namespace('userNs');
        
        $docm_nr_documento  = new Zend_Form_Element_Hidden('DOCM_NR_DOCUMENTO');
        $servico            = new Zend_Form_Element_Hidden('SERVICO');
        $status_solicitacao = new Zend_Form_Element_Hidden('STATUS_SOLICITACAO');

        $this->addElements(array(   $docm_nr_documento,
                                    $status_solicitacao,
                                    $servico
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