<?php
class Application_Model_DbTable_SadTbModeMoviDestinatario extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SAD';
    protected $_name = 'SAD_TB_MODE_MOVI_DESTINATARIO';
    protected $_primary = array('MODE_ID_MOVIMENTACAO') ;

    
    public function setLeitura($id_movimentacao,$cd_matr_recebedor){
                
        $fetchRow = $this->fetchRow("MODE_ID_MOVIMENTACAO = $id_movimentacao ")->toArray();
        
        if(is_null($fetchRow["MODE_CD_MATR_RECEBEDOR"])){
        
            $Dual = new Application_Model_DbTable_Dual();
            $data["MODE_ID_MOVIMENTACAO"] = $id_movimentacao;
            //$data["MODE_SG_SECAO_UNID_DESTINO"] = NULL;
            //$data["MODE_CD_SECAO_UNID_DESTINO"] = NULL;
            //$data["MODE_IC_RESPONSAVEL"] = NULL;
            $data["MODE_DH_RECEBIMENTO"] = $Dual->sysdate();
            $data["MODE_CD_MATR_RECEBEDOR"] = $cd_matr_recebedor;
            //$data["MODE_ID_CAIXA_ENTRADA"] = NULL;

            $row = $this->find($data["MODE_ID_MOVIMENTACAO"])->current();
            $row->setFromArray($data);
            $row->save();
        }else{
            return;
        }
    }
}