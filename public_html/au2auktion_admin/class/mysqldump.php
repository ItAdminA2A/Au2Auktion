<?php
	/*
		Klasse der kan lave dump af mysql tabeller
	*/

	class mysqldump
	{
		// Tabeller, der skal laves backup af
		var $tables_like = "none";
		
		function like($like)
		{
			$this->tables_like = $like;
		}
		
		/*
			Laver array med tabel-struktur
			$array = array(
				"table" => array(
					"fields" => array(
						"id" => "`id` INT(10) NOT NULL DEFAULT '0' AUTO_INCREMENT",
						"test" => "`test` VARCHAR(50) NOT NULL DEFAULT '1'"
						),
					"keys" => array(
						"PRIMARY" => "PRIMARY KEY (`id`)",
						"test" => "UNIQUE KEY `test` (`id`, `test`)"
						)
					)
				);
		*/
		function structure()
		{
			$array = array();
			$tables = mysql_query("SHOW TABLES LIKE '" . mysql_real_escape_string($this->tables_like) . "'");
			while (list($table) = mysql_fetch_row($tables))
			{
				$array[$table] = array(
					"fields" => array(),
					"keys" => array()
					);
				$fields = mysql_query("SHOW FIELDS FROM `$table`");
				while ($field = mysql_fetch_array($fields))
				{
					$data = "`" . $field["Field"] . "` " . $field["Type"];
						
					if ($field["Null"] == "NO")
					{
						$data .= " NOT NULL";
						$allow_null = false;
					}
					else
					{
						$allow_null = true;
					}
		
					if (is_null($field["Default"]) and $field["Key"] != "PRI" and strpos($data, "NOT NULL") === false)
					{
						$data .= " DEFAULT NULL";
					}
					elseif ($field["Default"] != "")
					{
						$data .= " DEFAULT '" . mysql_real_escape_string(stripslashes($field["Default"])) . "'";
					}
					
					if ($field["Extra"] != "")
					{
						$data .= " " . $field["Extra"];
					}
						
					$array[$table]["fields"][$field["Field"]] = $data;
				}
				$array_key_names = array();
				$array_key_fields = array();
				$keys = mysql_query("SHOW KEYS FROM `$table`");
				while ($key = mysql_fetch_array($keys))
				{
					if (!isset($array_key_names[$key["Key_name"]]))
					{
						if ($key["Key_name"] == "PRIMARY")
						{
							$data = "PRIMARY KEY";
						}
						else
						{
							$data = "";
							if ($key["Non_unique"] == 0) $data = "UNIQUE ";
							$data .= "KEY `" . $key["Key_name"] . "`";
						}
						$array_key_names[$key["Key_name"]] = $data;
					}
					else
					{
						$array_key_fields[$key["Key_name"]] .= ",";
					}
					$array_key_fields[$key["Key_name"]] .= "`" . $key["Column_name"] . "`";
				}
				reset($array_key_names);
				while (list($key, $value) = each($array_key_names))
				{
					$array[$table]["keys"][$key] = $value . " (" . $array_key_fields[$key] . ")";
				}
			}
			return $array;
		}
		
		// Laver SQL dump
		function dump($add_drop = true)
		{
			$array_dump = array();
			$tables = mysql_query("SHOW TABLES LIKE '" . mysql_real_escape_string($this->tables_like) . "'");
			while (list($table) = mysql_fetch_row($tables))
			{
				if ($add_drop) $array_dump[count($array_dump)] = "DROP TABLE `$table`";
				$data = "CREATE TABLE `$table` (\r\n";
				$first = true;
				$fields = mysql_query("SHOW FIELDS FROM `$table`");
				$array_fields = array();
				$array_fields_null = array();
				$sql_fields = "";
				while ($field = mysql_fetch_array($fields))
				{
					if (!$first) $data .= ",\r\n";
					$first = false;
					
					$data .= "`" . $field["Field"] . "` " . $field["Type"];
						
					if ($field["Null"] == "NO")
					{
						$data .= " NOT NULL";
						$allow_null = false;
					}
					else
					{
						$allow_null = true;
					}
		
					if (is_null($field["Default"]) and $field["Key"] != "PRI" and strpos($data, "NOT NULL") === false)
					{
						$data .= " DEFAULT NULL";
					}
					elseif ($field["Default"] != "")
					{
						$data .= " DEFAULT '" . mysql_real_escape_string(stripslashes($field["Default"])) . "'";
					}
					
					if ($field["Extra"] != "")
					{
						$data .= " " . $field["Extra"];
					}
						
					$array_fields[count($array_fields)] = $field["Field"];
					$array_fields_null[count($array_fields_null)] = $allow_null;
					if ($sql_fields != "") $sql_fields .= ",";
					$sql_fields .= "`" . $field["Field"] . "`";
				}
				$array_keys = array();
				$keys = mysql_query("SHOW KEYS FROM `$table`");
				while ($key = mysql_fetch_array($keys))
				{
					if ($key["Key_name"] == "PRIMARY")
					{
						$val = "PRIMARY KEY";
					}
					else
					{
						$val = "";
						if ($key["Non_unique"] == 0) $val = "UNIQUE ";
						$val .= "KEY `" . $key["Key_name"] . "`";
					}
					if ($array_keys[$val] != "") $array_keys[$val] .= ", ";
					$array_keys[$val] .= "`" . $key["Column_name"] . "`";
				}
				reset($array_keys);
				while (list($key, $val) = each($array_keys))
				{
					$data .= ",\r\n" . $key . " ($val) ";
				}
				$data .= "\r\n)";
				$array_dump[count($array_dump)] = $data;
				
				$ress = mysql_query("SELECT * FROM `$table`");
				while ($res = mysql_fetch_array($ress))
				{
					$data = "INSERT INTO `$table` ($sql_fields) VALUES (";
					for ($i = 0; $i < count($array_fields); $i++)
					{
						if ($i > 0) $data .= ",";
						if ($array_fields_null[$i] and $res[$array_fields[$i]] == NULL)
						{
							$data .= "NULL";
						}
						else
						{
							$data .= "'" . mysql_real_escape_string(stripslashes($res[$array_fields[$i]])) . "'";
						}
					}
					$data .= ")";
					$array_dump[count($array_dump)] = $data;
				}
			}
			return $array_dump;
		}
	}
?>