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

	$updates = array();
		
		// Automatisk opdatering, genereret 06-01-2009 15:12:33
		$updates["2009010601"]["text"] = "Tilføjet backup-funktion, der tager backup af alt med undtagelse af php-filer";
		$updates["2009010601"]["update"] = array(
					"FILE|Admin/pages/backup.php",
					"FILE|Admin/pages/menu.php",
					"FILE|class/mysqldump.php",
		"");
		
		// Automatisk opdatering, genereret 15-01-2009 10:21:12
		$updates["2009011500"]["text"] = "Tilføjet:<br>- simpel / avanceret redigering af indhold i menu<br>- mulighed for opkobling til sms gateway";
		$updates["2009011500"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/pages.php",
					"FILE|Admin/pages/settings.php",
					"FILE|class/sms.php",
					"FILE|class/tpl.php",
					"FILE|inc/functions.php",
					"FILE|lang/lang.php",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE
		" . $_table_prefix . "_pages_
	ADD
		`edit_mode` VARCHAR( 10 ) NOT NULL DEFAULT \'simple\'
	");'),
		"");
		
		// Automatisk opdatering, genereret 15-01-2009 13:12:33
		$updates["2009011502"]["text"] = "Rettelse af fejl ved frameset i admin";
		$updates["2009011502"]["update"] = array(
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 15-01-2009 13:22:58
		$updates["2009011503"]["text"] = "Rettelse af fejl i formular klasse";
		$updates["2009011503"]["update"] = array(
					"FILE|html/_form_confirm.html",
					"FILE|html/_form_confirm_false.html",
					"FILE|html/_form_confirm_file.html",
					"FILE|html/_form_confirm_image.html",
					"FILE|html/_form_confirm_password.html",
					"FILE|html/_form_confirm_true.html",
		"");
		
		// Automatisk opdatering, genereret 21-01-2009 21:01:18
		$updates["2009012100"]["text"] = "Rettelse af fejl i SPAW editor `Interne links` hvis disse indeholder apostroffer eller anførselstegn";
		$updates["2009012100"]["update"] = array(
					"FILE|Admin/pages/js_links.php",
		"");
		
		// Manuel oprettet
		$updates["2009012102"]["text"] = "Rettelse af fejl ved frameset i admin";
		$updates["2009012102"]["update"] = array(
					"FILE|html/admin_layout_frameset.html",
					"FILE|Admin/pages/frameset.php",
		"");
		
		
		
		// Automatisk opdatering, genereret 23-01-2009 17:03:09
		$updates["2009012301"]["text"] = "Opdatering af mysqldump klasse";
		$updates["2009012301"]["update"] = array(
					"FILE|class/mysqldump.php",
		"");
		
		// Automatisk opdatering, genereret 24-01-2009 12:43:17
		$updates["2009012401"]["text"] = "Opdatering af formular klasse";
		$updates["2009012401"]["update"] = array(
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 27-01-2009 14:29:01
		$updates["2009012700"]["text"] = "Rettelse af fejl i formular klasse";
		$updates["2009012700"]["update"] = array(
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 27-01-2009 14:41:28
		$updates["2009012701"]["text"] = "Rettelse af fejl i formular klasse";
		$updates["2009012701"]["update"] = array(
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 30-01-2009 13:44:10
		$updates["2009013000"]["text"] = "Tilføjet mulighed for at deaktivere admin-brugers adgang til at oprette / ændre / slette sprog";
		$updates["2009013000"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/languages.php",
		"");
		
		// Automatisk opdatering, genereret 26-02-2009 15:35:13
		$updates["2009022600"]["text"] = "Opdatering af backup/genskab system";
		$updates["2009022600"]["update"] = array(
					"FILE|Admin/pages/backup.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 03-03-2009 14:31:24
		$updates["2009030300"]["text"] = "Rettelse af fejl i SMS klasse";
		$updates["2009030300"]["update"] = array(
					"FILE|class/sms.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 09-03-2009 10:27:39
		$updates["2009030900"]["text"] = "Opdatering af SQL";
		$updates["2009030900"]["update"] = array(
					"FILE|Admin/pages/sql.php",
		"");
		
		// Automatisk opdatering, genereret 27-03-2009 15:10:51
		$updates["2009032700"]["text"] = "Mulighed for at angive retur e-mail og opfange alle bouncede mails";
		$updates["2009032700"]["update"] = array(
					"FOLDER|js/jquery",
					"FILE|Admin/install.php",
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/keep_alive.php",
					"FILE|Admin/pages/settings.php",
					"FILE|class/email.php",
					"FILE|class/pop3.php",
					"FILE|class/tpl.php",
					"FILE|html/_email_html.html",
					"FILE|html/_email_html_attachment.html",
					"FILE|html/_email_plain.html",
					"FILE|html/_email_plain_attachment.html",
					"FILE|js/jquery/jquery.js",
			"EVAL|" . base64_encode('	$db->execute("
		CREATE TABLE  `" . $_table_prefix . "_return_mail` (
		 `idauto` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		 `time` DATETIME NOT NULL ,
		 `lang_id` VARCHAR( 2 ) NOT NULL ,
		 `module` VARCHAR( 50 ) NOT NULL ,
		 `page` VARCHAR( 50 ) NOT NULL ,
		 `do` VARCHAR( 50 ) NOT NULL ,
		 `id` INT( 10 ) UNSIGNED NOT NULL ,
		 `message_id` VARCHAR( 50 ) NOT NULL,
		 `email` VARCHAR( 50 ) NOT NULL
		)
		");

'),
		"");
		
		// Automatisk opdatering, genereret 30-03-2009 13:25:14
		$updates["2009033000"]["text"] = "Rettelser til return mail funktion";
		$updates["2009033000"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 01-04-2009 09:36:18
		$updates["2009040100"]["text"] = "Opdatering af retur-mail bot";
		$updates["2009040100"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/email.php",
					"FILE|js/spaw/config/config.php",
		"");
		
		// Automatisk opdatering, genereret 01-04-2009 10:25:21
		$updates["2009040101"]["text"] = "Opdatering af e-mail klasse - tilføjelse af SMTP AUTH";
		$updates["2009040101"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 01-04-2009 10:49:01
		$updates["2009040102"]["text"] = "Opdatering af retur mail klasse";
		$updates["2009040102"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 22-04-2009 12:17:23
		$updates["2009042200"]["text"] = "Rettet så parametren `tpl` har prioritet over modulvalgt template";
		$updates["2009042200"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/file.php",
					"FILE|class/pop3.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 01-05-2009 14:36:51
		$updates["2009050100"]["text"] = "Ændret SPAW2 så &lt;P&gt; erstattes med &lt;BR&gt;";
		$updates["2009050100"]["update"] = array(
					"FILE|class/db.php",
					"FILE|js/spaw/js/common/editor.js",
		"");
		
		// Automatisk opdatering, genereret 04-05-2009 14:22:49
		$updates["2009050400"]["text"] = "Opdatering af bruger-klasse, så der understøttes bruger specifikke regler";
		$updates["2009050400"]["update"] = array(
					"FILE|class/user.php",
			"EVAL|" . base64_encode('
		$usrobj = new user("admin");
		$usrarray = $usrobj->admin_get_groups();
		$usrarray[count($usrarray)] = "admin";
		for ($usri = 0; $usri < count($usrarray); $usri++)
		{
			$db->execute("
				ALTER TABLE
					" . $_table_prefix . "_user_" . $usrarray[$usri] . "
				ADD
					`rules` VARCHAR(250) NOT NULL
				");
		}
	'),
		"");
		
		// Automatisk opdatering, genereret 07-05-2009 09:14:20
		$updates["2009050700"]["text"] = "Opdatering af SPAW så &gt;strong&lt; og &gt;span&lt; erstattes";
		$updates["2009050700"]["update"] = array(
					"FILE|js/spaw/js/common/editor.js",
		"");
		
		// Automatisk opdatering, genereret 15-05-2009 11:06:44
		$updates["2009051500"]["text"] = "Opdatering af bruger-klasse regler samt tilføjelse af regel-editor";
		$updates["2009051500"]["update"] = array(
					"FILE|Admin/pages/rule_editor.php",
					"FILE|class/form.php",
					"FILE|class/user.php",
					"FILE|html/_form_textarea_popup_rules.html",
					"FILE|js/admin.js",
		"");
		
		// Automatisk opdatering, genereret 15-05-2009 12:33:50
		$updates["2009051501"]["text"] = "Mulighed for at angivne regler for admin brugere";
		$updates["2009051501"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/rule_editor.php",
					"FILE|class/form.php",
					"FILE|class/user.php",
					"FILE|html/_form_textarea_popup_rules.html",
		"");
		
		// Automatisk opdatering, genereret 26-05-2009 14:07:15
		$updates["2009052600"]["text"] = "Tilføjet admin-rettighed til admin-brugere";
		$updates["2009052600"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
		"");
		
		// Automatisk opdatering, genereret 26-05-2009 14:42:48
		$updates["2009052601"]["text"] = "Rettelse af rettigheder til admin-brugere";
		$updates["2009052601"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
		"");
		
		// Automatisk opdatering, genereret 28-05-2009 10:06:00
		$updates["2009052800"]["text"] = "Tilføjet mulighed for alm admin-bruger at tildele rettigheder til menu + indstillinger<br>Tilføjet søgning efter keywords fra menu og moduler";
		$updates["2009052800"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
					"FILE|index.php",
					"FILE|pages/default.php",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE " . $_table_prefix . "_pages_
	ADD meta_keywords text not null default \'\'
	");'),
		"");
		
		// Automatisk opdatering, genereret 29-05-2009 09:26:32
		$updates["2009052900"]["text"] = "Tilføjet mulighed for at give admin-brug rettighed til backup/genskab";
		$updates["2009052900"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
		"");
		
		// Automatisk opdatering, genereret 29-05-2009 10:13:16
		$updates["2009052901"]["text"] = "Rettelse af fejl i formular-klasse i forbindelse med wysiwyg-popup";
		$updates["2009052901"]["update"] = array(
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 29-05-2009 15:25:47
		$updates["2009052902"]["text"] = "Tilføjelse af `Vis i menu` til menu-system";
		$updates["2009052902"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
					"FILE|pages/default.php",
			"EVAL|" . base64_encode('	$db->execute("
		ALTER TABLE " . $_table_prefix . "_pages_ 
		ADD `public` tinyint(1) unsigned NOT NULL default \'1\'
		");
'),
		"");
		
		// Automatisk opdatering, genereret 02-06-2009 11:09:15
		$updates["2009060200"]["text"] = "Tilføjet ubegrænset antal menu-niveauer";
		$updates["2009060200"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 02-06-2009 11:48:56
		$updates["2009060201"]["text"] = "Rettelse af SPAW så P erstattes med DIV";
		$updates["2009060201"]["update"] = array(
					"FILE|js/spaw/js/common/editor.js",
		"");
		
		// Automatisk opdatering, genereret 02-06-2009 12:02:17
		$updates["2009060202"]["text"] = "Rettelse så under-menu til under-menu indsættes via taget {<!>SUB_MENU}";
		$updates["2009060202"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 02-06-2009 13:20:12
		$updates["2009060203"]["text"] = "Rettelse af fejl i PAGE|menu_sub";
		$updates["2009060203"]["update"] = array(
					"FILE|pages/menu_sub.php",
		"");
		
		// Automatisk opdatering, genereret 02-06-2009 13:21:01
		$updates["2009060204"]["text"] = "Rettelse af fejl i PAGE|menu_sub";
		$updates["2009060204"]["update"] = array(
					"FILE|pages/menu_sub.php",
		"");
		
		// Automatisk opdatering, genereret 09-06-2009 19:20:26
		$updates["2009060900"]["text"] = "Fejl rettelse i forbindelse med sub-menu";
		$updates["2009060900"]["update"] = array(
					"FOLDER|js/stadel",
					"FILE|inc/functions.php",
					"FILE|js/stadel/spreadsheet.js",
		"");
		
		// Automatisk opdatering, genereret 11-06-2009 15:59:23
		$updates["2009061100"]["text"] = "Opdatering af javascript til admin";
		$updates["2009061100"]["update"] = array(
					"FILE|js/stadel/spreadsheet.js",
		"");
		
		// Automatisk opdatering, genereret 17-06-2009 14:18:30
		$updates["2009061700"]["text"] = "Opdatering af javascript-klasse";
		$updates["2009061700"]["update"] = array(
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 17-06-2009 14:21:27
		$updates["2009061701"]["text"] = "Opdatering af javascript-klasse";
		$updates["2009061701"]["update"] = array(
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 24-06-2009 09:01:46
		$updates["2009062400"]["text"] = "Opdatering af formular-klasse";
		$updates["2009062400"]["update"] = array(
					"FILE|html/_form_tab_div_header.html",
					"FILE|html/_form_tab_header_1.html",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 25-06-2009 13:46:27
		$updates["2009062500"]["text"] = "Rettelse af e-mail klasse";
		$updates["2009062500"]["update"] = array(
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 26-06-2009 11:54:18
		$updates["2009062600"]["text"] = "Opdatering af funktionsklasse og formularklasse";
		$updates["2009062600"]["update"] = array(
					"FILE|html/_form_checkbox.html",
					"FILE|html/_form_checkbox_checked.html",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 03-07-2009 09:46:32
		$updates["2009070300"]["text"] = "Mulighed for at definere size for input felter";
		$updates["2009070300"]["update"] = array(
					"FILE|class/form.php",
					"FILE|html/_form_input.html",
					"FILE|html/_form_password.html",
		"");
		
		// Automatisk opdatering, genereret 07-07-2009 10:32:15
		$updates["2009070700"]["text"] = "- automatisk viderestilling til smart-urls, hvis disse eksisterer<br>- mulighed for at definere om ikke publicerede menu skal indgå i sitemap";
		$updates["2009070700"]["update"] = array(
					"FILE|Admin/pages/languages.php",
					"FILE|Admin/pages/settings.php",
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 07-07-2009 13:17:57
		$updates["2009070701"]["text"] = "Rettelse så menu-punkter ikke vises med &amp;nbsp;-indrykninger i sitemap";
		$updates["2009070701"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 03-08-2009 14:53:51
		$updates["2009080300"]["text"] = "Rettelse af fejl når søgeord gemmes i menu";
		$updates["2009080300"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 06-08-2009 10:18:31
		$updates["2009080600"]["text"] = "Rettelse til smart-url viderestilling";
		$updates["2009080600"]["update"] = array(
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 13-08-2009 13:38:17
		$updates["2009081300"]["text"] = "Fejl ved rettigheder til regeleditor";
		$updates["2009081300"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 24-08-2009 09:24:58
		$updates["2009082400"]["text"] = "Rettelse i template-klasse så MENU_ variabler sættes korrekt";
		$updates["2009082400"]["update"] = array(
					"FILE|class/tpl.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 02-09-2009 12:50:58
		$updates["2009090200"]["text"] = "Mulighed for at upload egne ikoner til admin genveje";
		$updates["2009090200"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/default.php",
					"FILE|html/admin_default_element.html",
					"FILE|html/admin_default_links_icons_element.html",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 02-09-2009 17:08:20
		$updates["2009090201"]["text"] = "Tilføjet CSS editor samt global CSS, der inkluderer CSS fra alle moduler";
		$updates["2009090201"]["update"] = array(
					"FILE|Admin/pages/css.php",
					"FILE|Admin/pages/css_editor.php",
					"FILE|Admin/pages/css_editor_types.php",
					"FILE|Admin/pages/file_editor.php",
					"FILE|Admin/pages/menu.php",
					"FILE|css.php",
					"FILE|html/admin_css_editor.html",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 02-09-2009 17:15:45
		$updates["2009090202"]["text"] = "Rettelse til CSS editor";
		$updates["2009090202"]["update"] = array(
					"FILE|Admin/pages/css_editor.php",
		"");
		
		// Automatisk opdatering, genereret 03-09-2009 12:55:20
		$updates["2009090300"]["text"] = "Rettelse til CSS system";
		$updates["2009090300"]["update"] = array(
					"FILE|Admin/pages/css_editor.php",
					"FILE|css.php",
		"");
		
		// Automatisk opdatering, genereret 08-09-2009 13:10:40
		$updates["2009090800"]["text"] = "Opdatering af e-mail klasse";
		$updates["2009090800"]["update"] = array(
					"FILE|class/email.php",
					"FILE|html/admin_layout_login.html",
		"");
		
		// Automatisk opdatering, genereret 11-09-2009 14:26:43
		$updates["2009091100"]["text"] = "Mulighed for manuel sortering af admin menu grupper";
		$updates["2009091100"]["update"] = array(
					"FILE|Admin/pages/menu.php",
					"FILE|class/ajax.php",
					"FILE|html/_ajax_script.html",
					"FILE|html/admin_layout_menu.html",
					"FILE|html/admin_menu_headline.html",
					"FILE|html/admin_menu_headline_visible.html",
					"FILE|js/admin.js",
					"FILE|js/jquery/jquery.js",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE
		" . $_table_prefix . "_user_admin
	ADD
		extra_admin_menu_order TEXT
	");'),
		"");
		
		// Automatisk opdatering, genereret 14-09-2009 08:58:26
		$updates["2009091400"]["text"] = "Rettet så sub-menu kan ændres til top-menu";
		$updates["2009091400"]["update"] = array(
					"FILE|Admin/pages/pages.php",
		"");
		
		// Automatisk opdatering, genereret 14-09-2009 10:19:14
		$updates["2009091401"]["text"] = "Tilføjer scrollbar til popup i admin";
		$updates["2009091401"]["update"] = array(
					"FILE|html/_form_textarea_popup_rules.html",
					"FILE|html/_form_textarea_popup_wysiwyg.html",
		"");
		
		// Automatisk opdatering, genereret 14-09-2009 14:00:25
		$updates["2009091402"]["text"] = "Tilføjet drag-n-drop til flytning af menupunkter";
		$updates["2009091402"]["update"] = array(
					"FILE|Admin/pages/menu.php",
					"FILE|Admin/pages/pages.php",
					"FILE|class/table.php",
					"FILE|html/_table_footer.html",
					"FILE|html/_table_header.html",
					"FILE|html/_table_row_footer.html",
					"FILE|html/_table_row_header.html",
					"FILE|html/_table_th.html",
					"FILE|js/jquery/tablednd.js",
		"");
		
		// Automatisk opdatering, genereret 15-09-2009 11:42:53
		$updates["2009091500"]["text"] = "Tilføjelse af timeout på retur mail";
		$updates["2009091500"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 22-09-2009 15:23:52
		$updates["2009092200"]["text"] = "Opdatering af bruger klasse";
		$updates["2009092200"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 23-09-2009 09:33:58
		$updates["2009092300"]["text"] = "Opdatering af template klasse - mulighed for at indsætte variabler via tag f.eks. {<!>VAR|variabelnavn}";
		$updates["2009092300"]["update"] = array(
					"FILE|Admin/pages/menu.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 23-09-2009 15:10:01
		$updates["2009092301"]["text"] = "Rettelse af brugerklasse";
		$updates["2009092301"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 29-09-2009 14:21:49
		$updates["2009092900"]["text"] = "Opdatering af SPAW editor, så <p> ikke erstattes med <div>";
		$updates["2009092900"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|class/user.php",
					"FILE|js/spaw/js/common/editor.js",
		"");
		
		// Automatisk opdatering, genereret 06-10-2009 09:38:48
		$updates["2009100600"]["text"] = "Rettelse af variabel {<!>MENU_TOP_TITLE}";
		$updates["2009100600"]["update"] = array(
					"FILE|class/form.php",
					"FILE|html/_form_image_example.html",
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 20-10-2009 14:47:00
		$updates["2009102000"]["text"] = "Opdatering så det er muligt at vælge om billeder skal vedhæftes til e-mail";
		$updates["2009102000"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/settings.php",
					"FILE|class/convert.php",
					"FILE|class/email.php",
					"FILE|class/settings.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 11:35:47
		$updates["2009102100"]["text"] = "Rettelse af return mail bot så den viser header fra mail i system meddelelser";
		$updates["2009102100"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 11:56:18
		$updates["2009102101"]["text"] = "Rettelse af fejl i retur mail bot";
		$updates["2009102101"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 11:59:02
		$updates["2009102102"]["text"] = "Rettelse af fejl i retur mail bot";
		$updates["2009102102"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 12:23:38
		$updates["2009102103"]["text"] = "Opdatering af retur mail bot og e-mail klasse";
		$updates["2009102103"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/pop3.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 12:59:24
		$updates["2009102104"]["text"] = "Opdatering af retur mail bot og pop3 klasse";
		$updates["2009102104"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/pop3.php",
		"");
		
		// Automatisk opdatering, genereret 21-10-2009 13:07:09
		$updates["2009102105"]["text"] = "Rettelse af timeout i pop3 klasse";
		$updates["2009102105"]["update"] = array(
					"FILE|class/pop3.php",
		"");
		
		// Automatisk opdatering, genereret 26-10-2009 11:57:45
		$updates["2009102600"]["text"] = "Rettelse af install fil";
		$updates["2009102600"]["update"] = array(
					"FILE|Admin/install.php",
		"");
		
		// Automatisk opdatering, genereret 04-11-2009 11:43:33
		$updates["2009110400"]["text"] = "Mulighed for at opdaterer smart url for menu-punkter + tilføjet visning af smart-url ved redigering af menupunkt";
		$updates["2009110400"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|class/form.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 04-11-2009 11:54:45
		$updates["2009110401"]["text"] = "Rettelse af convert klasse";
		$updates["2009110401"]["update"] = array(
					"FILE|class/convert.php",
		"");
		
		// Automatisk opdatering, genereret 06-11-2009 11:40:14
		$updates["2009110600"]["text"] = "Rettelse i forbindelse med retur mail";
		$updates["2009110600"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 18-11-2009 12:12:35
		$updates["2009111800"]["text"] = "Visning af cron job URL under indstillinger";
		$updates["2009111800"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/settings.php",
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 19-11-2009 10:28:31
		$updates["2009111900"]["text"] = "Opdatering af bruger klasse for registrering af brugers sprog";
		$updates["2009111900"]["update"] = array(
					"FILE|class/user.php",
			"EVAL|" . base64_encode('$usrobj = new user;
$array = $usrobj->admin_get_groups();
$array[count($array)] = "admin";
for ($usri = 0; $usri < count($array); $usri++)
{
	$db->execute("
		ALTER TABLE
			" . $_table_prefix . "_user_" . $array[$usri] . "
		ADD
			lang_id VARCHAR(2) NOT NULL DEFAULT \'da\'
		");
}'),
		"");
		
		// Automatisk opdatering, genereret 19-11-2009 14:13:16
		$updates["2009111901"]["text"] = "Mulighed for at angive at menupunkt skal vises i linkoversigt";
		$updates["2009111901"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|Admin/install.php",
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/file_editor.php",
					"FILE|Admin/pages/pages.php",
					"FILE|class/pop3.php",
					"FILE|inc/functions.php",
					"FILE|pages/menu_link.php",
			"EVAL|" . base64_encode('	$db->execute("
		ALTER TABLE " . $_table_prefix . "_pages_ 
		ADD
		  `link` tinyint(1) unsigned NOT NULL default \'0\'
		 ");
'),
		"");
		
		// Automatisk opdatering, genereret 24-11-2009 11:03:01
		$updates["2009112400"]["text"] = "Rettelse af AJAX klasse";
		$updates["2009112400"]["update"] = array(
					"FILE|class/ajax.php",
					"FILE|class/convert.php",
					"FILE|html/_ajax_script.html",
		"");
		
		// Automatisk opdatering, genereret 24-11-2009 13:51:20
		$updates["2009112401"]["text"] = "Tilføjet mulighed for at definere antal sidetal for paging-klasse";
		$updates["2009112401"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/paging.php",
					"FILE|html/_paging_footer.html",
					"FILE|html/_paging_header.html",
					"FILE|lang/lang.php",
		"");
		
		// Automatisk opdatering, genereret 24-11-2009 14:14:05
		$updates["2009112402"]["text"] = "Mulighed for at definere under-titel til menu-punkter (indsættes via {<!>SUB_TITLE} i menu-filerne)";
		$updates["2009112402"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
					"FILE|lang/lang.php",
			"EVAL|" . base64_encode('	$db->execute("
		ALTER TABLE " . $_table_prefix . "_pages_
		ADD sub_title varchar(255) NOT NULL default \'\'
		");'),
		"");
		
		// Automatisk opdatering, genereret 26-11-2009 09:40:27
		$updates["2009112600"]["text"] = "Rettelse af admin ved setcookie()";
		$updates["2009112600"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|Admin/pages/menu.php",
		"");
		
		// Automatisk opdatering, genereret 30-11-2009 11:51:04
		$updates["2009113000"]["text"] = "Tilføjet cache-mulighed via parameteren cache, samt tjek for menu_sub_last_level.html";
		$updates["2009113000"]["update"] = array(
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 07-12-2009 09:08:43
		$updates["2009120700"]["text"] = "Rettelse af ftp klasse";
		$updates["2009120700"]["update"] = array(
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 07-12-2009 10:22:53
		$updates["2009120701"]["text"] = "Rettelse af ftp klasse";
		$updates["2009120701"]["update"] = array(
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 07-12-2009 10:24:45
		$updates["2009120702"]["text"] = "Rettelse af ftp klasse";
		$updates["2009120702"]["update"] = array(
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 09-12-2009 09:44:23
		$updates["2009120900"]["text"] = "Rettelse af linkmenu så den vises i samme struktur som alm. menu";
		$updates["2009120900"]["update"] = array(
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/modules.php",
					"FILE|html/_ajax_script.html",
					"FILE|inc/functions.php",
					"FILE|pages/menu_link.php",
		"");
		
		// Automatisk opdatering, genereret 10-12-2009 14:32:05
		$updates["2009121002"]["text"] = "Opdatering af spreadsheet.js";
		$updates["2009121002"]["update"] = array(
					"FILE|Admin/pages/default.php",
					"FILE|Admin/pages/login.php",
					"FILE|class/user.php",
					"FILE|js/stadel/spreadsheet.js",
					"FILE|class/form.php",
		"");

		// Automatisk opdatering, genereret 14-12-2009 11:22:30
		$updates["2009121400"]["text"] = "Rettelse af fejl i formular-klasse";
		$updates["2009121400"]["update"] = array(
					"FILE|class/csv.php",
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 15-12-2009 11:56:57
		$updates["2009121500"]["text"] = "Tilføjet visning af log for adminbrugere";
		$updates["2009121500"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
		"");
		
		// Automatisk opdatering, genereret 05-01-2010 13:26:51
		$updates["2010010505"]["text"] = "Tilføjet mulighed for at definere file mode for skrivbare mapper og filer ved opdatering af cms og moduler";
		$updates["2010010505"]["update"] = array(
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/layouts.php",
					"FILE|Admin/pages/modules.php",
					"FILE|Admin/pages/settings.php",
					"FILE|js/stadel/spreadsheet.js",
					"FILE|Admin/install.php",
			"EVAL|" . base64_encode('cms_setting("writable_file_mode", "0777");'),
		"");
		
		// Automatisk opdatering, genereret 05-01-2010 14:55:49
		$updates["2010010505"]["text"] = "Rettelse af mappe rettighed";
		$updates["2010010505"]["update"] = array(
					"FILE|Admin/pages/settings.php",
		"");
		
		// Automatisk opdatering, genereret 05-01-2010 14:58:03
		$updates["2010010506"]["text"] = "Rettelse af mappe rettighed";
		$updates["2010010506"]["update"] = array(
					"FILE|Admin/install.php",
			"EVAL|" . base64_encode('	cms_setting("writable_file_mode", "0777");
	cms_setting("default_file_mode", "0644");
	cms_setting("default_folder_mode", "0755");
'),
		"");
		
		// Automatisk opdatering, genereret 06-01-2010 09:29:00
		$updates["2010010600"]["text"] = "Opdatering af fil klasse";
		$updates["2010010600"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|class/file.php",
		"");
		
		// Automatisk opdatering, genereret 06-01-2010 10:18:15
		$updates["2010010601"]["text"] = "Rettelse af fil klasse";
		$updates["2010010601"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/file.php",
		"");
		
		// Automatisk opdatering, genereret 08-01-2010 23:22:35
		$updates["2010010800"]["text"] = "Opdatering af bruger klasse";
		$updates["2010010800"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 20-01-2010 05:59:23
		$updates["2010012000"]["text"] = "Rettelse af fil rettigheder";
		$updates["2010012000"]["update"] = array(
					"FILE|Admin/pages/settings.php",
		"");
		
		// Automatisk opdatering, genereret 20-01-2010 06:56:05
		$updates["2010012001"]["text"] = "Rettelse af install fil";
		$updates["2010012001"]["update"] = array(
					"FILE|Admin/install.php",
		"");
		
		// Automatisk opdatering, genereret 21-01-2010 09:37:13
		$updates["2010012100"]["text"] = "Rettelse af fil rettigheder";
		$updates["2010012100"]["update"] = array(
					"FILE|Admin/pages/settings.php",
		"");
		
		// Automatisk opdatering, genereret 21-01-2010 12:26:48
		$updates["2010012101"]["text"] = "Rettelse af fil rettigheder";
		$updates["2010012101"]["update"] = array(
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 27-01-2010 10:46:45
		$updates["2010012700"]["text"] = "Rettelse af css.php i layout";
		$updates["2010012700"]["update"] = array(
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 27-01-2010 10:54:24
		$updates["2010012701"]["text"] = "Rettelse af index.php";
		$updates["2010012701"]["update"] = array(
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 28-01-2010 04:19:02
		$updates["2010012800"]["text"] = "Opdatering af convert klasse";
		$updates["2010012800"]["update"] = array(
					"FILE|class/convert.php",
		"");
		
		// Automatisk opdatering, genereret 22-02-2010 10:13:15
		$updates["2010022200"]["text"] = "Tilføjelse af 404 fejlside så layoutfilen 404.html benyttes";
		$updates["2010022200"]["update"] = array(
					"FILE|Admin/pages/default.php",
					"FILE|Admin/pages/js_links.php",
					"FILE|Admin/pages/settings.php",
					"FILE|index.php",
					"FILE|pages/404.php",
		"");
		
		// Automatisk opdatering, genereret 23-02-2010 09:56:10
		$updates["2010022300"]["text"] = "Rettelse til link menu";
		$updates["2010022300"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 23-02-2010 21:51:11
		$updates["2010022301"]["text"] = "Tilføjelse af {<!>BREADCRUMB} til site-layout";
		$updates["2010022301"]["update"] = array(
					"FILE|html/_ajax_script.html",
					"FILE|inc/functions.php",
					"FILE|index.php",
					"FILE|pages/404.php",
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 23-02-2010 21:55:35
		$updates["2010022302"]["text"] = "Opdatering af brødkrummer";
		$updates["2010022302"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 23-02-2010 22:18:03
		$updates["2010022303"]["text"] = "Opdatering af brødkrummer";
		$updates["2010022303"]["update"] = array(
					"FILE|inc/functions.php",
					"FILE|pages/404.php",
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 25-02-2010 13:17:28
		$updates["2010022500"]["text"] = "Opdatering af smart url";
		$updates["2010022500"]["update"] = array(
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 26-02-2010 09:43:19
		$updates["2010022600"]["text"] = "Rettelse af popup wysiwyg";
		$updates["2010022600"]["update"] = array(
					"FILE|class/form.php",
					"FILE|html/_form_textarea_popup_wysiwyg.html",
		"");
		
		// Automatisk opdatering, genereret 02-03-2010 09:56:48
		$updates["2010030200"]["text"] = "Rettelse af menu visning";
		$updates["2010030200"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 03-03-2010 09:25:02
		$updates["2010030300"]["text"] = "Opdatering af formular-klasse";
		$updates["2010030300"]["update"] = array(
					"FILE|html/_form_tpl_th2.html",
		"");
		
		// Automatisk opdatering, genereret 09-03-2010 14:14:22
		$updates["2010030900"]["text"] = "Tilføjelse så breadcrumb_active.html benyttes til det aktive element i brødkrummer";
		$updates["2010030900"]["update"] = array(
					"FILE|class/settings.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 09-03-2010 14:43:35
		$updates["2010030901"]["text"] = "Rettelse af smart url for menu";
		$updates["2010030901"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 15-03-2010 12:43:41
		$updates["2010031500"]["text"] = "Tilføjet formattering af dato / tid under indstillinger";
		$updates["2010031500"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/menu.php",
					"FILE|Admin/pages/settings.php",
					"FILE|class/convert.php",
					"FILE|inc/functions.php",
			"EVAL|" . base64_encode('	cms_setting("convert_date_today", "%H:%M");
	cms_setting("convert_date_year", "%d. %B");
	cms_setting("convert_date_default", "%d. %B %Y");
'),
		"");
		
		// Automatisk opdatering, genereret 16-03-2010 12:42:52
		$updates["2010031600"]["text"] = "Mulighed for flere sprog i modulnavn";
		$updates["2010031600"]["update"] = array(
					"FILE|Admin/pages/modules.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 17-03-2010 12:21:38
		$updates["2010031700"]["text"] = "Rettelse af modul titel";
		$updates["2010031700"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 17-03-2010 12:22:45
		$updates["2010031701"]["text"] = "Rettelse af modul titel";
		$updates["2010031701"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 17-03-2010 12:23:33
		$updates["2010031702"]["text"] = "Rettelse af modul titel";
		$updates["2010031702"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 19-03-2010 11:45:56
		$updates["2010031900"]["text"] = "Opdatering af ajax klasse";
		$updates["2010031900"]["update"] = array(
					"FILE|html/_ajax_script.html",
					"FILE|img/ajax_loading.gif",
		"");
		
		// Automatisk opdatering, genereret 19-03-2010 11:47:15
		$updates["2010031901"]["text"] = "Opdatering af ajax klasse";
		$updates["2010031901"]["update"] = array(
					"FILE|html/_ajax_script.html",
		"");
		
		// Automatisk opdatering, genereret 19-03-2010 11:56:12
		$updates["2010031902"]["text"] = "Opdatering af ajax klasse";
		$updates["2010031902"]["update"] = array(
					"FILE|html/_ajax_script.html",
					"FILE|img/ajax_loading.gif",
		"");
		
		// Automatisk opdatering, genereret 24-03-2010 12:08:26
		$updates["2010032400"]["text"] = "Rettelse af modul opdatering";
		$updates["2010032400"]["update"] = array(
					"FILE|Admin/pages/modules.php",
		"");
		
		// Automatisk opdatering, genereret 26-03-2010 12:37:44
		$updates["2010032600"]["text"] = "Tilbage til gammel menu sortering";
		$updates["2010032600"]["update"] = array(
					"FILE|Admin/pages/pages.php",
		"");
		
		// Automatisk opdatering, genereret 26-03-2010 13:28:02
		$updates["2010032601"]["text"] = "Tilbage til menu drag'n'drop";
		$updates["2010032601"]["update"] = array(
					"FILE|Admin/pages/pages.php",
		"");
		
		// Automatisk opdatering, genereret 28-03-2010 21:56:04
		$updates["2010032800"]["text"] = "Opdatering af admin login";
		$updates["2010032800"]["update"] = array(
					"FILE|img/admin_background_login.gif",
		"");
		
		// Automatisk opdatering, genereret 08-04-2010 15:37:05
		$updates["2010040800"]["text"] = "Opdatering af DB klasse";
		$updates["2010040800"]["update"] = array(
					"FILE|Admin/pages/sql.php",
					"FILE|class/db.php",
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 09-04-2010 10:29:03
		$updates["2010040900"]["text"] = "Rettelse vedr. viderestilling til smart-url";
		$updates["2010040900"]["update"] = array(
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 20-05-2010 12:11:25
		$updates["2010052000"]["text"] = "Tilføjelse af notatfelt til alle sider i admin";
		$updates["2010052000"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/note.php",
					"FILE|class/user.php",
					"FILE|html/admin_layout_default.html",
					"FILE|html/admin_note.html",
					"FILE|img/note.png",
			"EVAL|" . base64_encode('	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_admin_notes` (
		  `module` varchar(50) NOT NULL,
		  `page` varchar(50) NOT NULL,
		  `note` text NOT NULL,
		  PRIMARY KEY  (`module`,`page`)
		)
		");
'),
		"");
		
		// Automatisk opdatering, genereret 21-05-2010 13:28:15
		$updates["2010052100"]["text"] = "Opdatering af database klasse";
		$updates["2010052100"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/change_password.php",
					"FILE|Admin/pages/css_editor_types.php",
					"FILE|Admin/pages/default.php",
					"FILE|Admin/pages/domains.php",
					"FILE|Admin/pages/file_editor.php",
					"FILE|Admin/pages/frameset.php",
					"FILE|Admin/pages/keep_alive.php",
					"FILE|Admin/pages/languages.php",
					"FILE|Admin/pages/log_messages.php",
					"FILE|Admin/pages/logout.php",
					"FILE|Admin/pages/menu.php",
					"FILE|Admin/pages/modules.php",
					"FILE|Admin/pages/note.php",
					"FILE|Admin/pages/pages.php",
					"FILE|Admin/pages/popup_colors.php",
					"FILE|Admin/pages/popup_images.php",
					"FILE|class/convert.php",
					"FILE|class/csv.php",
					"FILE|class/db.php",
					"FILE|class/ftp.php",
					"FILE|class/graph.php",
					"FILE|class/image.php",
					"FILE|class/js_realtime.php",
					"FILE|class/links.php",
					"FILE|class/message.php",
					"FILE|class/mysqldump.php",
					"FILE|class/paging.php",
					"FILE|class/settings.php",
					"FILE|class/table.php",
					"FILE|class/user.php",
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 25-05-2010 09:00:50
		$updates["2010052500"]["text"] = "Rettelse af bruger-klasse";
		$updates["2010052500"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 09-06-2010 21:12:30
		$updates["2010060900"]["text"] = "Opdatering af ajax klasse således at CDATA benyttes ved lange værdier";
		$updates["2010060900"]["update"] = array(
					"FILE|class/ajax.php",
					"FILE|html/_ajax_response_value.html",
		"");
		
		// Automatisk opdatering, genereret 02-07-2010 09:56:29
		$updates["2010070200"]["text"] = "Rettelse af menu editor";
		$updates["2010070200"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 06-07-2010 10:14:54
		$updates["2010070600"]["text"] = "Opdatering af admin menu";
		$updates["2010070600"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 06-09-2010 21:07:07
		$updates["2010090600"]["text"] = "Rettelse af cronjob";
		$updates["2010090600"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/db.php",
		"");
		
		// Automatisk opdatering, genereret 06-09-2010 21:08:23
		$updates["2010090601"]["text"] = "Rettelse af db klasse";
		$updates["2010090601"]["update"] = array(
					"FILE|class/db.php",
		"");
		
		// Automatisk opdatering, genereret 16-09-2010 12:17:17
		$updates["2010091600"]["text"] = "Mulighed for layout og modulspecifikke layoutfiler";
		$updates["2010091600"]["update"] = array(
					"FILE|class/email.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 17-09-2010 13:59:56
		$updates["2010091700"]["text"] = "Opdatering af bruger klasse";
		$updates["2010091700"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 17-09-2010 14:08:05
		$updates["2010091701"]["text"] = "Opdatering af bruger klasse";
		$updates["2010091701"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 20-09-2010 14:53:43
		$updates["2010092000"]["text"] = "Tilføjelse af MESSAGE-tag i formular-klasse";
		$updates["2010092000"]["update"] = array(
					"FILE|class/form.php",
					"FILE|class/tpl.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 21-09-2010 21:38:42
		$updates["2010092100"]["text"] = "opdatering af image klasse";
		$updates["2010092100"]["update"] = array(
					"FILE|class/image.php",
		"");
		
		// Automatisk opdatering, genereret 27-09-2010 10:26:41
		$updates["2010092700"]["text"] = "Rettelse af bruger-klasse";
		$updates["2010092700"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 01-10-2010 13:10:15
		$updates["2010100100"]["text"] = "Rettelse af form-klasse";
		$updates["2010100100"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|class/form.php",
					"FILE|class/image.php",
		"");
		
		// Automatisk opdatering, genereret 12-10-2010 10:08:41
		$updates["2010101200"]["text"] = "Opdatering af bruger klasse";
		$updates["2010101200"]["update"] = array(
					"FILE|class/form.php",
					"FILE|class/image.php",
					"FILE|class/user.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 22-11-2010 11:31:28
		$updates["2010112200"]["text"] = "Opdatering af formular";
		$updates["2010112200"]["update"] = array(
					"FILE|class/email.php",
					"FILE|class/form.php",
					"FILE|class/mysqldump.php",
					"FILE|html/_form_confirm_file.html",
					"FILE|html/_form_file_example.html",
					"FILE|js/spaw/config/config.php",
		"");
		
		// Automatisk opdatering, genereret 22-11-2010 13:51:52
		$updates["2010112201"]["text"] = "Mulighed for at vælge bruger-gruppe i forbindelse med menupunkt";
		$updates["2010112201"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 25-11-2010 14:20:14
		$updates["2010112500"]["text"] = "Opdatering af cronjob";
		$updates["2010112500"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 09-12-2010 10:12:48
		$updates["2010120900"]["text"] = "Mulighed for automatisk installation af opdateringer ved login";
		$updates["2010120900"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/auto_update.php",
					"FILE|Admin/pages/cms.php",
					"FILE|Admin/pages/login.php",
					"FILE|Admin/pages/modules.php",
					"FILE|html/admin_auto_update.html",
		"");
		
		// Automatisk opdatering, genereret 23-12-2010 11:52:20
		$updates["2010122300"]["text"] = "Opdatering af cronjob";
		$updates["2010122300"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
		"");
		
		// Automatisk opdatering, genereret 23-12-2010 11:53:19
		$updates["2010122301"]["text"] = "Opdatering af cronjob";
		$updates["2010122301"]["update"] = array(
					"FILE|Admin/pages/bot.php",
		"");
		
		// Automatisk opdatering, genereret 04-01-2011 14:03:04
		$updates["2011010400"]["text"] = "Rettelse af emne-encoding i email-klasse";
		$updates["2011010400"]["update"] = array(
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 06-01-2011 13:41:38
		$updates["2011010600"]["text"] = "Rettelse af brugerklasse";
		$updates["2011010600"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 09-02-2011 14:48:04
		$updates["2011020900"]["text"] = "Opdatering af AJAX script";
		$updates["2011020900"]["update"] = array(
					"FILE|html/_ajax_script.html",
		"");
		
		// Automatisk opdatering, genereret 28-02-2011 11:38:30
		$updates["2011022800"]["text"] = "Opdatering af sprogfiler";
		$updates["2011022800"]["update"] = array(
					"FILE|lang/en.php",
					"FILE|lang/lang.php",
		"");
		
		// Automatisk opdatering, genereret 01-03-2011 12:21:32
		$updates["2011030100"]["text"] = "Rettelse til formular klasse";
		$updates["2011030100"]["update"] = array(
					"FILE|class/form.php",
		"");
		
		// Automatisk opdatering, genereret 15-03-2011 16:40:17
		$updates["2011031500"]["text"] = "Tilføjelse af DATETIME tag";
		$updates["2011031500"]["update"] = array(
					"FILE|class/form.php",
					"FILE|class/tpl.php",
					"FILE|html/_form_radio.html",
					"FILE|html/_form_radio_option.html",
					"FILE|html/_form_radio_option_selected.html",
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 24-03-2011 11:11:23
		$updates["2011032400"]["text"] = "Menu-Forside stiller automatisk videre til /";
		$updates["2011032400"]["update"] = array(
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 28-03-2011 22:48:22
		$updates["2011032800"]["text"] = "Rettelse til CSS editor";
		$updates["2011032800"]["update"] = array(
					"FILE|Admin/pages/css_editor.php",
		"");
		
		// Automatisk opdatering, genereret 06-04-2011 11:32:44
		$updates["2011040600"]["text"] = "Opdatering af bruger klasse";
		$updates["2011040600"]["update"] = array(
					"FILE|class/user.php",
					"FILE|js/jquery/carousel.js",
		"");
		
		// Automatisk opdatering, genereret 06-04-2011 11:41:57
		$updates["2011040601"]["text"] = "Opdatering af bruger klasse";
		$updates["2011040601"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 06-04-2011 17:20:29
		$updates["2011040602"]["text"] = "Tilføjeset referer tjek til admin";
		$updates["2011040602"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 06-04-2011 17:24:10
		$updates["2011040603"]["text"] = "Opdatering af referer tjek i admin";
		$updates["2011040603"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 07-04-2011 07:52:37
		$updates["2011040700"]["text"] = "Rettelse af referer tjek";
		$updates["2011040700"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 13-04-2011 02:36:03
		$updates["2011041300"]["text"] = "Rettelse af bruger klasse";
		$updates["2011041300"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 13-04-2011 02:36:03
		$updates["2011041303"]["text"] = "Rettelse af log af";
		$updates["2011041303"]["update"] = array(
			"FOLDER|Admin/html",
					"FILE|Admin/html/admin_layout_top.html",
					"FILE|Admin/html/admin_layout_bottom.html",
					"FILE|html/admin_layout_frameset.html",
		"");
		
		// Automatisk opdatering, genereret 29-04-2011 15:49:48
		$updates["2011042900"]["text"] = "Rettelse af spaw ";
		$updates["2011042900"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 10-05-2011 16:31:54
		$updates["2011051000"]["text"] = "Tilføjelse af select med cms elementer til spaw editor";
		$updates["2011051000"]["update"] = array(
					"FILE|class/form.php",
					"FILE|class/tpl.php",
					"FILE|js/spaw/config/config.php",
					"FILE|js/spaw/plugins/core/js/common/table.js",
					"FILE|js/spaw/plugins/core/lib/toolbars/stadel.toolbar.php",
					"FILE|js/spaw/plugins/stadel/js/common/stadel.js",
		"");
		
		// Automatisk opdatering, genereret 13-05-2011 17:58:09
		$updates["2011051300"]["text"] = "Opdatering af tpl klasse";
		$updates["2011051300"]["update"] = array(
					"FILE|class/tpl.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 24-05-2011 12:13:39
		$updates["2011052400"]["text"] = "Opdatering af jQuery";
		$updates["2011052400"]["update"] = array(
					"FILE|class/tpl.php",
					"FILE|js/jquery/jquery.js",
		"");
		
		// Automatisk opdatering, genereret 24-05-2011 16:40:10
		$updates["2011052401"]["text"] = "Opdatering af interne links";
		$updates["2011052401"]["update"] = array(
					"FILE|html/admin_select_links.html",
		"");
		
		// Automatisk opdatering, genereret 26-05-2011 11:36:58
		$updates["2011052600"]["text"] = "Opdatering af AJAX klasse";
		$updates["2011052600"]["update"] = array(
					"FILE|class/ajax.php",
					"FILE|html/_ajax_script.html",
					"FILE|js/stadel/ajax.js",
		"");
		
		// Automatisk opdatering, genereret 09-06-2011 12:24:46
		$updates["2011060900"]["text"] = "Mulighed for bruger variabler";
		$updates["2011060900"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/settings.php",
					"FILE|html/_form_tab_footer.html",
					"FILE|html/_form_tab_header_1.html",
					"FILE|html/_form_tab_header_tab_error.html",
					"FILE|img/tab_error_left.gif",
					"FILE|img/tab_error_middle.gif",
					"FILE|img/tab_error_right.gif",
		"");
		
		// Automatisk opdatering, genereret 13-06-2011 17:57:32
		$updates["2011061300"]["text"] = "Mulighed for redirect af domaener";
		$updates["2011061300"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/domains.php",
					"FILE|index.php",
			"EVAL|" . base64_encode('$db->execute("ALTER TABLE " . $_table_prefix . "_domains_ ADD redirect VARCHAR(255) NOT NULL DEFAULT \'\'");'),
		"");
		
		// Automatisk opdatering, genereret 13-06-2011 18:05:49
		$updates["2011061301"]["text"] = "Mulighed for redirect af domaener";
		$updates["2011061301"]["update"] = array(
					"FILE|Admin/pages/domains.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 18-06-2011 00:04:41
		$updates["2011061800"]["text"] = "Mulighed for at indlæse undermenuer fra modul";
		$updates["2011061800"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/domains.php",
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE
		" . $_table_prefix . "_pages_
	ADD
		sub_menu varchar(50) not null default \'\'
	");'),
		"");
		
		// Automatisk opdatering, genereret 22-06-2011 11:30:26
		$updates["2011062200"]["text"] = "Rettelse af tabel-klasse";
		$updates["2011062200"]["update"] = array(
					"FILE|class/table.php",
					"FILE|html/_table_dnd.html",
					"FILE|html/_table_footer.html",
					"FILE|html/_table_header.html",
		"");
		
		// Automatisk opdatering, genereret 27-06-2011 08:45:43
		$updates["2011062700"]["text"] = "Rettelse af under-menu for moduler";
		$updates["2011062700"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 29-06-2011 10:57:35
		$updates["2011062900"]["text"] = "Rettelse af email klasse";
		$updates["2011062900"]["update"] = array(
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 29-06-2011 13:34:22
		$updates["2011062901"]["text"] = "Tilføjelse af flowplayer";
		$updates["2011062901"]["update"] = array(
					"FOLDER|js/flowplayer",
					"FILE|index.php",
					"FILE|js/flowplayer/flowplayer.controls.swf",
					"FILE|js/flowplayer/flowplayer.js",
					"FILE|js/flowplayer/flowplayer.swf",
		"");
		
		// Automatisk opdatering, genereret 29-06-2011 13:45:59
		$updates["2011062902"]["text"] = "Rettelse af spaw upload så den erstatter ikke gyldig tegn";
		$updates["2011062902"]["update"] = array(
					"FILE|js/spaw/plugins/spawfm/class/spawfm.class.php",
		"");
		
		// Automatisk opdatering, genereret 29-06-2011 13:48:18
		$updates["2011062903"]["text"] = "Rettelse af spaw upload så den erstatter ikke gyldig tegn";
		$updates["2011062903"]["update"] = array(
					"FILE|js/spaw/plugins/spawfm/class/spawfm.class.php",
		"");
		
		// Automatisk opdatering, genereret 01-07-2011 14:21:11
		$updates["2011070102"]["text"] = "Rettelse af admin layout";
		$updates["2011070102"]["update"] = array(
					"FILE|html/admin_layout_frameset.html",
					"FILE|html/admin_layout_top.html",
					"FILE|js/spaw/plugins/spawfm/class/spawfm.class.php",
		"");
		
		// Automatisk opdatering, genereret 01-07-2011 14:28:14
		$updates["2011070101"]["text"] = "Opdatering";
		$updates["2011070101"]["update"] = array(
					"FILE|Admin/index.php",
					"FILE|html/admin_layout_frameset.html",
		"");
		
		// Automatisk opdatering, genereret 01-07-2011 14:28:43
		$updates["2011070105"]["text"] = "Opdatering";
		$updates["2011070105"]["update"] = array(
			"EVAL|" . base64_encode(' '),
		"");
		
		// Automatisk opdatering, genereret 01-07-2011 14:29:09
		$updates["2011070106"]["text"] = "Opdatering";
		$updates["2011070106"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 05-07-2011 18:19:35
		$updates["2011070500"]["text"] = "Mulighed for at deaktivere viderestilling til forside";
		$updates["2011070500"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 08-07-2011 10:05:13
		$updates["2011070800"]["text"] = "Opdatering af form radio knapper";
		$updates["2011070800"]["update"] = array(
					"FILE|html/_form_radio_option.html",
		"");
		
		// Automatisk opdatering, genereret 08-07-2011 10:15:13
		$updates["2011070801"]["text"] = "Opdatering af form radio knapper";
		$updates["2011070801"]["update"] = array(
					"FILE|class/form.php",
					"FILE|html/_form_radio_option.html",
					"FILE|html/_form_radio_option_selected.html",
		"");
		
		// Automatisk opdatering, genereret 11-07-2011 08:34:41
		$updates["2011071100"]["text"] = "Tilføjelse af felt kommentar til bruger-klasse";
		$updates["2011071100"]["update"] = array(
					"FILE|class/form.php",
					"FILE|class/user.php",
					"FILE|html/_form_radio_option.html",
					"FILE|html/_form_radio_option_selected.html",
		"");
		
		// Automatisk opdatering, genereret 11-08-2011 10:36:50
		$updates["2011081100"]["text"] = "Rettelse af sitemenu";
		$updates["2011081100"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 11-08-2011 10:41:36
		$updates["2011081101"]["text"] = "Rettelse af sitemenu";
		$updates["2011081101"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 11-08-2011 11:17:02
		$updates["2011081102"]["text"] = "Rettelse af sitemenu";
		$updates["2011081102"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 16-08-2011 14:10:28
		$updates["2011081600"]["text"] = "Opdatering";
		$updates["2011081600"]["update"] = array(
					"FILE|Admin/pages/log_messages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 16-08-2011 14:15:08
		$updates["2011081601"]["text"] = "Opdatering";
		$updates["2011081601"]["update"] = array(
					"FILE|Admin/pages/log_messages.php",
		"");
		
		// Automatisk opdatering, genereret 17-08-2011 10:56:58
		$updates["2011081700"]["text"] = "Opdatering af menu";
		$updates["2011081700"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/pages.php",
					"FILE|inc/functions.php",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE
		" . $_table_prefix . "_pages_
	ADD
		alt_url varchar(250) not null default \'\',
	ADD
		no_link tinyint(1) unsigned not null default \'0\'
	");
'),
		"");
		
		// Automatisk opdatering, genereret 17-08-2011 10:57:40
		$updates["2011081701"]["text"] = "Opdatering af menu";
		$updates["2011081701"]["update"] = array(
					"FILE|Admin/install.php",
		"");
		
		// Automatisk opdatering, genereret 17-08-2011 13:45:57
		$updates["2011081702"]["text"] = "Rettelse af email klasse (erstatter [EMAIL] med modtagers e-mail)";
		$updates["2011081702"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 23-08-2011 09:34:27
		$updates["2011082300"]["text"] = "Tilføjelse af [NAME] til email klasse";
		$updates["2011082300"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|class/email.php",
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 12-09-2011 10:40:09
		$updates["2011091200"]["text"] = "Rettelse til SPAW editor for IE9";
		$updates["2011091200"]["update"] = array(
					"FILE|Admin/pages/languages.php",
					"FILE|index.php",
					"FILE|js/spaw/js/ie/editor.js",
		"");
		
		// Automatisk opdatering, genereret 12-09-2011 11:36:50
		$updates["2011091201"]["text"] = "Tilføjelse af [NAME] og [EMAIL] til emne";
		$updates["2011091201"]["update"] = array(
					"FILE|class/email.php",
		"");
		
		// Automatisk opdatering, genereret 20-09-2011 14:51:52
		$updates["2011092000"]["text"] = "Opdatering af menu";
		$updates["2011092000"]["update"] = array(
					"FILE|pages/menu_sub.php",
		"");
		
		// Automatisk opdatering, genereret 03-10-2011 15:53:57
		$updates["2011100300"]["text"] = "Automatisk opdatering af sprog-filer ved oversættelse";
		$updates["2011100300"]["update"] = array(
					"FILE|Admin/pages/languages.php",
					"FILE|inc/functions.php",
					"FILE|lang/en.php",
					"FILE|lang/lang.php",
					"FILE|lang/langx.php",
		"");
		
		// Automatisk opdatering, genereret 08-10-2011 08:22:00
		$updates["2011100800"]["text"] = "Rettelse af sprog oversættelse";
		$updates["2011100800"]["update"] = array(
					"FILE|Admin/pages/languages.php",
					"FILE|inc/functions.php",
					"FILE|lang/en.php",
					"FILE|lang/lang.php",
		"");
		
		// Automatisk opdatering, genereret 10-10-2011 10:33:27
		$updates["2011101000"]["text"] = "Rettelse af sprog oversættelse";
		$updates["2011101000"]["update"] = array(
					"FILE|Admin/pages/languages.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 04-11-2011 10:42:53
		$updates["2011110400"]["text"] = "Opdatering af admin regler";
		$updates["2011110400"]["update"] = array(
					"FILE|Admin/pages/admin_users.php",
					"FILE|Admin/pages/rule_editor.php",
					"FILE|class/user.php",
					"FILE|js/stadel/ajax.js",
		"");
		
		// Automatisk opdatering, genereret 04-11-2011 15:20:20
		$updates["2011110401"]["text"] = "Udvidelse af e-mail indstillinger for domæner";
		$updates["2011110401"]["update"] = array(
					"FILE|Admin/install.php",
					"FILE|Admin/pages/domains.php",
					"FILE|Admin/pages/settings.php",
					"FILE|index.php",
			"EVAL|" . base64_encode('$db->execute("
	ALTER TABLE
		" . $_table_prefix . "_domains_
	ADD
		email_attach_images VARCHAR( 1 ) NOT NULL ,
	ADD
		email_method VARCHAR( 5 ) NOT NULL ,
	ADD
		email_smtp_host VARCHAR( 100 ) NOT NULL ,
	ADD
		email_smtp_port VARCHAR( 5 ) NOT NULL ,
	ADD
		email_smtp_user VARCHAR( 100 ) NOT NULL ,
	ADD
		email_smtp_pass VARCHAR( 100 ) NOT NULL ,
	ADD
		return_email VARCHAR( 100 ) NOT NULL ,
	ADD
		return_email_server VARCHAR( 100 ) NOT NULL ,
	ADD
		return_email_user VARCHAR( 100 ) NOT NULL ,
	ADD
		return_email_pass VARCHAR( 100 ) NOT NULL 
	");
	'),
		"");
		
		// Automatisk opdatering, genereret 22-11-2011 17:22:02
		$updates["2011112204"]["text"] = "Rettelse af email";
		$updates["2011112204"]["update"] = array(
					"FILE|class/user.php",
					"FILE|class/db.php",
					"FILE|inc/functions.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 23-11-2011 09:41:09
		$updates["2011112300"]["text"] = "Rettelse af funktionsklasse";
		$updates["2011112300"]["update"] = array(
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 30-11-2011 12:19:24
		$updates["2011113000"]["text"] = "Rettelse af viderestilling for menupunkter";
		$updates["2011113000"]["update"] = array(
					"FILE|Admin/pages/bot.php",
					"FILE|Admin/pages/pages.php",
					"FILE|pages/default.php",
		"");
		
		// Automatisk opdatering, genereret 02-12-2011 09:57:11
		$updates["2011120200"]["text"] = "Opdatering af bruger-klasse";
		$updates["2011120200"]["update"] = array(
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 12-12-2011 12:58:38
		$updates["2011121200"]["text"] = "Tilføjet felt til HTML HEAD";
		$updates["2011121200"]["update"] = array(
					"FILE|Admin/pages/settings.php",
					"FILE|index.php",
		"");
		
		// Automatisk opdatering, genereret 20-12-2011 10:49:06
		$updates["2011122000"]["text"] = "Rettelse af admin";
		$updates["2011122000"]["update"] = array(
					"FILE|Admin/index.php",
		"");
		
		// Automatisk opdatering, genereret 01-02-2012 14:17:59
		$updates["2012020100"]["text"] = "Opdatering af tpl";
		$updates["2012020100"]["update"] = array(
					"FILE|Admin/pages/log_messages.php",
					"FILE|class/csv.php",
					"FILE|class/email.php",
					"FILE|class/pop3.php",
					"FILE|class/table.php",
					"FILE|class/tpl.php",
					"FILE|js/stadel/ajax.js",
		"");
		
		// Automatisk opdatering, genereret 07-03-2012 11:03:11
		$updates["2012030700"]["text"] = "Rettelse af tpl klasse";
		$updates["2012030700"]["update"] = array(
					"FILE|class/image.php",
					"FILE|class/table.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 27-03-2012 11:19:19
		$updates["2012032700"]["text"] = "Mulighed for at kopiere menu mellem sprog";
		$updates["2012032700"]["update"] = array(
					"FILE|Admin/pages/pages.php",
		"");
		
		// Automatisk opdatering, genereret 11-04-2012 10:56:40
		$updates["2012041100"]["text"] = "Opdatering af bruger klasse";
		$updates["2012041100"]["update"] = array(
					"FILE|Admin/pages/pages.php",
					"FILE|class/user.php",
		"");
		
		// Automatisk opdatering, genereret 23-04-2012 11:43:38
		$updates["2012042300"]["text"] = "Opdatering af admin menu";
		$updates["2012042300"]["update"] = array(
					"FILE|class/user.php",
					"FILE|html/admin_layout_menu.html",
					"FILE|img/icon_support.gif",
		"");
		
		// Automatisk opdatering, genereret 16-05-2012 13:56:45
		$updates["2012051600"]["text"] = "Tilføjet create_password()";
		$updates["2012051600"]["update"] = array(
					"FILE|class/table.php",
					"FILE|inc/functions.php",
		"");
		
		// Automatisk opdatering, genereret 13-06-2012 11:43:22
		$updates["2012061300"]["text"] = "Tilføjelse af <!>IF|værdi1|værdi2|sand|falsk tag til template klasse";
		$updates["2012061300"]["update"] = array(
					"FILE|class/tpl.php",
					"FILE|inc/functions.php",
					"FILE|js/stadel/edit.js",
		"");
		
		// Automatisk opdatering, genereret 14-06-2012 10:38:33
		$updates["2012061400"]["text"] = "Hastighedsforbedring af template-klasse ca. 90%";
		$updates["2012061400"]["update"] = array(
					"FILE|Admin/pages/file_editor.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 14-06-2012 10:45:05
		$updates["2012061401"]["text"] = "Template rettelse";
		$updates["2012061401"]["update"] = array(
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 14-06-2012 10:59:45
		$updates["2012061402"]["text"] = "Template rettelse";
		$updates["2012061402"]["update"] = array(
					"FILE|class/table.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 14-06-2012 11:12:17
		$updates["2012061403"]["text"] = "Opdatering af fil editor";
		$updates["2012061403"]["update"] = array(
					"FILE|Admin/pages/elements.php",
					"FILE|Admin/pages/file_editor.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 15-06-2012 09:07:27
		$updates["2012061500"]["text"] = "Rettelse af template";
		$updates["2012061500"]["update"] = array(
					"FILE|Admin/pages/elements.php",
					"FILE|Admin/pages/file_editor.php",
					"FILE|class/tpl.php",
		"");
		
		// Automatisk opdatering, genereret 19-06-2012 11:36:59
		$updates["2012061900"]["text"] = "Rettelse at template klasse";
		$updates["2012061900"]["update"] = array(
					"FILE|class/tpl.php",
					"FILE|index.php",
		"");
		
			// Automatisk opdatering, genereret 19-06-2012 22:45:29
			$updates["2012061901"]["text"] = "Opdatering";
			$updates["2012061901"]["update"] = array(
						"FILE|index.php",
			"");
			
			// Automatisk opdatering, genereret 20-06-2012 11:27:43
			$updates["2012062000"]["text"] = "Opdatering";
			$updates["2012062000"]["update"] = array(
						"FILE|class/email.php",
			"");
			
			// Automatisk opdatering, genereret 21-06-2012 13:29:25
			$updates["2012062100"]["text"] = "Opdatering";
			$updates["2012062100"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|class/email.php",
			"");
			
			// Automatisk opdatering, genereret 03-07-2012 13:16:34
			$updates["2012070300"]["text"] = "Opdatering";
			$updates["2012070300"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|class/tpl.php",
			"");
			
		// Automatisk opdatering, genereret 10-08-2012 10:53:50
		$updates["2012081000"]["text"] = "Mulighed for [SUBJECT] i body (email klasse)";
		$updates["2012081000"]["update"] = array(
					"FILE|class/email.php",
					"FILE|class/tpl.php",
					"FILE|js/spaw/config/config.php",
		"");
		
			// Automatisk opdatering, genereret 28-08-2012 08:02:57
			$updates["2012082800"]["text"] = "Opdatering";
			$updates["2012082800"]["update"] = array(
						"FILE|Admin/pages/file_editor.php",
						"FILE|class/email.php",
			"");
			
			// Automatisk opdatering, genereret 31-08-2012 15:19:36
			$updates["2012083100"]["text"] = "Opdatering";
			$updates["2012083100"]["update"] = array(
						"FILE|Admin/pages/elements.php",
						"FILE|Admin/pages/file_editor.php",
			"");
			
			// Automatisk opdatering, genereret 04-09-2012 14:43:05
			$updates["2012090400"]["text"] = "Opdatering";
			$updates["2012090400"]["update"] = array(
						"FILE|inc/functions.php",
						"FILE|index.php",
			"");
			
			// Automatisk opdatering, genereret 04-09-2012 15:17:38
			$updates["2012090401"]["text"] = "Opdatering";
			$updates["2012090401"]["update"] = array(
						"FILE|html/_ajax_script.html",
						"FILE|js/stadel/ajax.js",
			"");
			
			// Automatisk opdatering, genereret 04-09-2012 15:25:10
			$updates["2012090402"]["text"] = "Opdatering";
			$updates["2012090402"]["update"] = array(
						"FILE|js/stadel/ajax.js",
			"");
			
			// Automatisk opdatering, genereret 04-09-2012 15:30:44
			$updates["2012090403"]["text"] = "Opdatering";
			$updates["2012090403"]["update"] = array(
						"FILE|Admin/pages/pages.php",
			"");
			
			// Automatisk opdatering, genereret 05-09-2012 11:28:41
			$updates["2012090500"]["text"] = "Opdatering";
			$updates["2012090500"]["update"] = array(
						"FILE|html/_ajax_script.html",
			"");
			
			// Automatisk opdatering, genereret 05-09-2012 14:23:52
			$updates["2012090501"]["text"] = "Opdatering";
			$updates["2012090501"]["update"] = array(
						"FILE|class/form.php",
			"");
			
			// Automatisk opdatering, genereret 05-09-2012 14:40:49
			$updates["2012090502"]["text"] = "Opdatering";
			$updates["2012090502"]["update"] = array(
						"FILE|class/tpl.php",
			"");
			
			// Automatisk opdatering, genereret 13-09-2012 11:54:57
			$updates["2012091300"]["text"] = "Opdatering";
			$updates["2012091300"]["update"] = array(
						"FILE|class/table.php",
						"FILE|css.php",
			"");
			
			// Automatisk opdatering, genereret 17-09-2012 16:46:24
			$updates["2012091700"]["text"] = "Opdatering";
			$updates["2012091700"]["update"] = array(
						"FILE|class/tpl.php",
						"FILE|js/stadel/functions.js",
			"");
			
			// Automatisk opdatering, genereret 20-09-2012 15:02:26
			$updates["2012092000"]["text"] = "Opdatering";
			$updates["2012092000"]["update"] = array(
						"FILE|class/mysqldump.php",
						"FILE|class/tpl.php",
						"FILE|js/stadel/functions.js",
			"");
			
			// Automatisk opdatering, genereret 20-09-2012 15:14:26
			$updates["2012092001"]["text"] = "Opdatering";
			$updates["2012092001"]["update"] = array(
						"FILE|Admin/pages/modules.php",
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 21-09-2012 11:58:23
			$updates["2012092100"]["text"] = "Opdatering";
			$updates["2012092100"]["update"] = array(
						"FILE|class/form.php",
						"FILE|js/spaw/config/config.php",
			"");
			
			// Automatisk opdatering, genereret 25-09-2012 10:33:10
			$updates["2012092500"]["text"] = "Opdatering";
			$updates["2012092500"]["update"] = array(
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 25-09-2012 11:06:46
			$updates["2012092501"]["text"] = "Opdatering";
			$updates["2012092501"]["update"] = array(
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 25-09-2012 13:07:15
			$updates["2012092502"]["text"] = "Opdatering";
			$updates["2012092502"]["update"] = array(
						"FILE|js/spaw/js/common/editor.js",
			"");
			
			// Automatisk opdatering, genereret 26-09-2012 14:49:58
			$updates["2012092600"]["text"] = "Opdatering";
			$updates["2012092600"]["update"] = array(
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 10-10-2012 10:03:43
			$updates["2012101000"]["text"] = "Opdatering";
			$updates["2012101000"]["update"] = array(
						"FILE|js/spaw/config/config.php",
			"");
			
			// Automatisk opdatering, genereret 22-10-2012 12:06:34
			$updates["2012102200"]["text"] = "Opdatering";
			$updates["2012102200"]["update"] = array(
						"FILE|class/email.php",
						"FILE|js/spaw/config/config.php",
			"");
			
			// Automatisk opdatering, genereret 24-10-2012 12:28:54
			$updates["2012102400"]["text"] = "Opdatering";
			$updates["2012102400"]["update"] = array(
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 03-01-2013 22:31:54
			$updates["2013010300"]["text"] = "Opdatering";
			$updates["2013010300"]["update"] = array(
						"FILE|class/csv.php",
			"");
			
			// Automatisk opdatering, genereret 15-01-2013 12:17:39
			$updates["2013011500"]["text"] = "Opdatering";
			$updates["2013011500"]["update"] = array(
						"FILE|pages/menu_sub.php",
			"");
			
			// Automatisk opdatering, genereret 18-02-2013 11:12:00
			$updates["2013021800"]["text"] = "Opdatering";
			$updates["2013021800"]["update"] = array(
						"FILE|class/user.php",
						"FILE|inc/functions.php",
						"FILE|index.php",
						"FILE|js/spaw/config/config.php",
						"FILE|js/spaw/plugins/stadel/js/common/stadel.js",
			"");
			
			// Automatisk opdatering, genereret 19-02-2013 17:36:21
			$updates["2013021900"]["text"] = "Opdatering";
			$updates["2013021900"]["update"] = array(
						"FILE|class/user.php",
			"");
			
			// Automatisk opdatering, genereret 21-02-2013 11:27:59
			$updates["2013022100"]["text"] = "Opdatering";
			$updates["2013022100"]["update"] = array(
						"FILE|class/user.php",
						"FILE|inc/functions.php",
			"");
			
			// Automatisk opdatering, genereret 18-03-2013 14:49:52
			$updates["2013031800"]["text"] = "Opdatering";
			$updates["2013031800"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|Admin/pages/settings.php",
						"FILE|class/paging.php",
			"");
			
			// Automatisk opdatering, genereret 10-04-2013 12:09:05
			$updates["2013041000"]["text"] = "Opdatering";
			$updates["2013041000"]["update"] = array(
						"FILE|Admin/pages/file_editor.php",
						"FILE|class/form.php",
			"");
			
			// Automatisk opdatering, genereret 10-04-2013 13:39:50
			$updates["2013041001"]["text"] = "Opdatering";
			$updates["2013041001"]["update"] = array(
						"FILE|Admin/pages/login.php",
						"FILE|class/form.php",
			"");
			
			// Automatisk opdatering, genereret 08-05-2013 12:44:59
			$updates["2013050800"]["text"] = "Opdatering";
			$updates["2013050800"]["update"] = array(
						"FILE|Admin/pages/settings.php",
			"");
			
			// Automatisk opdatering, genereret 16-05-2013 10:51:31
			$updates["2013051600"]["text"] = "Opdatering";
			$updates["2013051600"]["update"] = array(
						"FILE|inc/functions.php",
						"FILE|lang/en.php",
						"FILE|lang/lang.php",
			"");
			
			// Automatisk opdatering, genereret 19-06-2013 14:53:09
			$updates["2013061900"]["text"] = "Opdatering";
			$updates["2013061900"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|Admin/pages/settings.php",
						"FILE|class/form.php",
						"FILE|class/user.php",
						"FILE|html/_user_accept_cookies.html",
						"FILE|inc/functions.php",
						"FILE|index.php",
			"");
			
			// Automatisk opdatering, genereret 19-06-2013 15:06:08
			$updates["2013061901"]["text"] = "Opdatering";
			$updates["2013061901"]["update"] = array(
						"FILE|html/_user_accept_cookies.html",
						"FILE|index.php",
			"");
			
			// Automatisk opdatering, genereret 20-06-2013 13:53:39
			$updates["2013062000"]["text"] = "Opdatering";
			$updates["2013062000"]["update"] = array(
						"FILE|html/admin_layout_print.html",
			"");
			
			// Automatisk opdatering, genereret 20-06-2013 14:06:10
			$updates["2013062001"]["text"] = "Opdatering";
			$updates["2013062001"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|class/user.php",
			"");
			
			// Automatisk opdatering, genereret 25-06-2013 13:42:31
			$updates["2013062500"]["text"] = "Opdatering";
			$updates["2013062500"]["update"] = array(
						"FILE|js/spaw/config/config.php",
			"");
			
			// Automatisk opdatering, genereret 25-06-2013 13:49:18
			$updates["2013062501"]["text"] = "Opdatering";
			$updates["2013062501"]["update"] = array(
						"FILE|js/spaw/config/config.php",
						"FILE|js/spaw/dialogs/dialog.php",
			"");
			
			// Automatisk opdatering, genereret 01-07-2013 12:36:34
			$updates["2013070100"]["text"] = "Opdatering";
			$updates["2013070100"]["update"] = array(
						"FILE|Admin/pages/admin_users.php",
			"");
			
			// Automatisk opdatering, genereret 04-07-2013 10:29:26
			$updates["2013070400"]["text"] = "Opdatering";
			$updates["2013070400"]["update"] = array(
						"FILE|html/_user_accept_cookies.html",
			"");
			
			// Automatisk opdatering, genereret 30-07-2013 13:02:03
			$updates["2013073000"]["text"] = "Opdatering";
			$updates["2013073000"]["update"] = array(
						"FILE|Admin/index.php",
						"FILE|class/user.php",
			"");
			
			// Automatisk opdatering, genereret 12-08-2013 09:28:27
			$updates["2013081200"]["text"] = "Opdatering";
			$updates["2013081200"]["update"] = array(
						"FILE|Admin/pages/frameset.php",
						"FILE|Admin/pages/settings.php",
			"");
			
			// Automatisk opdatering, genereret 20-08-2013 09:49:43
			$updates["2013082000"]["text"] = "Opdatering";
			$updates["2013082000"]["update"] = array(
						"FILE|class/ajax.php",
						"FILE|html/_ajax_response.html",
						"FILE|html/_ajax_response_value.html",
						"FILE|html/_ajax_script.html",
						"FILE|js/stadel/ajax.js",
			"");
			
			// Automatisk opdatering, genereret 22-08-2013 11:08:40
			$updates["2013082200"]["text"] = "Opdatering";
			$updates["2013082200"]["update"] = array(
						"FILE|css/jquery.mobile-1.3.css",
						"FILE|js/jquery/jquery-1.9.js",
						"FILE|js/jquery/jquery.mobile-1.3.js",
			"");
			
			// Automatisk opdatering, genereret 22-08-2013 17:42:23
			$updates["2013082201"]["text"] = "Opdatering";
			$updates["2013082201"]["update"] = array(
						"FILE|class/ftp.php",
			"");
			
			// Automatisk opdatering, genereret 27-08-2013 12:43:40
			$updates["2013082700"]["text"] = "Opdatering";
			$updates["2013082700"]["update"] = array(
						"FILE|html/admin_layout_login.html",
						"FILE|html/admin_login.html",
			"");
			
			// Automatisk opdatering, genereret 27-08-2013 13:23:48
			$updates["2013082701"]["text"] = "Opdatering";
			$updates["2013082701"]["update"] = array(
						"FILE|html/admin_layout_default.html",
						"FILE|html/admin_layout_frameset.html",
						"FILE|html/admin_layout_login.html",
						"FILE|html/admin_layout_popup.html",
						"FILE|html/admin_layout_print.html",
			"");
			
			// Automatisk opdatering, genereret 29-08-2013 13:38:24
			$updates["2013082900"]["text"] = "Opdatering";
			$updates["2013082900"]["update"] = array(
						"FILE|js/spaw/config/config.php",
			"");
			
			// Automatisk opdatering, genereret 03-09-2013 12:50:57
			$updates["2013090300"]["text"] = "Opdatering";
			$updates["2013090300"]["update"] = array(
						"FOLDER|css/images",
						"FILE|css/images/animated-overlay.gif",
						"FILE|css/images/ui-bg_diagonals-thick_18_b81900_40x40.png",
						"FILE|css/images/ui-bg_diagonals-thick_20_666666_40x40.png",
						"FILE|css/images/ui-bg_flat_10_000000_40x100.png",
						"FILE|css/images/ui-bg_glass_100_f6f6f6_1x400.png",
						"FILE|css/images/ui-bg_glass_100_fdf5ce_1x400.png",
						"FILE|css/images/ui-bg_glass_65_ffffff_1x400.png",
						"FILE|css/images/ui-bg_gloss-wave_35_f6a828_500x100.png",
						"FILE|css/images/ui-bg_highlight-soft_100_eeeeee_1x100.png",
						"FILE|css/images/ui-bg_highlight-soft_75_ffe45c_1x100.png",
						"FILE|css/images/ui-icons_222222_256x240.png",
						"FILE|css/images/ui-icons_228ef1_256x240.png",
						"FILE|css/images/ui-icons_ef8c08_256x240.png",
						"FILE|css/images/ui-icons_ffd27a_256x240.png",
						"FILE|css/images/ui-icons_ffffff_256x240.png",
						"FILE|css/jquery-ui-1.10.css",
						"FILE|html/jquery.html",
						"FILE|js/jquery/jquery-ui-1.10.js",
			"");
			
			// Automatisk opdatering, genereret 09-09-2013 09:36:01
			$updates["2013090900"]["text"] = "Opdatering";
			$updates["2013090900"]["update"] = array(
						"FILE|html/_ajax_response_value.html",
						"FILE|html/jquery-mobile.html",
						"FILE|html/jquery.html",
			"");
			
			// Automatisk opdatering, genereret 17-09-2013 12:17:46
			$updates["2013091700"]["text"] = "Opdatering";
			$updates["2013091700"]["update"] = array(
						"FILE|Admin/pages/settings.php",
						"FILE|class/tpl.php",
						"FILE|html/_ajax_response_value.html",
						"FILE|index.php",
			"");
			
			// Automatisk opdatering, genereret 17-09-2013 12:19:54
			$updates["2013091701"]["text"] = "Tilføjelse af indeksering og omskrivning af links";
			$updates["2013091701"]["update"] = array(
						"FILE|Admin/pages/settings.php",
						"FILE|index.php",
			"");
			?>