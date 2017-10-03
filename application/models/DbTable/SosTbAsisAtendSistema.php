<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbAsisAtendSistema extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_ASIS_ATEND_SISTEMA';
    protected $_primary = 'ASIS_ID_ATENDIMENTO_SISTEMA';
    protected $_sequence = 'SOS_SQ_ASIS';


    public function getIdAtendimentoSistema($ic_emergencia,$id_categoria_servico,$ic_nivel_criticidade)
    {
        
        if($id_categoria_servico == '2'){
            $ic_emergencia = 'S';
        }
        
        $id_categoria_servico = (int) $id_categoria_servico;
        switch ($id_categoria_servico) {
            case 1:
                $id_ocorrencia = 5;
                $ic_nivel_criticidade = 'X';
                break;
            case 2:
                if($ic_emergencia == 'S'){
                    $id_ocorrencia = 1;
                    $ic_nivel_criticidade = $ic_nivel_criticidade;
                }
                break;
            case 3:
                $id_ocorrencia = 3;
                $ic_nivel_criticidade = 1;
                break;
            case 4:
                if($ic_emergencia == 'S'){
                    $id_ocorrencia = 2;
                    $ic_nivel_criticidade = 1;
                }else{
                    $id_ocorrencia = 4;
                    $ic_nivel_criticidade = 1;
                }
                break;
            case 5:
                if($ic_emergencia == 'S'){
                    $id_ocorrencia = 2;
                    $ic_nivel_criticidade = 1;
                }else{
                    $id_ocorrencia = 4;
                    $ic_nivel_criticidade = 1;
                }
                break;
            case 6:
                if($ic_emergencia == 'S'){
                    $id_ocorrencia = 2;
                    $ic_nivel_criticidade = 1;
                }else{
                    $id_ocorrencia = 4;
                    $ic_nivel_criticidade = 1;
                }
                break;
            case 7:
                $id_ocorrencia = 6;
                $ic_nivel_criticidade = 'X';
                break;
            case 8:
                $id_ocorrencia = 7;
                $ic_nivel_criticidade = 'X';
                break;
            default:
                break;
        }
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT *
                            FROM SOS_TB_ASIS_ATEND_SISTEMA
                            WHERE ASIS_ID_OCORRENCIA = $id_ocorrencia
                            AND ASIS_ID_CATEGORIA_SERVICO = $id_categoria_servico
                            AND ASIS_IC_NIVEL_CRITICIDADE = '$ic_nivel_criticidade'");
        Zend_Debug::dump($stmt->fetch());
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT ASIS_ID_ATENDIMENTO_SISTEMA
                            FROM SOS_TB_ASIS_ATEND_SISTEMA
                            WHERE ASIS_ID_OCORRENCIA = $id_ocorrencia
                            AND ASIS_ID_CATEGORIA_SERVICO = $id_categoria_servico
                            AND ASIS_IC_NIVEL_CRITICIDADE = '$ic_nivel_criticidade'");
        return $stmt->fetch();
    }
	
	public function getTempoAtendimento($prazo, $tempoTranscorrido){
		
		
		$prazo = explode(' ', $prazo);
		//Dias Uteis.
		if($prazo[2] == '2'){
		$tempoTranscorrido = explode(' ', $tempoTranscorrido);
			/**
			* Transforma o tempo transcorrido para segundos.
			*/
			$segundosTranscorridos = $tempoTranscorrido[0] * 13; // Transforma em Horas Uteis
			$segundosTranscorridos = ($segundosTranscorridos + $tempoTranscorrido[1]) * 60 ; // Soma as Horas Uteis
			$segundosTranscorridos = ($segundosTranscorridos + $tempoTranscorrido[2]) * 60; // Soma os Minutos
			$segundosTranscorridos = $segundosTranscorridos + $tempoTranscorrido[3]; // Total de Seugndos

			/**
			* Pega o prazo e coloca no formato de Dias, Horas, Minutos e Segundos
			*/
			if($prazo[1] == 'HORAS'){
				$d = 0;
				$h = $prazo[0] - 1;
				$m = 60;
				$s = 60;
			}else if($prazo[1] == 'DIAS'){
				$d = $prazo[0] - 1;
				$h = 13 - 1;
				$m = 60;
				$s = 60;
			}

			/**
			* Transforma o Prazo em Segundos.
			*/
			$prazoRestante = $prazoRestante = $d * 13; // Transforma em Horas Uteis
			$prazoRestante = ($prazoRestante + $h) * 60 ; // Soma as Horas Uteis
			$prazoRestante = ($prazoRestante + $m) * 60; // Soma os Minutos
			$prazoRestante = $prazoRestante + $s; // Total de Seugndos
			$prazoRestante  = $prazoRestante - $segundosTranscorridos;

			/**
			* Recebe o resultado da diferente entre o tempo Restante e o Tempo Transcorrido em segundos, 
			* e transforma para HH:MM:SS
			*/
			$hours = floor($prazoRestante / 3600);
			$prazoRestante -= $hours * 3600;
			$minutes = floor($prazoRestante / 60);
			$prazoRestante -= $minutes * 60;
			
			if($hours < 10 && $hours >= 0){
				$hours = "0".$hours;
			}if($minutes < 10 && $minutes >= 0){
				$minutes = "0".$minutes;
			}if($prazoRestante < 10 && $prazoRestante >= 0){
				$prazoRestante = "0".$prazoRestante;
			}
			
			/**
			* Veificar se a hora esta negativa, caso esteja é porque o prazo já foi esgotado.
			*/
			if($hours < 0 || $d < 0){
				return 'ESGOTADO';
			}else{
				return "$hours:$minutes:$prazoRestante";  
			}
		}else{ // Dias Corridos
			$data_inicial = mktime(substr($tempoTranscorrido,11,2),
								   substr($tempoTranscorrido,14,2),
								   substr($tempoTranscorrido,17,2),
								   substr($tempoTranscorrido,3,2),
								   substr($tempoTranscorrido,0,2),
								   substr($tempoTranscorrido,6,4));
			$timestamp = $data_inicial;
			$data = NULL;
			$i = 0;
			return date('d/m/Y G:i:s' ,  strtotime("+$prazo[0] month",$timestamp)); 
		}
	}
	
	public function getServicoSistema($idMovimentacao){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  OSIS_NM_OCORRENCIA,
									CTSS_NM_CATEGORIA_SERVICO,
									CTSS_ID_CATEGORIA_SERVICO,
									ASSO_IC_ATENDIMENTO_EMERGENCIA,
									ASIS_IC_NIVEL_CRITICIDADE,
									(SELECT PRAT_QT_PRAZO||' '||UNME_DS_UNID_MEDIDA||'S'
									FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
									INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
									ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
									WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_INICIO_ATENDIMENTO) ASIS_PRZ_INICIO_ATENDIMENTO,

									CASE 
									WHEN ASSO_IC_SOLUCAO_CAUSA_PROBLEMA = 'S' AND ASSO_IC_ATENDIMENTO_EMERGENCIA = 'S' THEN
									(SELECT PRAT_QT_PRAZO||' '||UNME_DS_UNID_MEDIDA||'S'
									FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
									INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
									ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
									WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_PROBLEMA) 
									END ASIS_PRZ_SOL_PROBLEMA,

									CASE
									WHEN ASSO_IC_SOLUCAO_PROBLEMA = 'S' AND ASSO_IC_ATENDIMENTO_EMERGENCIA = 'S' THEN
									(SELECT PRAT_QT_PRAZO||' '||UNME_DS_UNID_MEDIDA||'S'
									FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
									INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
									ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
									WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_CAUSA_PROBLEMA) 
									END ASIS_PRZ_SOL_CAUSA_PROBLEMA,

									(SELECT PRAT_QT_PRAZO||' MESES'
									FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
									INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
									ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
									WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_EXECUCAO_SERVICO) ASIS_PRZ_EXECUCAO_SERVICO
							FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
							INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
							ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
							INNER JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA CTSS
							ON ASIS.ASIS_ID_CATEGORIA_SERVICO = CTSS.CTSS_ID_CATEGORIA_SERVICO
							INNER JOIN SOS_TB_OSIS_OCORRENCIA_SISTEMA OSIS
							ON OSIS.OSIS_ID_OCORRENCIA = ASIS.ASIS_ID_OCORRENCIA
							INNER JOIN SOS_TB_SESI_SERVICO_SISTEMA SESI
							ON CTSS.CTSS_ID_SERVICO_SISTEMA = SESI.SESI_ID_SERVICO_SISTEMA
							WHERE ASSO.ASSO_ID_MOVIMENTACAO = $idMovimentacao");
        return $stmt->fetch();
	}
}