<?php
	/*
		Se / opret notat til modul / side
	*/
	
	list($tmpmodule, $tmppage) = split("[|]", $vars["id"]);
	
	if ($tmpmodule == "" and $tmppage == "") die("Ugyldigt modul");
	
	if ($vars["submit"] == "true")
	{
		$db->execute("
			UPDATE
				" . $_table_prefix . "_admin_notes
			SET
				note = '" . $db->escape($vars["note"]) . "' 
			WHERE
				module = '" . $db->escape($tmpmodule) . "' AND
				page = '" . $db->escape($tmppage) . "'
			");
		if ($db->affected_rows() == 0)
		{
			$db->execute("
				INSERT INTO
					" . $_table_prefix . "_admin_notes
				(
					module,
					page,
					note
				)
				VALUES
				(
					'" . $db->escape($tmpmodule) . "',
					'" . $db->escape($tmppage) . "',
					'" . $db->escape($vars["note"]) . "'
				)
				");
		}
		$html .= "<script> close(); </script>";
	}
	else
	{	
		$tmp = new tpl("admin_note");
		$tmp->set("id", $vars["id"]);
		$tmp->set("note", stripslashes($db->execute_field("
			SELECT
				note
			FROM
				" . $_table_prefix . "_admin_notes
			WHERE
				module = '" . $db->escape($tmpmodule) . "' AND
				page = '" . $db->escape($tmppage) . "'
			")));
		$html .= $tmp->html();
	}
	
	$tpl = "popup";
?>