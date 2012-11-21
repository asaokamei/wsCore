<?php
namespace WScore\DbAccess;

class SqlBuilder
{
    // +----------------------------------------------------------------------+
    //  Making SQL Statements.
    // +----------------------------------------------------------------------+
    /**
     * @param SqlObject $sql
     * @throws \RuntimeException
     * @return string
     */
    public static function makeInsert( $sql )
    {
        if( is_array( $sql ) ) $sql = (object) $sql;
        if( !$sql->table ) throw new \RuntimeException( 'table not set. ' );
        $listV = implode( ', ', $sql->rowData );
        $listC = implode( ', ', array_keys( $sql->rowData ) );
        $insert = "INSERT INTO {$sql->table} ( {$listC} ) VALUES ( {$listV} )";
        return $insert;
    }

    /**
     * @param SqlObject $sql
     * @return string
     * @throws \RuntimeException
     */
    public static function makeUpdate( $sql )
    {
        if( is_array( $sql ) ) $sql = (object) $sql;
        if( !$sql->table ) throw new \RuntimeException( 'table not set. ' );
        $list   = array();
        foreach( $sql->rowData as $col => $val ) {
            $list[] = "{$col}={$val}";
        }
        $update  = "UPDATE {$sql->table} SET " . implode( ', ', $list );
        $update .= ( $where= self::makeWhere( $sql->where ) ) ? " WHERE {$where}" : '';
        return $update;
    }

    /**
     * @param SqlObject $sql
     * @return string
     * @throws \RuntimeException
     */
    public static function makeDelete( $sql )
    {
        if( is_array( $sql ) ) $sql = (object) $sql;
        if( !$sql->table ) throw new \RuntimeException( 'table not set. ' );
        if( !$where = self::makeWhere( $sql->where ) ) {
            throw new \RuntimeException( 'Cannot delete without where condition. ' );
        }
        return "DELETE FROM {$sql->table} WHERE " . $where;
    }

    // +----------------------------------------------------------------------+
    //  Making Clauses for SQL Statement.
    // +----------------------------------------------------------------------+
    /**
     * @param array|string $where
     * @return string
     */
    public static function makeWhere( $where )
    {
        if( is_array( $where ) ) {
            $where_str = '';
            foreach( $where as $wh ) {
                $where_str .= call_user_func_array( array( 'self', 'formWhere' ), $wh );
            }
        }
        else {
            $where_str = $where;
        }
        $where_str = trim( $where_str );
        $where_str = preg_replace( '/^(and|or) /i', '', $where_str );
        return $where_str;
    }

    /**
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param string $op
     * @return string
     */
    public static function formWhere( $col, $val, $rel='=', $op='AND' ) 
    {
        $where = '';
        $rel = strtoupper( $rel );
        if( $rel == 'IN' || $rel == 'NOT IN' ) {
            $tmp = is_array( $val ) ? implode( ", ", $val ): "{$val}";
            $val = "( " . $tmp . " )";
        }
        elseif( $rel == 'BETWEEN' ) {
            $val = "{$val{0}} AND {$val{1}}";
        }
        elseif( $col == '(' ) {
            $val = $rel = '';
        }
        elseif( $col == ')' ) {
            $op = $rel = $val = '';
        }
        elseif( "$val" == "" && "$rel" == "" ) {
            return '';
        }
        $where .= trim( "{$op} {$col} {$rel} {$val}" ) . ' ';
        return $where;
    }
    
    /**
     * @param SqlObject $sql
     * @return string
     */
    public static function makeCount( $sql )
    {
        if( is_array( $sql ) ) $sql = (object) $sql;
        $count = clone $sql;
        $count->columns   = 'COUNT(*) AS WScore__Count__';
        $count->forUpdate = FALSE;
        $select = self::makeSelect( $count );
        return $select;
    }

    /**
     * @param SqlObject $sql
     * @return string
     */
    public static function makeSelect( $sql )
    {
        if( is_array( $sql ) ) $sql = (object) $sql;
        $select  = 'SELECT ';
        $select .= ( $sql->distinct ) ? 'DISTINCT ': '';
        $select .= self::makeSelectBody( $sql );
        $select .= ( $sql->forUpdate ) ? ' FOR UPDATE': '';
        return $select;
    }

    /**
     * @param SqlObject $sql
     * @return string
     * @throws \RuntimeException
     */
    public static function makeSelectBody( $sql )
    {
        if( !$sql->table ) throw new \RuntimeException( 'table not set. ' );
        $select  = self::makeColumn( $sql->columns );
        $select .= ' FROM ' . $sql->table;
        $select .= self::makeJoin( $sql->join );
        $select .= ( $where = self::makeWhere( $sql->where ) ) ? ' WHERE '.$where: '';
        $select .= ( $sql->group  ) ? ' GROUP BY '   . $sql->group: '';
        $select .= ( $sql->having ) ? ' HAVING '     . $sql->having: '';
        $select .= ( $sql->order  ) ? ' ORDER BY '   . $sql->order: '';
        $select .= ( $sql->misc   ) ? ' '            . $sql->misc: '';
        $select .= ( $sql->limit  > 0 ) ? ' LIMIT '  . $sql->limit: '';
        $select .= ( $sql->offset > 0 ) ? ' OFFSET ' . $sql->offset: '';
        return $select;
    }

    /**
     * @param array $join
     * @return string
     */
    public static function makeJoin( $join ) 
    {
        $joined = '';
        if( !empty( $join ) )
            foreach( $join as $j ) {
                $joined .= ' ' . $j;
            }
        return $joined;
    }

    /**
     * @param array|string $columns
     * @return string
     */
    public static function makeColumn( $columns ) 
    {
        if( !$columns || empty( $columns ) ) {
            $column = '*';
        }
        elseif( is_array( $columns ) ) {
            $column = implode( ', ', $columns );
        }
        else {
            $column = $columns;
        }
        return $column;
    }
    // +----------------------------------------------------------------------+
}