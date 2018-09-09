<?php
	/*COPYRIGHT*\
		COPYRIGHT STADEL.DK 2006
		
		AL KODE I DENNE FIL TILHØRER STADEL.DK, THOMAS@STADEL.DK.
		KODEN MÅ UNDER INGEN  OMSTÆNDIGHEDER  BENYTTES  TIL ANDET
		FORMÅL END  DET DEN ER KØB TIL.  KODEN MÅ IKKE  ÆNDRES AF
		ANDRE   END   STADEL.DK.   KODEN  MÅ  IKKE  SÆLGES  ELLER
		VIDEREDISTRIBUERES  HELT, DELVIS ELLER SOM EN KOPI AF DET
		SYSTEM   DET  OPRINDELIGT  ER  KØBT  SAMMEN  MED.  ENHVER
		OVERTRÆDELSE  AF EN ELLER FLERE AF DE NÆVNTE  BETINGELSER
		VIL RESULTERE I RETSFORFØLGELSE OG ERSTATNING FOR BRUD PÅ
		OPHAVSRETTEN AF KODEN, IFLG.  DANSK  OPHAVSRETSLOV. DENNE
		COPYRIGHT    MEDDELELSE    MÅ    DESUDEN    UNDER   INGEN
		OMSTÆNDIGHEDER FJERNES FRA DENNE FIL.
	
		ALL   CODE  IN  THIS  FILE  ARE  COPYRIGHTED   STADEL.DK,
		THOMAS@STADEL.DK.  IT'S NOT  ALLOWED TO USE THIS CODE FOR 
		ANY OTHER PURPOSE  THAN TOGEHTER  WITH THE ORGINAL SCRIPT 
		AS IT HAS BEEN  BOUGHT  AS A PART OF. IT'S NOT ALLOWED TO 
		SELL OR REDISTRIBUTE  THE CODE IN IT'S COMPLETE SENTENCE,
		ANY  PART OF THE  CODE OR AS A PART OF ANOTHER  SYSTEM OR 
		SCRIPT.  ANY  VIOLATION  OF  THESE  RULES  WILL RESULT IN 
		PROSECUTION   AND   COMPENSATION  FOR  VIOLATION  OF  THE 
		COPYRIGHT OF THIS SYSTEM,  SCRIPT AND CODE,  ACCORDING TO 
		DANISH  COPYRIGHT LAW. THIS  COPYRIGHT  MAY  NOT,  IN ANY 
		CIRCUMSTANCE, BE REMOVED FROM THIS FILE.
	\*COPYRIGHT*/

	/*
		18-01-2007:		Ændring af mail-opbygning så den virker i Outlook, Hotmail m.f.
		19-01-2007:		Tilføjelse af headere "Message-ID" og "Date"
		30-01-2007:		Rettelse af "Message-ID": Mangler <> omkring message-ID i layout-filer
						Ændret så body-plain tekst kan defineres for HTML-emails
		02-02-2007:		Tilføjet quoted-printable encoding for plain, 7bit encoding for HTML samt
						qouted-printable for subject
		20-03-2007:		Rettet fejl vedr. $body_plain
						Ændret max længde af QP linie til 70 tegn
		16-08-2007:		Rettet relative links i HTML-emails
		21-01-2008:		Mulighed for kvittering
		28-01-2008:		Rettet bug vedr. content-type for vedhæftede filer
		03-03-2008:		Læser nu smtp-server fra _settings_
		07-05-2008:		Fjerner \r inden der søges efter header og body
		27-03-2009:		Tilføjer return-path
		01-04-2009:		Tilføjet AUTH LOGIN
		17-08-2011:		Erstatter [EMAIL] med modtagers e-mail
	*/
	
	class email
	{
		// Emne
		var $subject = "";
		// Besked
		var $body = "";
		var $body_plain = "Dette er en HTML email, så du skal benytte en HTML læser for at læse denne email\r\nThis is a HTML email so you will need a HTML reader to read this email";
		// Format
		var $format = "html";
		// Array med modtagere
		var $to_email = array();
		var $to_name = array();
		// Variabler [VARNAME] der skal erstattes
		var $to_vars = array();
		// Afsender
		var $from_email = "";
		var $from_name = "";
		var $from_return_email = "";
		// Template der anvendes til e-mail
		var $tpl = "_email_layout";
		var $tpl_vars = array();
		// Array med filer der skal vedhæftes
		var $attach = array();
		var $attach_filename = array();
		var $attach_contentid = array();
		var $attach_contenttype = array();
		// Angiver om der automatisk skal vedhæftes billeder, hvis det er HTML
		var $attach_images = true;
		// SMTP server
		var $use_smtp = false;
		var $smtp_server = "localhost";
		var $smtp_port = 25;
		var $smtp_user = "";
		var $smtp_pass = "";
		// Kvittering
		var $receipt = false;
		// Fejl ved afsendelse
		var $error = "";
		// Message-ID
		var $message_id = "";
		
		// Init
		function email()
		{
			global $_settings_;
			$this->from($_settings_["SITE_EMAIL_NAME"], $_settings_["SITE_EMAIL"], $_settings_["RETURN_EMAIL"]);
			$this->use_smtp = ($_settings_["EMAIL_METHOD"] == "smtp");
			$this->smtp_server = $_settings_["EMAIL_SMTP_HOST"];
			$this->smtp_port = $_settings_["EMAIL_SMTP_PORT"];
			$this->smtp_user = $_settings_["EMAIL_SMTP_USER"];
			$this->smtp_pass = $_settings_["EMAIL_SMTP_PASS"];
			$this->attach_images = ($_settings_["EMAIL_ATTACH_IMAGES"] == 1);
			$this->html();
			
			// Laver default message-id
			$this->message_id();
		}
		
		// Angiv message-id - format: "Stadel.dk.CMS.da.Modulnavn.side.handling.id.message_id@servernavn"
		function message_id($message_id = "")
		{
			global $module, $page, $do, $id, $_lang_id;
			if ($message_id == "") $message_id = uniqid("");
			$this->message_id = "Stadel.dk.CMS." . $_lang_id . "." . $module . "." . $page . "." . $do . "." . $id . "." . $message_id . "@" . $_SERVER["HTTP_HOST"];
		}
		
		// Angiv om der automatisk skal vedhæftes billeder, hvis der er HTML
		function attach_images($attach_images)
		{
			$this->attach_images = $attach_images;
		}
		
		// Angiv om der skal anmodes om kvittering
		function receipt($receipt)
		{
			$this->receipt = $receipt;
		}
		
		// Angiver fil, der skal vedhæftes
		function attach($filename, $attach_filename = "", $contenttype = "", $contentid = false)
		{
			if (!is_file($filename) and !eregi("^http://", $filename)) return false;
			// Tjekker content-type
			if ($contenttype == "")
			{
				if (eregi("\.([a-z]{3,4})", $attach_filename, $array))
				{
					$contenttype = strtolower($array[1]);
					if (in_array($contenttype, array("gif", "jpg", "jpeg", "png", "bmp", "tif", "tiff")))
					{
						$contenttype = "image/" . $contenttype;
					}
					else
					{
						$contenttype = "application/" . $contenttype;
					}
				}
				else
				{
					$contenttype = "application/x";
				}
			}
			$i = count($this->attach);
			$this->attach[$i] = $filename;
			$this->attach_filename[$i] = $attach_filename;
			$this->attach_contentid[$i] = $contentid;
			$this->attach_contenttype[$i] = $contenttype;
		}
		
		// Angiver emne
		function subject($subject)
		{
			$this->subject = $subject;
		}
		
		// Angiver besked
		function body($body)
		{
			$this->body = $body;
		}
		
		// Angiver plain besked ved HTML mails
		function body_plain($body_plain)
		{
			$this->body_plain = $body_plain;
		}
		
		// Angiver at formatet skal være HTML
		function html()
		{
			global $_settings_, $_document_root;
			if (is_file($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/email.html"))
			{
				$this->tpl = "LAYOUT|" . $_settings_["SITE_LAYOUT"] . "|email";
			}
			else
			{
				$this->tpl = "_email_layout";
			}
			$this->format = "html";
		}
		
		// Angiver at formatet skal være tekst
		function plain()
		{
			global $_settings_, $_document_root;
			if (is_file($_document_root . "/layouts/" . $_settings_["SITE_LAYOUT"] . "/html/email_text.html"))
			{
				$this->tpl = "LAYOUT|" . $_settings_["SITE_LAYOUT"] . "|email_text";
			}
			else
			{
				$this->tpl = "_email_layout_text";
			}
			$this->format = "plain";
		}
		
		// Angiver at formatet skal være tekst
		function text()
		{
			$this->plain();
		}
		
		// Tilføjer modtager
		function to($name, $email = "", $vars = array())
		{
			if ($email == "") $email = $name;
			
			$name = preg_replace("/[^a-zA-Z0-9\-\.\/]/", "", $name);
			
			$i = count($this->to_name);
			$this->to_name[$i] = $name;
			$this->to_email[$i] = $email;
			$this->to_vars[$i] = $vars;
		}
		
		// Angiver afsender
		function from($name, $email, $return_email = "")
		{
			global $_settings_;
			$name = str_replace("\"", "", $name);
			if ($return_email == "")
			{
				$return_email = $_settings_["RETURN_EMAIL"];
				if (!$return_email or $return_email == "") $return_email = $email;
			}
			$this->from_name = $name;
			$this->from_email = $email;
			$this->from_return_email = $return_email;
		}

		// Laver quoted-printable encoding - bruges til plain		
		function encode_qp($in)
		{
			$out = "";
			$len = 0;
			for ($i = 0; $i < strlen($in); $i++)
			{
				$char = substr($in, $i, 1);
				$ord = ord($char);
				if ($ord < 33 or $ord == 61 or $ord > 126)
				{
					// Encoder tegn
					$char = "=" . bin2hex($char);
				}
				$len += strlen($char);
				if ($len >= 70)
				{
					// Laver soft linieskift
					$out .= "=\r\n";
					$len = strlen($char);
				}
				$out .= $char;
			}
			return $out;
		}
		
		// Laver 7bit encoding af HTML
		function encode_html($in)
		{
			$in = str_replace("\r", "", $in);
			$out = "";
			$len = 0;
			for ($i = 0; $i < strlen($in); $i++)
			{
				$char = substr($in, $i, 1);
				$ord = ord($char);
				if ($ord > 126)
				{
					// Encoder tegn
					$char = "&#" . $ord . ";";
				}
				$len += strlen($char);
				if ($len >= 998)
				{
					// Finder et tag, hvor vi kan indsætte linieskift
					$pos = strrpos($out, "<");
					if ($pos === false) $pos = 998;
					// Laver linieskift
					$out = substr($out, 0, $pos) . "\r\n" . substr($out, $pos);
					// Finder længde
					$len = strlen($out) + strlen($char) - $pos;
				}
				$out .= $char;
			}
			return $out;
		}
		
		// Laver quoted-printable encoding af subject
		function encode_subject($in)
		{
			$out = "=?iso-8859-1?Q?";
			for ($i = 0; $i < strlen($in); $i++)
			{
				$char = substr($in, $i, 1);
				$ord = ord($char);
				if ($ord < 33 or $ord == 61 or $ord > 126 or $char == "?")
				{
					// Encoder tegn
					$char = "=" . bin2hex($char);
				}
				$out .= $char;
			}
			$out .= "?=";
			return $out;
		}
		
		// Sender email
		function send()
		{
			global $_lang_id;
			
			// Behandler subject - oversætter etc.
			$tmp = new tpl($this->subject);
			reset($this->tpl_vars);
			while (list($key, $value) = each($this->tpl_vars)) $tmp->set($key, $value);
			$subject = $tmp->html();
			
			// Henter layout for mail
			$tmp = new tpl($this->tpl);
			reset($this->tpl_vars);
			while (list($key, $value) = each($this->tpl_vars)) $tmp->set($key, $value);
			$tmp->set("server_name", $_SERVER["SERVER_NAME"]);
			$tmp->set("html", $this->body);
			$body = $tmp->html();
			
			// Henter body plain
			$body_plain = $this->body_plain;
			
			// Filer, der skal vedhæftes
			$attach = $this->attach;
			$attach_filename = $this->attach_filename;
			$attach_contentid = $this->attach_contentid;
			$attach_contenttype = $this->attach_contenttype;

			// Vedhæft billeder ?
			$has_inline = false;
			if ($this->attach_images and $this->format == "html")
			{
				// Finder billeder i HTML og vedhæfter som filer
				
				// <img src="">
				$dummyid = uniqid("IMAGEATTACH");
				while (eregi("<img[^>]* (src=[\"']{0,1}([^>^ ^\"^']+)[\"']{0,1})[^>]*>", $body, $array))
				{
					// Billede fundet
					$replace_this = $array[1];
					$i = count($attach);
					// Finder URL
					$url = $array[2];
					if (!eregi("^(http://|https://)", $url))
					{
						$url = ereg_replace("^/", "", $url);
						$url = "http://" . $_SERVER["SERVER_NAME"] . "/$url";
					}
					$attach[$i] = $url;
					// Finder contenttype
					$contenttype = "";
					if (eregi("\.([a-z]+)$", $url, $array))
					{
						$contenttype = "image/" . strtolower($array[1]);
					}
					else
					{
						$contenttype = "image/jpeg";
					}
					$attach_contenttype[$i] = $contenttype;
					// Finder contentid
					$contentid = uniqid("") . "@" . uniqid("");
					$attach_contentid[$i] = $contentid;
					// Erstatter i HTML
					$body = str_replace($replace_this, $dummyid . "=\"cid:" . $contentid . "\"", $body);
					$has_inline = true;
				}
				// Erstatter dummyid med src
				$body = str_replace($dummyid, "src", $body);
				
				// background-image: url('')
				$dummyid = uniqid("IMAGEATTACH");
				while (eregi("background-image:[ ]*(url\([\"']{0,1}([^>^ ^\"^']+)[\"']{0,1}\))", $body, $array))
				{
					// Billede fundet
					$replace_this = $array[1];
					$i = count($attach);
					// Finder URL
					$url = $array[2];
					if (!eregi("^(http://|https://)", $url))
					{
						$url = ereg_replace("^/", "", $url);
						$url = "http://" . $_SERVER["SERVER_NAME"] . "/$url";
					}
					$attach[$i] = $url;
					// Finder contenttype
					$contenttype = "";
					if (eregi("\.([a-z]+)$", $url, $array))
					{
						$contenttype = "image/" . strtolower($array[1]);
					}
					else
					{
						$contenttype = "image/jpeg";
					}
					$attach_contenttype[$i] = $contenttype;
					// Finder contentid
					$contentid = uniqid("") . "@" . uniqid("");
					$attach_contentid[$i] = $contentid;
					// Erstatter i HTML
					$body = str_replace($replace_this, $dummyid . "('cid:" . $contentid . "')", $body);
					$has_inline = true;
				}
				// Erstatter dummyid med src
				$body = str_replace($dummyid, "url", $body);
			}
			else
			{
				// Finder billeder i HTML og erstatter relative URL
				
				// <img src="">
				$dummyid = uniqid("IMAGEATTACH");
				while (eregi("<img[^>]* (src=[\"']{0,1}([^>^ ^\"^']+)[\"']{0,1})[^>]*>", $body, $array))
				{
					// Billede fundet
					$replace_this = $array[1];
					$i = count($attach);
					// Finder URL
					$url = $array[2];
					if (!eregi("^(http://|https://)", $url))
					{
						$url = ereg_replace("^/", "", $url);
						$url = "http://" . $_SERVER["SERVER_NAME"] . "/$url";
					}
					
					// Erstatter i HTML
					$body = str_replace($replace_this, $dummyid . "=\"$url\"", $body);
					$has_inline = true;
				}
				// Erstatter dummyid med src
				$body = str_replace($dummyid, "src", $body);
				
				// background-image: url('')
				$dummyid = uniqid("IMAGEATTACH");
				while (eregi("background-image:[ ]*(url\([\"']{0,1}([^>^ ^\"^']+)[\"']{0,1}\))", $body, $array))
				{
					// Billede fundet
					$replace_this = $array[1];
					$i = count($attach);
					// Finder URL
					$url = $array[2];
					if (!eregi("^(http://|https://)", $url))
					{
						$url = ereg_replace("^/", "", $url);
						$url = "http://" . $_SERVER["SERVER_NAME"] . "/$url";
					}
					
					// Erstatter i HTML
					$body = str_replace($replace_this, $dummyid . "('$url')", $body);
					$has_inline = true;
				}
				// Erstatter dummyid med src
				$body = str_replace($dummyid, "url", $body);
			}
			
			// Retter relative urler
			if ($this->format == "html")
			{
				// Retter relative links
				$dummyid = uniqid("LINKFIX");
				while (eregi("<a ([^>^\[]+)>", $body, $array))
				{
					// Link fundet
					$replace_this = $array[0];
					
					// Finder URL
					if (eregi("href=([^>^ ]+)", $replace_this, $array))
					{
						// Finder URL
						$url = $array[1];
						$url = str_replace("\"", "", $url);
						$url = str_replace("'", "", $url);
						if (!eregi("^([a-z]+:.+)", $url))
						{
							$url = ereg_replace("^/", "", $url);
							$url = "http://" . $_SERVER["SERVER_NAME"] . "/$url";
						}
						$ahref = "href=\"$url\">";
					}
					else
					{
						$ahref = substr($replace_this, 3);
					}
					
					// Erstatter i HTML
					$body = str_replace($replace_this, $dummyid . $ahref, $body);
				}
				// Erstatter dummyid med <a
				$body = str_replace($dummyid, "<a ", $body);
			}
			
			// Laver boundary
			$boundary = "---myboundary-" . uniqid("");
			
			// Vedhæfter filer, hvis der er nogen
			$attachments = "";
			$attachments_inline = "";
			for ($i = 0; $i < count($attach); $i++)
			{
				// Åbner fil
				if ($fp = fopen($attach[$i], "r"))
				{
					// Læser fil
					$data = "";
					while (!feof($fp))
					{
						$data .= fread($fp, 1024);
					}
					fclose($fp);
					// Encoder data
					$data = chunk_split(base64_encode($data), 70);
					$data = trim($data);
					// Finder filnavn
					if ($attach_filename[$i] <> "")
					{
						$filename = $attach_filename[$i];
					}
					else
					{
						$filename = str_replace("/", "", substr($attach[$i], strrpos($attach[$i], "/")));
					}
					$filename = eregi_replace("\?.+$", "", $filename);
					$filename = eregi_replace("[^a-z^0-9^_^\.^-]", "", $filename);
					// Inline ?
					$is_inline = ($attach_contentid[$i] <> "" and $this->format == "html");
					// Indsætter data
					$tmp = new tpl("_email_attachment" . ($is_inline ? "_inline" : ""));
					$tmp->set("boundary", $boundary);
					$tmp->set("contenttype", $attach_contenttype[$i]);
					$tmp->set("contentid", $attach_contentid[$i]);
					$tmp->set("filename", $filename);
					$tmp->set("content", $data);
					if ($is_inline)
					{
						// Vedhæftet
						$attachments_inline .= $tmp->html();
					}
					else
					{
						// Inline
						$attachments .= $tmp->html();
					}
				}
			}
			$has_attachment = ($attachments <> "" or $attachments_inline <> "");
			
			// Tjekker for <!DOCTYPE> tag hvis HTML
			if ($this->format == "html")
			{
				if (strpos($body, "<!DOCTYPE ") === false)
				{
					$body = "<!DOCTYPE HTML>\r\n" .
						$body;
				}
			}
			
			// Laver encoding
			$subject_plain = $subject;
			$subject = $this->encode_subject($subject);
			if ($this->format == "html")
			{
				$body = $this->encode_html($body);
				$body_plain = $this->encode_qp($body_plain);
			}
			else
			{
				$body = $this->encode_qp($body);
			}
			
			// Laver mail
			$tmp = new tpl("_email_" . $this->format . ($has_attachment ? "_attachment" : ""));
			if ($this->receipt) $tmp->set("receipt", "Disposition-Notification-To: \"" . $this->from_name . "\" <" . $this->from_email . ">\r\n");
			$tmp->set("boundary", $boundary);
			$tmp->set("from_name", $this->from_name);
			$tmp->set("from_email", $this->from_email);
			$tmp->set("from_return_email", $this->from_return_email);
			$tmp->set("subject", $subject);
			$tmp->set("body", $body);
			$tmp->set("body_plain", $body_plain);
			$tmp->set("attachments", $attachments);
			$tmp->set("attachments_inline", $attachments_inline);
			$tmp->set("message_id", $this->message_id);
			$tmp->set("date", date("D, j M Y H:i:s O"));
			$content = $tmp->html();
			$content = str_replace("\r\n.\r\n", "\r\n..\r\n", $content);
			
			if ($this->use_smtp)
			{
				// SMTP
				
				// Forbinder til SMTP
				$fp = fsockopen($this->smtp_server, $this->smtp_port);
				if (!$fp)
				{
					$this->error = "Could not connect to SMTP-server " .
						$this->smtp_server . ":" . $this->smtp_port;
					return false;
				}
				
				// Venter på velkomst
				list($code, $res) = $this->fgets_smtp($fp);
				if ($code <> "220")
				{
					$this->error = "SMTP-server didn't welcome me ($res)";
					return false;
				}
				
				// Hilser på serveren
				fputs($fp, "HELO " . $_SERVER["SERVER_NAME"] . "\r\n");
				list($code, $res) = $this->fgets_smtp($fp);
				if ($code <> "250")
				{
					$this->error = "SMTP-server didn't accept my domain ($res)";
					return false;
				}
				
				// Logger evt. ind
				if ($this->smtp_user != "")
				{
					fputs($fp, "AUTH LOGIN\r\n");
					list($code, $res) = $this->fgets_smtp($fp);
					if ($code <> "334")
					{
						$this->error = "Could not AUTH with SMTP-server ($res)";
						return false;
					}
					// Sender brugernavn
					fputs($fp, base64_encode($this->smtp_user) . "\r\n");
					list($code, $res) = $this->fgets_smtp($fp);
					if ($code <> "334")
					{
						$this->error = "Could not AUTH with SMTP-server ($res)";
						return false;
					}
					// Sender password
					fputs($fp, base64_encode($this->smtp_pass) . "\r\n");
					list($code, $res) = $this->fgets_smtp($fp);
					if ($code <> "235")
					{
						$this->error = "Could not AUTH with SMTP-server ($res)";
						return false;
					}
				}

				// Genmemløber modtagere
				$this->error = "";
				for ($i = 0; $i < count($this->to_name); $i++)
				{
					// Sender mail
					fputs($fp, "MAIL FROM: <" . $this->from_return_email . ">\r\n");
					list($code, $res) = $this->fgets_smtp($fp);
					if ($code <> "250")
					{
						if ($res == "") $res = "Sender not accepted";
						$this->error .= $this->to_email[$i] . ": $res\r\n";
					}
					else
					{
						fputs($fp, "RCPT TO: <" . $this->to_email[$i] . ">\r\n");
						list($code, $res) = $this->fgets_smtp($fp);
						if ($code <> "250")
						{
							if ($res == "") $res = "Reciever not accepted";
							$this->error .= $this->to_email[$i] . ": $res\r\n";
							fputs($fp, "RSET\r\n");
							list($code, $res) = $this->fgets_smtp($fp);
						}
						else
						{
							fputs($fp, "DATA\r\n");
							list($code, $res) = $this->fgets_smtp($fp);
							if ($code <> "354")
							{
								if ($res == "") $res = "Could not deliver mail data";
								$this->error .= $this->to_email[$i] . ": $res\r\n";
							}
							else
							{
								// Sender mail data
								if ($this->to_name[$i] != "" and $this->to_name[$i] != $this->to_email[$i])
								{
									$tmpname = "\"" . $this->to_name[$i] . "\" ";
								}
								else
								{
									$tmpname = "";
								}
								
								$tmpsubject = $subject;
								$tmpsubject = str_replace("[EMAIL]", $this->to_email[$i], $tmpsubject);
								$tmpsubject = str_replace("[NAME]", $this->to_name[$i], $tmpsubject);
								$tmpcontent = $content;
								$tmpcontent = str_replace("[EMAIL]", $this->to_email[$i], $tmpcontent);
								$tmpcontent = str_replace("[NAME]", $this->to_name[$i], $tmpcontent);
								$tmpcontent = str_replace("[SUBJECT]", $subject_plain, $tmpcontent);
								reset($this->to_vars[$i]);
								while (list($tmpkey, $tmpvalue) = each($this->to_vars[$i]))
								{
									$tmpsubject = str_replace("[" . $tmpkey . "]", $tmpvalue, $tmpsubject);
									$tmpcontent = str_replace("[" . $tmpkey . "]", $tmpvalue, $tmpcontent);
								}
								
								fputs($fp, "To: " . $tmpname . "<" . $this->to_email[$i] . ">\r\n" .
									"Subject: $subject\r\n" .
									"$tmpcontent\r\n" .
									".\r\n");
								list($code, $res) = $this->fgets_smtp($fp);
								if ($code <> "250")
								{
									if ($res == "") $res = "Mail data not accepted";
									$this->error .= $this->to_email[$i] . ": $res\r\n";
								}
							}
						}
					}
				}
				
				// Siger farvel
				fputs($fp, "QUIT\r\n");
				
				// Lukker forbindelse
				fclose($fp);
			}
			else
			{
				// PHP mail()
				
				// Fjerner \r
				$content = str_replace("\r", "", $content);

				// Finder headers
				$pos = strpos($content, "\n\n");
				$headers = substr($content, 0, $pos);
				$body = substr($content, $pos + 2);
				
				// Gennemløber modtagere				
				$this->error = "";
				for ($i = 0; $i < count($this->to_name); $i++)
				{
					$tmpsubject = $subject;
					$tmpsubject = str_replace("[EMAIL]", $this->to_email[$i], $tmpsubject);
					$tmpsubject = str_replace("[NAME]", $this->to_name[$i], $tmpsubject);
					$tmpcontent = $body;
					$tmpcontent = str_replace("[EMAIL]", $this->to_email[$i], $tmpcontent);
					$tmpcontent = str_replace("[NAME]", $this->to_name[$i], $tmpcontent);
					$tmpcontent = str_replace("[SUBJECT]", $subject_plain, $tmpcontent);
					reset($this->to_vars[$i]);
					while (list($tmpkey, $tmpvalue) = each($this->to_vars[$i]))
					{
						$tmpsubject = str_replace("[" . $tmpkey . "]", $tmpvalue, $tmpsubject);
						$tmpcontent = str_replace("[" . $tmpkey . "]", $tmpvalue, $tmpcontent);
					}
					
					if (!mail("\"" . $this->to_name[$i] . "\" <" . $this->to_email[$i] . ">", $tmpsubject, $tmpcontent, $headers))
					{
						$this->error .= $this->to_email[$i] . ": Error while sending using PHP mail()\r\n";
					}
				}
			}

			// Tilføjer evt. til log			
			if ($this->error <> "")
			{
				add_log_message($this->error);
			}
						
			// Sletter modtager-listen
			$this->to_name = array();
			$this->to_email = array();
			
			// Retur
			return ($this->error == "");
		}
		
		// Henter svar fra SMTP-server
		function fgets_smtp($fp, $timeout = 3)
		{
			// Venter på init
			$timeout += time();
			$line = "";
			while (!feof($fp))
			{
				$line .= fgets($fp, 1024);
				if (eregi("[^0-9]{0,1}([0-9]+) (.*)\n", $line, $array))
				{
					// Retur
					return array($array[1], trim($array[2]));
				}
				if ($timeout < time()) return array(false, false);
			}
			
			// Retur
			return array(false, false);
		}
	}
?>