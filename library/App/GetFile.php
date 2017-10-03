<?php

/**
 * @category            App
 * @package		App_GetFile
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author              Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * 
 * Copiada da DownloadFile. Foram realizadas algumas modificações na classe.
 */
class App_GetFile {

    protected $fileTypes = array(
        'pdf' => 'application/pdf',
    );
    protected $extensionsToStream = array(
        'pdf'
    );
    private $_response = null;
    private $_headers = array();

    public function __construct() {
        $this->_response = Zend_Controller_Front::getInstance()->getResponse();
        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);

        $layout->disableLayout();
        $viewRenderer->setNoRender();
    }

    /**
     *
     * @param string $fileLocation
     * @param string $fileName
     * @param int $size
     * @param int $maxSpeed
     * @param boolean $doStream
     * @return type
     * @throws Exception 
     */
    public function onBrowser($fileLocation, $fileName, $size) {
        //echo file_get_contents($fileLocation);
        if (connection_status() != 0)
            return(false);
        $extension = strtolower(end(explode('.', $fileName)));
        //Zend_Debug::dump($extension);exit;

        /* ALTERADO POR MAURICIO EM 20/07/2011 - ESTAVA DANDO ERRO DE TIPO DE ARQUIVO */
        $contentType = 'application/force-download';
        if (array_key_exists($extension, $this->fileTypes)) {
            $contentType = $this->fileTypes[$extension];
        }

        $this->_headers = array(
            "Cache-Control:" => "public",
            "Content-Transfer-Encoding: " => "binary",
            "Content-Type: " => $contentType,
        );

        $contentDisposition = 'inline';

        if (true == strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $fileName = preg_replace('/\./', '%2e', $fileName, substr_count($fileName, '.') - 1);
            $this->_headers["Content-Disposition:"] = "$contentDisposition;filename=\"$fileName\"";
        } else {
            $this->_headers["Content-Disposition:"] = "$contentDisposition;filename=\"$fileName\"";
        }

        $this->_headers["Pragma:"] = "anytextexeptno-cache";
        $this->_headers["Accept-Ranges:"] = "bytes";
        $range = 0;

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            str_replace($range, "-", $range);
            $size2 = $size - 1;
            $new_length = $size - $range;
            $this->_headers[] = "HTTP/1.1 206 Partial Content";
            $this->_headers["Content-Length:"] = $new_length;
            $this->_headers["Content-Range:"] = "bytes $range$size2/$size";
        } else {
            $size2 = $size - 1;
            $this->_headers["Content-Range:"] = "bytes 0-$size2/$size";
            $this->_headers["Content-Length:"] = $size;
        }
        
        if ($size == 0) {
            throw new Exception('Arquivo com Zero Byte, download abordado');
        }
        ini_set('magic_quotes_runtime', 0);
        $this->_setResponse();
        $conteudo = file_get_contents($fileLocation);
        $this->_sendResponse($conteudo);
        
        return((connection_status() == 0) and !connection_aborted());
    }

    private function _sendResponse($body) {
        $this->_response
                ->setBody($body);

        Zend_Controller_Front::getInstance()->setResponse($this->_response);
        return true;
    }

    private function _setResponse() {

        foreach ($this->_headers as $i => $value) {
            if (is_int($i)) {
                header($value);
            }
            header($i . $value);
        }
        return true;
    }

}

?>
