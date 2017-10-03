<?php

class App_Utilidades_Arquivo {

    public static function getHexadecimal($caminho, $binarioArquivo = null) {
        if (is_null($binarioArquivo)) {
            $handle = fopen($caminho, "r");
            $binarioArquivo = fread($handle, filesize($caminho));
            fclose($handle);
        }
        return bin2hex($binarioArquivo);
    }

    public static function converteHexadecimalParaBinario($hexadecimal) {
        return pack("H*", $hexadecimal);
    }

    public static function gravaHexadecimalNaTemp($hexadecimal, $nomeComExtensao) {
        $binario = self::converteHexadecimalParaBinario($hexadecimal);
        $caminho = substr(APPLICATION_PATH, 0, strpos(APPLICATION_PATH, 'application')) . 'temp' . DIRECTORY_SEPARATOR . $nomeComExtensao;
        //grava o arquivo
        $handle = fopen($caminho, "w");
        fwrite($handle, $binario);
        fclose($handle);
        return $caminho;
    }

    public static function gravaBinarioNaTemp($binario, $nomeComExtensao) {
        $caminho = substr(APPLICATION_PATH, 0, strpos(APPLICATION_PATH, 'application')) . 'temp' . DIRECTORY_SEPARATOR . $nomeComExtensao;
        //grava o arquivo
        $handle = fopen($caminho, "w");
        fwrite($handle, $binario);
        fclose($handle);
        return $caminho;
    }

    /**
     * 
     * @param array $arquivos (array(array('nome'=>'string','caminho'=>'string'))
     * @param type $nomeZip
     * @return boolean
     * @throws Exception
     */
    public static function compactarComZip(array $arquivos, $nomeZip) {
        $zip = new ZipArchive();
        if ($zip->open($nomeZip, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('Não foi possível criar o arquivo zip ' . $nomeZip);
        }

        foreach ($arquivos as $arquivo) {
            $zip->addFile(realpath($arquivo['caminho']), $arquivo['nome']);
        }
        return $zip->close();
    }

}
