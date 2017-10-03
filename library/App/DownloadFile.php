<?php

/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

/**
* Description of DownloadFile
*
* @author tr17286ps
*/
class App_DownloadFile 
{
    protected $fileTypes = array(
        'swf'  => 'application/x-shockwave-flash',
        'pdf'  => 'application/pdf',
        'exe'  => 'application/octet-stream',
        'zip'  => 'application/zip',
        'doc'  => 'application/msword',
        'docx' => 'application/msword',
        'rtf' => 'application/msword',
        'odt' => 'application/swriter',
        'html' => 'text/html',
        'htm'  => 'text/html',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpeg' => 'image/jpg',
        'jpg'  => 'image/jpg',
        'rar'  => 'application/rar',
        'ra'   => 'audio/x-pn-realaudio',
        'ram'  => 'audio/x-pn-realaudio',
        'ogg'  => 'audio/x-pn-realaudio',
        'wav'  => 'video/x-msvideo',
        'wmv'  => 'video/x-msvideo',
        'avi'  => 'video/x-msvideo',
        'asf'  => 'video/x-msvideo',
        'divx' => 'video/x-msvideo',
        'mp3'  => 'audio/mpeg',
        'mp4'  => 'audio/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg'  => 'video/mpeg',
        'mpe'  => 'video/mpeg',
        'mov'  => 'video/quicktime',
        'swf'  => 'video/quicktime',
        '3gp'  => 'video/quicktime',
        'm4a'  => 'video/quicktime',
        'aac'  => 'video/quicktime',
        'm3u'  => 'video/quicktime',
        'tif'  => 'image/tiff',
        'tiff' => 'image/tiff',
    );
    
    protected $extensionsToStream = array(
        'mp3', 'm3u', 'm4a', 'mid', 'ogg', 'ra', 'ram', 
        'wm', 'wav', 'wma', 'aac', '3gp', 'avi', 'mov', 
        'mp4', 'mpeg', 'mpg', 'swf', 'wmv', 'divx', 'asf'
    );
    private $_response = null;
    private $_headers = array();
    
    public function __construct() 
    {
        $this->_response = Zend_Controller_Front::getInstance()->getResponse();
        $layout = Zend_Layout::getMvcInstance();
        $view   = $layout->getView();
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
                $viewRenderer->setView($view);
        
        $layout->disableLayout();
        $viewRenderer->setNoRender();
    }
    
    public function isOpenInBrowser($fileName)
    {
        $tipos = array('pdf','html','htm');
        $extension = strtolower(end(explode('.', $fileName)));
        if(in_array($extension, $tipos)){
            return true;
        }
        return false;
    }
    
    public function open($fileLocation, $fileName, $size)
    {
        $extension = strtolower(end(explode('.', $fileName)));
        if(array_key_exists($extension, $this->fileTypes)){
            $contentType = $this->fileTypes[$extension];
        }
        $this->_headers = array(
            "Pragma: "       => "public",
            "Cache-Control:" => "public",
            "Content-Type: " => $contentType,
        );
        //$this->_headers["Content-Disposition:"] = "attachment;filename=\"$fileName\"";
        $this->_setResponse();
        
        //echo '<pre>';
        $conteudo = file_get_contents($fileLocation);
        //echo $conteudo;
        $this->_sendResponse($conteudo);
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
    public function download($fileLocation, $fileName, $size, $maxSpeed = 100, $doStream = false) {
        //echo file_get_contents($fileLocation);
        if (connection_status() != 0)
            return(false);
        $extension = strtolower(end(explode('.', $fileName)));
        //Zend_Debug::dump($extension);exit;

        /* ALTERADO POR MAURICIO EM 20/07/2011 - ESTAVA DANDO ERRO DE TIPO DE ARQUIVO */
        $contentType = 'application/force-download';
        if(array_key_exists($extension, $this->fileTypes)){
            $contentType = $this->fileTypes[$extension];
        }
        
        $this->_headers = array(
            "Cache-Control:" => "public",
            "Content-Transfer-Encoding: " => "binary",
            "Content-Type: " => $contentType,
        );

        $contentDisposition = 'attachment';
        if ($doStream == true && in_array($extension, $this->extensionsToStream)) {
            $contentDisposition = 'inline';
        }

        if (true == strstr($_SERVER['HTTP_USER_AGENT'], "MSIE") ) {
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

        //Zend_Debug::dump($this->_headers);exit;
        $this->_setResponse();
        
        //echo '<pre>';
        $conteudo = file_get_contents($fileLocation);
        //echo $conteudo;
        $this->_sendResponse($conteudo);
        /*
        $fp = fopen("$fileLocation", "rb");
        fseek($fp, $range);

        while (!feof($fp) and (connection_status() == 0)) {
            set_time_limit(0);
            print(fread($fp, 1024 * $maxSpeed));
            flush();
            ob_flush();
            //usleep(1) ;
            //sleep(1);
        }
        fclose($fp);

         */
        return((connection_status() == 0) and !connection_aborted());
    }
    
    private function _sendResponse($body)
    {
        $this->_response
                 ->setBody($body);
        
        Zend_Controller_Front::getInstance()->setResponse($this->_response);
        return true;
    }
    
    private function _setResponse()
    {
     
        foreach ($this->_headers as $i=>$value) {
            if (is_int($i)){
                header($value);
            }
            header($i.$value);
           
        }
       /*
       $this->_response->clearAllHeaders();
       $this->_response->clearBody();
        foreach ($this->_headers as $i=>$value) {
            if (is_int($i)){
                $this->_response
                        ->setHeader($value);
                continue;
            }
            $this->_response
                    ->setHeader($i, $value, true);
        }
        */
       return true;
    }
}

?>
