<?php
	/*
		System meddelelser, fejl meldingerne etc.
	*/
	
	$msg = new message;
	$msg->title("{LANG|System-meddelelser}");
	$html .= $msg->html();
	
	$total = 100;
		
	$paging = new paging;
	$paging->total($total);
	$limit = $paging->limit(20);
	$start = ($paging->current_page() - 1) * $limit;
	$html .= $paging->html();
	
	$tbl = new table;
	$tbl->th("{LANG|Tid}");
	$tbl->th("{LANG|Meddelelse}");
	$tbl->endrow();
	
	$db->execute("
		SELECT
			*
		FROM
			" . $_table_prefix . "_log_messages
		ORDER BY
			id DESC
		LIMIT
			$start, $limit
		");
	while ($db->fetch_array())
	{
		$tbl->td(date("d-m-Y H:i:s", strtotime($db->array["time"])));
		$tbl->td(nl2br(stripslashes($db->array["message"])));
		$tbl->endrow();
	}		
	
	if ($db->num_rows() == 0)
	{
		$tbl->td(date("d-m-Y H:i:s"));
		$tbl->td("{LANG|Ingen meddelelser}...");
		$tbl->endrow();
	}
	
	$html .= $tbl->html();
?>