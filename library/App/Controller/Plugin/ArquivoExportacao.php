<?php
/**
 * Data e hora atual do servidor onde está instalado o Apache.
 */
class App_Controller_Plugin_ArquivoExportacao extends Zend_Controller_Plugin_Abstract
{
    public static function pdf($nomeArquivo, $body)
    {
//        $view = new Zend_View();
//        $view->setScriptPath(realpath(APPLICATION_PATH.'/modules/sosti/views/scripts/relatoriossolicitacoes'));
//        $view->render('pdf.phtml');
        
////        $response = $layout->getResponse();
////        $body = $response->getBody();
////        Zend_Debug::dump($body);exit;
////        $response->clearBody();

       define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
       define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
       include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
       $mpdf=new mPDF('',    // mode - default ''
                      '',    // format - A4, for example, default ''
                       8,    // font size - default 0
                      '',    // default font family
                      10,    // margin_left
                      10,    // margin right
                      10,    // margin top
                      10,    // margin bottom
                       9,    // margin header
                       9,    // margin footer
                      'L');

       $mpdf->AddPage('P', '', '0', '1');
       $mpdf->WriteHTML($body);
       $name =  $nomeArquivo.'.pdf';
       $mpdf->Output($name,'D');
    }
    
    public static function excel($nomeArquivo)
    {
        $name = $nomeArquivo.'.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '"');
        header('Cache-Control: max-age=0');
    }
}