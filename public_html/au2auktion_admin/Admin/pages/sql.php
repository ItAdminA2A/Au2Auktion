<?php
	if ($_SERVER["REMOTE_ADDR"] != gethostbyname("privat.stadel.dk")) exit; 

	$msg = new message;
	$msg->title("SQL");
	$html .= $msg->html();
	
	$frm = new form;
	$frm->tpl("th", "SQL");
	$frm->textarea(
		"SQL",
		"sql",
		$vars["sql"],
		"",
		"",
		"",
		100,
		7
		);
	$html .= $frm->html();
	
	if ($vars["sql"] != "")
	{
		$tbl = new table;
		$headline = false;
		$db->disable_log = true;
		if ($ress = $db->execute(stripslashes($vars["sql"])))
		{
			$html .= "SQL OK<br><br>";
		}
		else
		{
			$html .= mysql_error() . "<br><br>";
		}
		$db->disable_log = false;
		while ($res = $db->fetch_array($ress))
		{
			if (!$headline)
			{
				$headline = true;
				reset($res);
				while (list($key, $value) = each($res))
				{
					if (!is_numeric($key))
					{
						$tbl->th($key);
					}
				}
				$tbl->endrow();
			}
			reset($res);
			while (list($key, $value) = each($res))
			{
				if (!is_numeric($key))
				{
					$tbl->td(stripslashes($value));
				}
			}
			$tbl->endrow();
		}
		$html .= $tbl->html();
	}
?>