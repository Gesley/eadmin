<?php

class App_Util extends Application_Model_DbTable_Dual {
	
	/**
	 * 
	 * Retira todos os caracteres não numéricos
	 * @param string $matricula
	 * 
	 */
	public function stripNonNumeric($matricula){
		
		return (int)preg_replace('/\D/', '', $matricula);
	}
	/**
	 * Retorna o primeiro dia dos mês anterior
	 * 
	 */ 
   public function getprimeirodiadmesPassado(){
   	$m = date('n');
   	return $lastmonth_start = date('d/m/Y',mktime(1,1,1,$m-1,1,date('Y')))." 00:00:00"; 
   }
   /**
    * Retorna a data atual com  o horario as 11:59:59PM
    * 
    */
   public function getcurrentdate(){
   	
   	return date('d/m/Y'). " 23:59:59";
   }

    /**
     * retorna timestamp from DUAL
     * 
     */
    public static function getTimeStamp_Audit() {

        return parent::sysdate();
}
	
    public static function getIpMaquina_Audit() {

        return substr($_SERVER['REMOTE_ADDR'], 0, 50);
    }

    /**
     * Método ara retornar o sistema operacional do Cliente(usuario)
     * 
     */
    public static function getUsuarioSistemaOperacional_Audit() {

        return substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
    }

    public static function getMatricula_Audit() {

        $userNs = new Zend_Session_Namespace('userNs');
        return $userNs->matricula;
    }

    /**
     * Método para insirir uma movimentação na tabela de movimentação SadTbMofaMoviFase
     * @params Array
     */
    public static function addMovimentacao($params) {
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase ();
        try {
            $SadTbMofaMoviFase->createRow($params)->save();
        } catch (Exception $e) {
            throw new Exception('[' . __CLASS__ . ']  Não inserir na tabela de movimentação.Erro: ' . $e->getMessage());
        }
    }

    public static function toEn($date)
    {
        $date = explode(' ', $date);
        $time = (isset($date[1])) ? " $date[1]" : NULL;
        $date = $date[0];

        $date = explode('/', $date);
        $date = array($date[2], $date[1], $date[0]);
        return implode('-', $date) . $time;
    }

    public static function stringDateToDate($strDate)
    {
        $str = $strDate;
        $dateyear = str_split($str, 4);
        $ano = $dateyear[0];

        $datemounth = str_split($dateyear[1], 2);
        $mes = $datemounth[0];
        $dia = $datemounth[1];

        $data = $dia."-".$mes."-".$ano;

        //$data = date ('d-M-Y', strtotime($data));

        return $data;
    }

    public static function stringHourToDate($strHour)
    {
        $str = $strHour;
        $hora = str_split($str, 2);

        return $hora[0].":".$hora[1].":00";
    }

    /* Formato moeda oracle */
    function moeda($valor)
    {
       $valor =  number_format($valor/100,2,",","");
       str_replace(',','%',$valor);
       //str_replace('.',',',$valor);
       return str_replace('%','.',$valor);
    }

    /**
     * Formata a moeda para o banco de dados oracle
     * @param float $valor
     * @return float
     */
    public static function formatamodedaorcamento($valor)
    {
        $qtd = strlen(trim($valor));



        $valorincial = substr($valor, 0, $qtd -2);
        $valorfinal = substr($valor, strlen($valorincial), 2 );

        return $valorincial.".".$valorfinal;

//        return preg_replace("@0+@","",$valorincial.".".$valorfinal);
    }

}

?>
