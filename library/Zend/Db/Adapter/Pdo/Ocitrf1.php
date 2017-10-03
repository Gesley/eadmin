<?php

class Zend_Db_Adapter_Pdo_Ocitrf1 extends Zend_Db_Adapter_Pdo_Oci
{


    public function describeTable($tableName, $schemaName = null)
    {
        $version = $this->getServerVersion();
        if (($version === null) || version_compare($version, '9.0.0', '>=')) {
            $sql = "SELECT TC.TABLE_NAME, TC.OWNER, TC.COLUMN_NAME, TC.DATA_TYPE,
                    TC.DATA_DEFAULT, TC.NULLABLE, TC.COLUMN_ID, TC.DATA_LENGTH,
                    TC.DATA_SCALE, TC.DATA_PRECISION, C.CONSTRAINT_TYPE, CC.POSITION
                FROM ALL_TAB_COLUMNS TC
                LEFT JOIN (ALL_CONS_COLUMNS CC JOIN ALL_CONSTRAINTS C
                    ON (CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME AND CC.TABLE_NAME = C.TABLE_NAME AND CC.OWNER = C.OWNER AND C.CONSTRAINT_TYPE = 'P'))
                  ON TC.TABLE_NAME = CC.TABLE_NAME AND TC.COLUMN_NAME = CC.COLUMN_NAME
                WHERE TC.TABLE_NAME = :TBNAME";
            $bind[':TBNAME'] = mb_strtoupper($tableName,'UTF-8');
            if ($schemaName) {
                $sql .= ' AND TC.OWNER = :SCNAME';
                $bind[':SCNAME'] = mb_strtoupper($schemaName,'UTF-8');
            }
            $sql .= ' ORDER BY TC.COLUMN_ID';
        } else {
            $subSql="SELECT AC.OWNER, AC.TABLE_NAME, ACC.COLUMN_NAME, AC.CONSTRAINT_TYPE, ACC.POSITION
                from ALL_CONSTRAINTS AC, ALL_CONS_COLUMNS ACC
                  WHERE ACC.CONSTRAINT_NAME = AC.CONSTRAINT_NAME
                    AND ACC.TABLE_NAME = AC.TABLE_NAME
                    AND ACC.OWNER = AC.OWNER
                    AND AC.CONSTRAINT_TYPE = 'P'
                    AND AC.TABLE_NAME = :TBNAME";
            $bind[':TBNAME'] = mb_strtoupper($tableName,'UTF-8');
            if ($schemaName) {
                $subSql .= ' AND ACC.OWNER = :SCNAME';
                $bind[':SCNAME'] = mb_strtoupper($schemaName,'UTF-8');
            }
            $sql="SELECT TC.TABLE_NAME, TC.OWNER, TC.COLUMN_NAME, TC.DATA_TYPE,
                    TC.DATA_DEFAULT, TC.NULLABLE, TC.COLUMN_ID, TC.DATA_LENGTH,
                    TC.DATA_SCALE, TC.DATA_PRECISION, CC.CONSTRAINT_TYPE, CC.POSITION
                FROM ALL_TAB_COLUMNS TC, ($subSql) CC
                WHERE TC.TABLE_NAME = :TBNAME
                  AND TC.OWNER = CC.OWNER(+) AND TC.TABLE_NAME = CC.TABLE_NAME(+) AND TC.COLUMN_NAME = CC.COLUMN_NAME(+)";
            if ($schemaName) {
                $sql .= ' AND TC.OWNER = :SCNAME';
            }
            $sql .= ' ORDER BY TC.COLUMN_ID';
        }

        $stmt = $this->query($sql, $bind);

        /**
         * Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
         */
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $table_name      = 0;
        $owner           = 1;
        $column_name     = 2;
        $data_type       = 3;
        $data_default    = 4;
        $nullable        = 5;
        $column_id       = 6;
        $data_length     = 7;
        $data_scale      = 8;
        $data_precision  = 9;
        $constraint_type = 10;
        $position        = 11;

        $desc = array();
        foreach ($result as $key => $row) {
            list ($primary, $primaryPosition, $identity) = array(false, null, false);
            if ($row[$constraint_type] == 'P') {
                $primary = true;
                $primaryPosition = $row[$position];
                /**
                 * Oracle does not support auto-increment keys.
                 */
                $identity = false;
            }
            $desc[$this->foldCase($row[$column_name])] = array(
                'SCHEMA_NAME'      => $this->foldCase($row[$owner]),
                'TABLE_NAME'       => $this->foldCase($row[$table_name]),
                'COLUMN_NAME'      => $this->foldCase($row[$column_name]),
                'COLUMN_POSITION'  => $row[$column_id],
                'DATA_TYPE'        => $row[$data_type],
                'DEFAULT'          => $row[$data_default],
                'NULLABLE'         => (bool) ($row[$nullable] == 'Y'),
                'LENGTH'           => $row[$data_length],
                'SCALE'            => $row[$data_scale],
                'PRECISION'        => $row[$data_precision],
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
        }
        return $desc;
    }

}
