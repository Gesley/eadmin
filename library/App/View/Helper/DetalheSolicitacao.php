<?php
class App_View_Helper_DetalheSolicitacao extends Zend_View_Helper_Abstract
{
    private static $html = '';
    
    public static function detalhe ($idCaixa, $idDocumento)
    {
       
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
//        $sosMaeqManutencao = new Application_Model_DbTable_SosTbMaeqManutencaoEqpto();
//        $i = 0;
//        foreach ($arraySolicitacao as $p) {
//                $data[$i] = $p;//Zend_Json::decode($p);

        /**
         * Verifica a categorização de Serviços e Prioridades dos 
         * Sostis da caixa de Desenvolvimento / Sustentação. 
         */
//                $idCaixa = $this->_getParam('idcaixa');

        if ($idCaixa != null) {
            $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($idDocumento, 2, true);
            //                    $this->view->idCaixa = $idCaixa;
        } else {
            $DocmDocumento = $SosTbSsolSolicitacao->getDadosSolicitacao($idDocumento, null, true);
        }

        $DocmDocumentoHistorico = $SosTbSsolSolicitacao->getHistoricoSolicitacao($idDocumento);

        $DocmDocumentoVinculacao = $SosTbSsolSolicitacao->getPrincipalVinculacao($idDocumento);

        //$DocmListaVinculados = $SosTbSsolSolicitacao->getListaSolicitacoesVinculadas($idDocumento);
        //                $this->view->DocmListaVinculados = $DocmListaVinculados;
        //$DocmNaoConformidades = $SosTbSsolSolicitacao->getNaoConformidades($idDocumento);
        //                $this->view->DocmNaoConformidades = $DocmNaoConformidades;


        /*         * ****************************passar o id da movimentação************* */
        //                $DocmContoleQaulidade = $SosTbSsolSolicitacao->getDefeitosSolicitacao($data[$i]["MOFA_ID_MOVIMENTACAO"]);
        //                $this->view->DocmDefeitos = $DocmContoleQaulidade;
//        $DocmManutencaoEquip = $sosMaeqManutencao->getDadosManutencaoSolicitacao($idDocumento);
        //                $this->view->DocmManutencaoEquip = $DocmManutencaoEquip;
        //                $this->render('detalhe');
        //                Zend_Debug::dump($DocmDocumentoHistorico); exit;

        /**
         * Dados do Documento
         */
        self::$html = '<tr>
                           <th  colspan="5" style="background-color: #CCCCCC;">
                               SOLICITAÇÃO DE SERVIÇO A TI Nº: '.$DocmDocumento["DOCM_NR_DOCUMENTO"].'
                           </th>
                       </tr>';
        self::$html .= '<tr>
                            <td  colspan="5" style="border:1px solid silver;">
                                <strong>DOCUMENTO</strong>
                                <br />
                                <br />
                                <strong>Solicitação Nº:</strong> '.$DocmDocumento["DOCM_NR_DOCUMENTO"].'
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <strong>Data da Solicitação:</strong> '.$DocmDocumento["DOCM_DH_CADASTRO"].'
                                <br />';
        if ($idCaixa == 2) {
            if ($DocmDocumento["ASSO_IC_ATENDIMENTO_EMERGENCIA"] == "S") {
                self::$html .= '<img src="'.$this->baseUrl().'/img/sosti/emergencial.png" title="Demanda de Carater Emergencial" /> 
                                DEMANDA EMERGÊNCIAL
                                <br />';
            }
            self::$html .= '<!-- <img src="$this->baseUrl()/img/sosti/$DocmDocumentoHistorico["CTSS_ID_CATEGORIA_SERVICO"].png" title="$DocmDocumentoHistorico["CTSS_NM_CATEGORIA_SERVICO"]" /> -->
                           '.$DocmDocumento["CTSS_NM_CATEGORIA_SERVICO"].'" - ";
                           '.$DocmDocumento["OSIS_NM_OCORRENCIA"].'
                           Nível de Criticidade: '.$DocmDocumento["ASIS_IC_NIVEL_CRITICIDADE"].'
                           <br />';
        }
//        self::$html  .= '<tr>
//                     <!-- <td title="Número do documento">
//                        <strong>Solicitação Nº:</strong>
//                        '.$DocmDocumentoHistorico["DOCM_NR_DOCUMENTO"].'
//                     </td>-->
//                     <td title="Data e hora de cadastro" colspan="2">
//                         <strong>Data da Solicitação:</strong>
//                         '.$DocmDocumentoHistorico["DOCM_DH_CADASTRO"].'
//                     </td>
//                 </tr>';
//        if($DocmDocumento["DOCM_NR_DCMTO_USUARIO"]) {
//            self::$html  .= '<tr>
//                         <td title="Número do documento">
//                             <strong>Nº Documento Usuário:</strong>
//                             '.$DocmDocumentoHistorico["DOCM_NR_DCMTO_USUARIO"].'
//                         </td>
//                     </tr>'; 
//        } 
//                        if($this->fuso != 0) { 
//                            self::$html .= '<td title="Data e hora de cadastro na Seção">
//                                               Data/hora na Seção:<br />';
//                            $dataHora = new Zend_Date($DocmDocumento["DOCM_DH_CADASTRO"]);
//                            self::$html .= '$dataHora->add($this->fuso, Zend_Date::HOUR);
//                                           </td>';
//                        }
        if (!$DocmDocumento["SSOL_NM_USUARIO_EXTERNO"]) {
            self::$html .= '<strong>Unidade Solicitante:</strong> '.$DocmDocumento["LOTA_SIGLA_LOTACAO"] . ' - ' . $DocmDocumento["LOTA_DSC_LOTACAO"] . ' - ' . $DocmDocumento["LOTA_COD_LOTACAO"] . ' - ' . $DocmDocumento["LOTA_SIGLA_SECAO"] . ' - ' . $DocmDocumento["FAMILIA"] . ' 
                            <br />
                            <strong>Nome do Solicitante:</strong> '.$DocmDocumento["NOME"] . '
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <strong>Matricula:</strong> '.$DocmDocumento["DOCM_CD_MATRICULA_CADASTRO"] . '
                            <br />
                            <strong>E-mail do Solicitante</strong> '.$DocmDocumento["SSOL_DS_EMAIL_EXTERNO"] . '
                            <br />';
        } else {
            self::$html .= '<strong>Unidade Cadastrante:</strong> '.$DocmDocumento["LOTA_SIGLA_LOTACAO"] . ' - ' . $DocmDocumento["LOTA_DSC_LOTACAO"] . '
                            <br />
                            <strong>Nome do Solicitante Externo:</strong> ' . $DocmDocumento["SSOL_NM_USUARIO_EXTERNO"] . '
                            <br />
                            <strong>E-mail do Solicitante:</strong> ' . $DocmDocumento["SSOL_DS_EMAIL_EXTERNO"].'
                            <br />';
        }
//                    self::$html .= '<tr>
//                        <th >Telefone:</th>
//                        <td colspan="3">'.$DocmDocumento["SSOL_NR_TELEFONE_EXTERNO"].'</td>
//                    </tr>
//                    <tr>
//                        <th >Local de Atendimento:</th>
//                        <td colspan="3">'.$DocmDocumento["SSOL_ED_LOCALIZACAO"].'</td>
//                    </tr>
//<!--                    <tr>
//                        <th>Unidade Solicitante:</th>
//                        <td colspan="3">'.$DocmDocumento["LOTA_SIGLA_LOTACAO_REDATORA"].' - '.$DocmDocumento["LOTA_DSC_LOTACAO_REDATORA"].'</td>
//                    </tr>-->
//                    <tr>
//                        <th >Serviço Atual:</th>
//                        <td colspan="3">'.$DocmDocumento["SSER_DS_SERVICO"].'</td>
//                    </tr>';
        if ($DocmDocumento["SSES_DT_INICIO_VIDEO"]) {
            self::$html .= '<strong>Data e hora do início da videoconferência: </strong>'.$DocmDocumento["SSES_DT_INICIO_VIDEO"].'
                            <br />';
        }
        if ($DocmDocumento["SSOL_NR_TOMBO"]) {
            self::$html .= '<strong>Nº do Tombo:</strong> '.$DocmDocumento["SSOL_NR_TOMBO"].'
                            <br />';
        }
        if (strcmp($DocmDocumentoVinculacao["DOCM_NR_DOCUMENTO"], $DocmDocumento["DOCM_NR_DOCUMENTO"]) && $DocmDocumentoVinculacao["DOCM_NR_DOCUMENTO"] != NULL) {
            self::$html .= '<strong>Solicitação Vinculada:</strong>
                            <br />
                            ESTA SOLICITAÇÃO ESTÁ VINCULADA A UMA PRINCIPAL: '.$DocmDocumentoVinculacao["DOCM_NR_DOCUMENTO"].'
                            <br />';
        } elseif (!(strcmp($DocmDocumentoVinculacao["DOCM_NR_DOCUMENTO"], $DocmDocumento["DOCM_NR_DOCUMENTO"])) && $DocmDocumentoVinculacao["DOCM_NR_DOCUMENTO"] != NULL) {
            self::$html .= '<strong>Solicitação Vinculada:</strong>
                            <br />
                            ESTA SOLICITAÇÃO É A PRINCIPAL DE UMA VINCULAÇÃO
                            <br />';
        }
        self::$html .= '<strong>Descrição:</strong> '.$DocmDocumento["DOCM_DS_ASSUNTO_DOC"].
                       '<br />';
        if ($DocmDocumento["SSOL_DS_OBSERVACAO"] != "") {
            self::$html .= '<strong>Observação:</strong> '.$DocmDocumento["SSOL_DS_OBSERVACAO"];
        }
        if (strlen($DocmDocumento["ATENDENTE"]) > 3) {
            self::$html .= '<strong>Encaminhado para:</strong> '.$DocmDocumento["ATENDENTE"].'
                            <br />';
        }
//        self::$html .= '<tr>
//            <th >
//                <a title="Gerar um pdf da solicitação" href=" ' . $this->baseUrl() . '/sosti/solicitacao/solicitacaopdf/solic/' . $DocmDocumento["SSOL_ID_DOCUMENTO"] . ' ">Guia para Atendimento Presencial</a>&emsp;
//            </th>
//        </tr>
//        </table>
//        </div>';
        self::$html .= '</td>
                        </tr>';
        /**
         * Dados do Histórico
         */
        foreach ($DocmDocumentoHistorico as $historico):
            //				$dadosSistema = $SosAssisAtendSistema->getServicoSistema($historico["MOVI_ID_MOVIMENTACAO"]);

            self::$html .= '<tr>
                                <td  colspan="5" style="border:1px solid silver;">
                                <strong>'.str_replace(' SISAD', '', $historico['FADM_DS_FASE']).'</strong>
                                <br />
                                <br />';
//            self::$html .= '<strong>Fase:</strong>' . str_replace(' SISAD', '', $historico['FADM_DS_FASE']);
//            $dataHoraHistorico = new Zend_Date($historico['MOFA_DH_FASE']);
//            self::$html .= $historico['MOFA_DH_FASE'].'<br />  <strong>Data/hora da fase na Seção: </strong>'.(($this->fuso != 0)?($dataHoraHistorico->add($this->fuso, Zend_Date::HOUR)):(''));

            $timeInterval = new App_TimeInterval();
            $tempoDesdeCad = $timeInterval->tempoTotal($DocmDocumento['DOCM_DH_CADASTRO'], $historico['MOFA_DH_FASE'], 'dd/MM/yyyyHH:mm:ss');
            self::$html .= '<strong>Tempo do cadastro até a fase atual:</strong> ' .$tempoDesdeCad;

            if ($historico["FADM_ID_FASE"] == 1006 || $historico["FADM_ID_FASE"] == 1001 || $historico["FADM_ID_FASE"] == 1022 || $historico["FADM_ID_FASE"] == 1029 || $historico["FADM_ID_FASE"] == 1050) {
                self::$html .= '<br />
                                <strong>Caixa destino: </strong>'.$historico["CXEN_DS_CAIXA_ENTRADA"] . ' 
                                <br />
                                <strong>Serviço: </strong>'.$historico["SSER_DS_SERVICO"];
                if (!is_null($historico["SSES_DT_INICIO_VIDEO"])) {
                    self::$html .= '<br />
                                    <strong>Data e hora do início da videoconferência: </strong>
                                   '.$historico["SSES_DT_INICIO_VIDEO"];

                    self::$html .= '<br />
                                    <strong>Videoconferência realizada por este grupo: </strong>
                                    '.$historico["SSES_IC_VIDEO_REALIZADA"];
                }
            }
            if (
                    ( $historico["FADM_ID_FASE"] == 1005 && !(is_null($historico["SNAT_CD_NIVEL"])) )
                    ||
                    ( $historico["FADM_ID_FASE"] == 1006 && !(is_null($historico["SNAT_CD_NIVEL"])) )
                    ||
                    ( $historico["FADM_ID_FASE"] == 1022 && !(is_null($historico["SNAT_CD_NIVEL"])) )
                    ||
                    ( $historico["FADM_ID_FASE"] == 1029 && !(is_null($historico["SNAT_CD_NIVEL"])) )
                    ||
                    ( $historico["FADM_ID_FASE"] == 1001 && !(is_null($historico["SNAT_CD_NIVEL"])) )
            ) {
                self::$html .= '<br />
                                <strong>Nível destino: </strong>' . $historico["SNAT_CD_NIVEL"] . ' - ' . $historico["SNAT_DS_NIVEL"];
            }

            if ($historico["FADM_ID_FASE"] == 1014 || $historico["FADM_ID_FASE"] == 1019) {
                self::$html .= '<br />
                                <strong>Avaliação: </strong>' . $historico["STSA_DS_TIPO_SAT"];
            }

            if ($historico["FADM_ID_FASE"] == 1038) {
                self::$html .= '<br/>
                                <strong>Prazo: </strong>' . $historico["SSPA_DT_PRAZO"] . ' 
                                <br/>
                                <strong>Aprovação: </strong>';
                if ($historico["SSPA_IC_CONFIRMACAO"] == 'S') {
                    self::$html .= 'aprovado';
                } else if ($historico["SSPA_IC_CONFIRMACAO"] == 'N') {
                    self::$html .= 'reprovado';
                }
            }

            if ($historico["FADM_ID_FASE"] == 1008) {
                self::$html .= '<br />
                                <strong>Trocado para o Serviço: </strong>' . $historico["SSER_DS_SERVICO"];
                if (!is_null($historico["SSES_DT_INICIO_VIDEO"])) {
                    self::$html .= '<br />
                                    <strong>Data e hora do início da videoconferência: </strong>
                                    ' . $historico["SSES_DT_INICIO_VIDEO"] . '
                                    <br />
                                    <strong>Videoconferência realizada por este grupo: </strong>
                                    ' . $historico["SSES_IC_VIDEO_REALIZADA"];
                }
            }
            self::$html .= '<br /><strong>Por:</strong>' . $historico["MOFA_CD_MATRICULA"] . ' - ' . $historico["MOFA_CD_MATRICULA_NOME"] . ':
                            <br /><strong>Descrição:</strong> '.$historico["MOFA_DS_COMPLEMENTO"];
            if ($historico["NR_RED"]) {
                self::$html .= '<strong>Anexos:</strong>';
//$i = 1;
//foreach ($this->AnexAnexo as $Anexo):
//                       self::$html .= '<a target="_blank" title="Abrir Documento" href="'.$this->baseUrl().'/sisad/gerenciared/recuperar/id/'.$DocmDocumento["SSOL_ID_DOCUMENTO"].'/dcmto/'.$historico["NR_RED"].'">Anexo&emsp;</a>&emsp;';
//$i++; endforeach; 
            }
//					if($dadosSistema && $historico["FADM_ID_FASE"] == "1001"){ 
//						self::$html .= '<fieldset style="border: 1px solid #A6C9E2; margin-right: 15px;">
//							<legend>Solicitação de Serviço</legend>
//							<div style="margin-left: 10px; margin-top: -20px; font-size: 11px; ">
//							<br/><strong>Tipo de Serviço:</strong>'.$dadosSistema["CTSS_NM_CATEGORIA_SERVICO"].' 
//							<br/><strong>Ocorrência:</strong>'.$dadosSistema["OSIS_NM_OCORRENCIA"].'
//							<br/><strong>Atendimento Emergencial:</strong>'.$dadosSistema["ASSO_IC_ATENDIMENTO_EMERGENCIA"];
//					} 
        self::$html .= '</td>
                            </tr>';
        endforeach;

        self::$html .= '<tr>
                            <td  colspan="5" style="border:1px solid silver;">
                                <strong>DESCRIÇÃO DA SOLICITAÇÃO</strong>
                                <br />
                                <br />
                                '.$DocmDocumento["DOCM_DS_ASSUNTO_DOC"].'
                                </fieldset>
                            </td>
                        </tr>';
        return self::$html;
    }
     
}