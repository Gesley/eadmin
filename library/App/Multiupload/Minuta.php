<?php
/**
 * Description of Anexo
 *
 * @author Pedro Henrique dos Santos Correia
 */


class App_Multiupload_Minuta {
    
    public $_parametros_red;
    public $_metadados_red;
    
    public function __construct($data)
    {
        $this->_parametros_red = new App_Minuta_Parametros_Incluir();
        $this->_metadados_red = new App_Minuta_Metadados_Incluir($data);        
    }
    
    public function incluirarquivos(Zend_File_Transfer_Adapter_Http $element_file)
    {
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
        $anexos = $element_file->getFileInfo();

            if(isset($anexos["DOCM_DS_HASH_RED"])){
                unset ($anexos["DOCM_DS_HASH_RED"]);
            }
        $cont = 0;
        foreach ($anexos as $value) {
            $userfile = $value['name'];/*caminho completo do arquivo gravado no servidor*/
            $userfilecomp = $value['tmp_name'];
            $tempDirectory = "temp";
            $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SOSTITEMPDOC' . date("dmYHisu") . $userfile;
            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
            $filterFileRename->filter($userfilecomp);/*Renomeando a partir do caminho completo do arquivo no servidor*/
            
            if (defined('APPLICATION_ENV')) {
                if (APPLICATION_ENV == 'development') {
                  $red = new Services_Red_Minuta_Incluir(true); /*DESENVOLVIMENTO*/                    
                } else if (APPLICATION_ENV == 'production') {
                  $red = new Services_Red_Minuta_Incluir(false); /*PRODUÇÃO*/                    
                }
            }

            $red->debug = false;
            $red->temp = APPLICATION_PATH . '/../temp';

            $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $fullFilePath);
            
            $extensao = explode('.',$userfile);
            $ext = $mapperDocumento->retornaCodExtensao($extensao[1]);
            
            if(is_array($retornoIncluir_red)){
                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red['numeroDocumento'];
                $nrDocRed["incluidos"][$cont]["NOME"] = $userfile;
                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
            } else {
                if(strlen($retornoIncluir_red) == 39){
                    $nrDocRed["erro"] = "Servidor de arquivos fora do ar, não é possivel inserir anexo(s). O procedimento poderá ser efetuado sem anexo!<br />erro: $retornoIncluir_red";
                }else{
                    /*tratamento de erro*/
                    $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                    $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                    $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                    $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                    switch ($retornoIncluir_red_array["codigo"]) {
                        case 'Erro: 80':
                            $dadosDocumento = $tabelaSadTbDocmDocumento->fetchAll("DOCM_NR_DOCUMENTO_RED = $retornoIncluir_red_array[idDocumento]")->toArray();
                            if(isset ($dadosDocumento[0])){
                                $nrDocRed["existentes"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["existentes"][$cont]["NOME"] = $userfile;
                                $nrDocRed["existentes"][$cont]["NR_DOCUMENTO"] = $dadosDocumento[0]["DOCM_NR_DOCUMENTO"];
                                $nrDocRed["existentes"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
                            }else{
                                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["incluidos"][$cont]["NOME"] = $userfile;
                                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
                            }
                            break;
                        default:
                            return null;
                    }
                }
           }
           $cont++;
        }
        return $nrDocRed;
    }
    
    
    public function incluirarquivoHtml($textoHtml)
    {
            $cont = 0;
            $nomearquivo = uniqid();
            $caminho = realpath(APPLICATION_PATH . '/../temp');
            $caminho .= DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") .$nomearquivo.".html" ;
            $fopen = fopen($caminho, "w");
            $fwrite = fwrite($fopen, $textoHtml);
            $fclose = fclose($fopen);

            if (defined('APPLICATION_ENV')) {
                if (APPLICATION_ENV == 'development') {
                  $red = new Services_Red_Minuta_Incluir(true); /*DESENVOLVIMENTO*/                    
                } else if (APPLICATION_ENV == 'production') {
                  $red = new Services_Red_Minuta_Incluir(false); /*PRODUÇÃO*/                    
                }
            }

            $red->debug = false;
            $red->temp = APPLICATION_PATH . '/../temp';

            try {             
              $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $caminho);
             } catch (Exception $exc) {
              Zend_Debug::dump($exc,'exceptionssssssss'); //exit;
            }
            if(is_array($retornoIncluir_red)){
                //$nrDocRed["incluidos"][$cont] = $retornoIncluir_red['numeroDocumento'];
                //$nrDocRed["extensao"][$cont] = 4;
                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red['numeroDocumento'];
                $nrDocRed["incluidos"][$cont]["NOME"] = "Arquivo HTML";
                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = 4;
                
            } else {
                if(strlen($retornoIncluir_red) == 39){
                    $nrDocRed["erro"] = "Servidor de arquivos fora do ar, não é possivel inserir anexo(s). O procedimento poderá ser efetuado sem anexo!<br />erro: $retornoIncluir_red";
                }else{
                    /*tratamento de erro*/
                    $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                    $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                    $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                    $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                    switch ($retornoIncluir_red_array["codigo"]) {
                        case 'Erro: 80':
                            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                            $dadosDocumento = $tabelaSadTbDocmDocumento->fetchAll("DOCM_NR_DOCUMENTO_RED = $retornoIncluir_red_array[idDocumento]")->toArray();
                            if(isset ($dadosDocumento[0])){
                                $nrDocRed["existentes"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["existentes"][$cont]["NOME"] = $caminho;
                                $nrDocRed["existentes"][$cont]["NR_DOCUMENTO"] = $dadosDocumento[0]["DOCM_NR_DOCUMENTO"];
                            }else{
                                //$nrDocRed["incluidos"][$cont] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["incluidos"][$cont]["NOME"] = "Arquivo HTML";
                                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = 4;
                            }
                            break;
                        default:
                            return null;
                    }
                }
           }
        return $nrDocRed;
    }
}

?>