<?php
	/*
		Klasse der kan sende SMS via HTTP gateway
	*/

	class sms
	{
		// Modtager liste
		var $sms_mobile = array();
		
		// Besked
		var $sms_message = "";
		
		// Afsender
		var $sms_sender = "";
		
		// Fejl
		var $error = "";
		
		function mobile($mobile)
		{
			$this->sms_mobile[count($this->sms_mobile)] = $mobile;
		}
		
		function sender($sender)
		{
			$this->sms_sender = $sender;
		}
		
		function message($message)
		{
			$this->sms_message = $message;
		}
		
		function send()
		{
			// Henter indstillinger
			$sms_url = cms_setting("sms_url");
			$sms_response_ok = cms_setting("sms_response_ok");
			$sms_response_regexp = (cms_setting("sms_response_regexp") == 1);
			
			$res = "";
			$ok = true;
			if (cms_setting("sms_multiple") == 1)
			{
				// Flere pr. kald
				$sms_separator = cms_setting("sms_separator");
				$mobiles = "";
				for ($i = 0; $i < count($this->sms_mobile); $i++)
				{
					if ($i > 0) $mobiles .= $sms_separator;
					$mobiles .= urlencode($this->sms_mobile[$i]);
				}
				$sms_url = str_replace("{MOBILE}", $mobiles, $sms_url);
				$sms_url = str_replace("{SENDER}", urlencode($this->sms_sender), $sms_url);
				$sms_url = str_replace("{MESSAGE}", urlencode($this->sms_message), $sms_url);
				$res = file_get_contents($sms_url);
				
				if ($sms_response_regexp)
				{
					$ok = eregi($sms_response_ok, $res);
				}
				else
				{
					$ok = ($res == $sms_response_ok);
				}
			}
			else
			{
				// En af gangen
				$sms_url = str_replace("{SENDER}", urlencode($this->sms_sender), $sms_url);
				$sms_url = str_replace("{MESSAGE}", urlencode($this->sms_message), $sms_url);
				for ($i = 0; $ok and $i < count($this->sms_mobile); $i++)
				{
					$res = file_get_contents(str_replace("{MOBILE}", urlencode($this->sms_mobile[$i]), $sms_url));
					if ($sms_response_regexp)
					{
						$ok = eregi($sms_response_ok, $res);
					}
					else
					{
						$ok = ($res == $sms_response_ok);
					}
				}
			}
			if (!$ok)
			{
				$this->error = $res;
				if ($res == "") $res = "None";
				add_log_message("Could not send SMS\r\n" .
					"Mobile: " . implode(", ", $this->sms_mobile) . "\r\n" .
					"Message: " . $this->sms_message . "\r\n" .
					"Gateway response: $res");
			}
			return $ok;
		}
	}
?>