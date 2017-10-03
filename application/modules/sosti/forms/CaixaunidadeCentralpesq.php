<?php
class Sosti_Form_CaixaunidadeCentralpesq extends Zend_Form {
	var $caixaUnidades = array (//"CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): TRIBUNAL REGIONAL FEDERAL DA PRIMEIRA REGIÃO - 2 - TR" => "1",
	"CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DO AMAZONAS - 4 - AM" => "6", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DO DISTRITO FEDERAL - 7 - DF" => "8", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DE GOIAS - 8 - GO" => "9", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DE MATO GROSSO - 11 - MT" => "12", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DO ACRE - 3 - AC" => "5", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DA BAHIA - 6 - BA" => "7", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DO MARANHÃO - 9 - MA" => "10", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DE MINAS GERAIS - 10 - MG" => "11", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DO AMAPÁ - 5 - AP" => "18", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DO ESTADO DO PARÁ - 12 - PA" => "13", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DO PIAUI - 13 - PI" => "14", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SEÇÃO JUDICIÁRIA DO ESTADO DE RONDÔNIA - 14 - RO" => "15", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DE RORAIMA - 15 - RR" => "16", "CAIXA DE ATENDIMENTO AO USUÁRIO DO(A): SECAO JUDICIARIA DE TOCANTINS - 16 - TO" => "17" );
	public function init() {
		
		$this->setAction ( '' )->setMethod ( 'post' );
		
		$caixaunidades = new Zend_Form_Element_Multiselect ( 'MODE_ID_CAIXA_ENTRADA' );
					$caixaunidades->setRequired ( false );
					$caixaunidades->setLabel ( 'Selecione Caixa de unidade:' );
					$caixaunidades->addMultiOptions ( array_flip ( $this->caixaUnidades ) );
					$caixaunidades->setOptions ( array ('style' => 'height: 250px' ) );
					$caixaunidades->setDescription('Este é um campo de multiseleção. Pressione a tecla CTRL para selecionar vários itens. ');
		
		$snat_cd_nivel = new Zend_Form_Element_Select ( 'SNAT_CD_NIVEL' );
					
					$snat_cd_nivel->setLabel ( 'Nivel de Atendimento:' );
		
		$docm_cd_matricula_cadastro = new Zend_Form_Element_Text ( 'DOCM_CD_MATRICULA_CADASTRO' );
			$docm_cd_matricula_cadastro->setValue ( '' )->setLabel ( 'Solicitante: ' )->setAttrib ( 'style', 'text-transform: uppercase; width: 540px;' );
		
		$ssol_cd_matricula_atendente = new Zend_Form_Element_Text ( 'SSOL_CD_MATRICULA_ATENDENTE' );
			$ssol_cd_matricula_atendente->setValue ( '' )->setLabel ( 'Atendente: ' )->setAttrib ( 'style', 'text-transform: uppercase; width: 540px;' );
		
		$docm_cd_lotacao_geradora = new Zend_Form_Element_Text ( 'DOCM_CD_LOTACAO_GERADORA' );
			$docm_cd_lotacao_geradora->setValue ( '' )->setLabel ( 'Unidade Solicitante:' )->setAttrib ( 'style', 'text-transform: uppercase; width: 540px;' );
		
		$mofa_id_fase = new Zend_Form_Element_Select ( 'MOFA_ID_FASE' );
			$mofa_id_fase->setValue ( '' )->setRequired ( false )->setLabel ( 'Última Fase:' );
		
		
		$rh_central = new Application_Model_DbTable_RhCentralLotacao ();
		$secao = $rh_central->getSecoestrf1 ();
		$getLotacao = $rh_central->getLotacao ();
		
		$trf1_secao = new Zend_Form_Element_Select ( 'TRF1_SECAO' );
		$trf1_secao->addMultiOptions ( array ('' => '' ) );
		$trf1_secao->setLabel ( 'Filtrar Região: TRF1 ou Seção' );
		$trf1_secao->setRequired ( false );
		$trf1_secao->setAttrib ( 'style', 'width: 500px; ' ) ;
		foreach ( $secao as $v ) {
			if($v ["LOTA_COD_LOTACAO"] != 2 &&$v ["LOTA_TIPO_LOTACAO"] != 9)
				$trf1_secao->addMultiOptions ( array ($v ["SESB_SIGLA_SECAO_SUBSECAO"] . '|' . $v ["LOTA_COD_LOTACAO"] . '|' . $v ["LOTA_TIPO_LOTACAO"] => $v ["LOTA_DSC_LOTACAO"] ) );
		}
		$trf1_secao->setValue('::SELEIONE::');
		
		$sgrs_id_grupo = new Zend_Form_Element_Select ( 'SGRS_ID_GRUPO' );
			$sgrs_id_grupo->setRequired ( false );
			
			$sgrs_id_grupo->setAttrib ( 'style', 'width: 350px; ' );
			$sgrs_id_grupo->setLabel ( 'Grupo de Serviço:' );
			$sgrs_id_grupo->setAttrib ( 'style', 'width: 650px;' );
			$sgrs_id_grupo->addMultiOptions ( array ('' => '' ) );
			
			$sser_id_servico =  new Zend_Form_Element_Multiselect ( 'SSER_ID_SERVICO' );
				$sser_id_servico->setRequired ( false );
				$sser_id_servico->setLabel ( ' Serviço:' );
				$sser_id_servico->setAttrib ( 'style', 'width: 450px;height:150px ' );
		
		$SadTbFadmFaseAdm = new Application_Model_DbTable_SadTbFadmFaseAdm ();
		$FadmFaseAdm = $SadTbFadmFaseAdm->fetchAll ( "FADM_NM_SISTEMA = 'SOSTI'", "FADM_DS_FASE" );
		
		$mofa_id_fase->addMultiOptions ( array ('' => '' ) );
		foreach ( $FadmFaseAdm as $FaseAdm ) :
			$mofa_id_fase->addMultiOptions ( array ($FaseAdm ['FADM_ID_FASE'] => /*$FaseAdm['FADM_ID_FASE'].' - '. */ $FaseAdm ["FADM_DS_FASE"] ) );
		endforeach
		;
		
                                   $somente_principal = new Zend_Form_Element_Checkbox('SOMENTE_PRINCIPAL');
                                   $somente_principal
                                                    ->setRequired(false)
                                                    ->setLabel('Mostrar Solicitações Vinculadas (Filhas)')
                                                    ->setCheckedValue('S')
                                                    ->setUncheckedValue('N')
                                                    ;
                                   
		$data_inicial_cadastro = new Zend_Form_Element_Text ( 'DATA_INICIAL_CADASTRO' );
		$data_inicial_cadastro->setValue ( '' )->setLabel ( 'Data inicial - Cadastro:' );
		
		$data_final_cadastro = new Zend_Form_Element_Text ( 'DATA_FINAL_CADASTRO' );
		$data_final_cadastro->setValue ( '' )->setLabel ( 'Data final - Cadastro:' );
		
		$docm_nr_documento = new Zend_Form_Element_Text ( 'DOCM_NR_DOCUMENTO' );
		$docm_nr_documento->setValue ( '' )->setLabel ( 'Nº da Solicitação:' )->setAttrib ( 'style', 'width: 540px;' )->addFilter ( 'StripTags' )->addFilter ( 'StringTrim' )->addValidator ( 'Alnum' );
		$submit = new Zend_Form_Element_Submit ( 'Filtrar' );
		
		$data_inicial_fase = new Zend_Form_Element_Text ( 'DATA_INICIAL' );
		$data_inicial_fase->setValue ( '' )->setLabel ( 'Data inicial - Última Fase:' );
		
		$data_final_fase = new Zend_Form_Element_Text ( 'DATA_FINAL' );
		$data_final_fase->setValue ( '' )->setLabel ( 'Data final - Última Fase:' );
                
                $cate_id_categoria = new Zend_Form_Element_MultiCheckbox('CATE_ID_CATEGORIA');
                $cate_id_categoria
                        ->setRequired(false)
                        ->setLabel('Categorias:');
                
        $docm_cd_matricula_cadastro_value = new Zend_Form_Element_Hidden('DOCM_CD_MATRICULA_CADASTRO_VALUE');
        $docm_cd_matricula_cadastro_value->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');      
        
        $ssol_cd_matricula_atendente_value = new Zend_Form_Element_Hidden('SSOL_CD_MATRICULA_ATENDENTE_VALUE');
        $ssol_cd_matricula_atendente_value->setValue('')
                            ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $docm_cd_lotacao_geradora_value = new Zend_Form_Element_Hidden('DOCM_CD_LOTACAO_GERADORA_VALUE');
        $docm_cd_lotacao_geradora_value->setValue('')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
		
		$this->addElements ( array ($docm_nr_documento, $snat_cd_nivel, $docm_cd_matricula_cadastro, $ssol_cd_matricula_atendente, $mofa_id_fase, $docm_cd_lotacao_geradora, $trf1_secao, $sgrs_id_grupo, $sser_id_servico,$cate_id_categoria, $caixaunidades, $somente_principal,$data_inicial_cadastro, $data_final_cadastro, $data_inicial_fase, $data_final_fase, $docm_cd_matricula_cadastro_value,$ssol_cd_matricula_atendente_value,$docm_cd_lotacao_geradora_value,$submit ) );
	}

}