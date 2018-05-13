<?php
abstract class mysql_main {
public $last_query;

public function select($table, $where=true) {
$query=sprintf("SELECT * FROM `%s` WHERE %s", $this->escape($table), $where);
$result=$this->action($query);
return $result;
}
		

public function p_select($table, $where=true) {
return $this->process($this->select($table, $where));
}

public function insert($table, $object) {
		$columns = array();
		$values = array();

		foreach($object as $key => $value) {
			$key =$this->escape($key . '');
			$columns[] = "`{$key}`";
			if (is_null($value )) {
				$values[] = 'NULL';
			} else {
				$value = $this->escape($value . '');
				$values[] = "'{$value}'";
			}
		}
		
		$columns = implode(', ', $columns);
		$values = implode(', ', $values);

		$query = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->escape($table), $columns, $values);
		$result = $this->action($query);
		return $this->insert_id;
	}

	public function update($table, $object, $where) {
		$sets = array();
		foreach ($object as $key => $value) {
			$key = $this->escape($key . '');
			if (is_null($value)) $sets[] = "`{$key}`=NULL"; else {
				$value = $this->escape($value . '');
				$sets[] = "`{$key}`='{$value}'";
			}
		}
		$sets = implode(', ', $sets);
		$query = sprintf("UPDATE `%s` SET %s WHERE %s", $this->escape($table), $sets, $where);
		$result =$this->action($query);
		return $this->affected_rows;
		}

	public function delete($table, $where) {
		$query = sprintf("DELETE FROM `%s` WHERE %s", $this->escape($table), $where);
		$result = $this->action($query);
		return $this->affected_rows;
	}

public function query($query) {
$result =$this->action($query);
return $result;
}

public function p_query($query) {
return $this->process($this->query($query));
}

public function get_row($table, $where) {
$row=$this->p_select($table, $where);
return (isset($row[0])) ? $row[0] : false;
}

public function extract_row($query) {
$rows=$this->p_query($query);
return (isset($rows[0])) ? $rows[0] : false;
}

public function get_column($table, $column) {
$arr1=$this->p_query(sprintf("SELECT `%s` FROM `%s`", $this->escape($column), $this->escape($table)));
$arr2=[];
foreach($arr1 as $value) {
$arr2[]=$value[$column];
}
return $arr2;
}

public function extract_value($query) {
$result=$this->query($query);
if (!$row=$result->fetch_assoc()) return false;
foreach ($row as $value) {
return $value;
}
return false;
}

public function get_value($table, $column, $row, $needcol) {
$result=$this->p_query(sprintf("SELECT `%s` FROM `%s` WHERE `%s` = '%s'", 
$this->escape($needcol), $this->escape($table), $this->escape($column), $this->escape($row)));
$value=(isset($result[0]) && isset($result[0][$needcol])) ? $result[0][$needcol] : false;
return $value;
}

public function get_count($table, $where) {
$arr=$this->p_query(sprintf("SELECT count(*) FROM `%s` WHERE %s", $this->escape($table), $where));
return $arr[0]['count(*)'];
}

public function escape($string) {
return $this->link->real_escape_string($string);
}

private function process($result) {
		$arr = [];
		while ($row = $result->fetch_assoc()) {
			$arr[] = $row;
		}
		return $arr;
	}

private function action($query) {
$this->last_query=nl2br($query);
$result=$this->link->query($query);
if (!$result) $this->error();
return $result;
}

private function error() {
$message="{$this->link->error}";
$message.="<hr>{$this->last_query}";
die($message);
}

}