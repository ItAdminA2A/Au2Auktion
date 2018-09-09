<?
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

	$db->execute("
		CREATE TABLE " . $_table_prefix . "_ip_ban (
		  id int(10) unsigned NOT NULL auto_increment,
		  ip varchar(15) NOT NULL default '',
		  time datetime NOT NULL default '0000-00-00 00:00:00',
		  reason varchar(25) NOT NULL default '',
		  PRIMARY KEY  (id)
		)
		");
	
	$db->execute("
		CREATE TABLE " . $_table_prefix . "_pages_ (
		  id int(10) unsigned NOT NULL auto_increment,
		  sub_id int(10) unsigned NOT NULL default '0',
		  title varchar(50) NOT NULL default '',
		  sub_title varchar(255) NOT NULL default '',
		  time_from datetime default NULL,
		  time_to datetime default NULL,
		  `order` int(10) unsigned NOT NULL default '0',
		  content text NOT NULL,
		  active tinyint(1) unsigned NOT NULL default '0',
		  `link` tinyint(1) unsigned NOT NULL default '0',
		  `public` tinyint(1) unsigned NOT NULL default '0',
		  frontpage tinyint(1) unsigned NOT NULL default '0',
		  layout varchar(50) not null default 'default',
		  user_group varchar(25) not null default '',
		  lang_id varchar(2) not null default 'da',
		  meta_title varchar(255) not null default '',
		  meta_description text not null default '',
		  meta_keywords text not null default '',
		  `edit_mode` VARCHAR( 10 ) NOT NULL DEFAULT 'simple',
		  sub_menu varchar(50) not null default '',
		  alt_url varchar(250) not null default '',
		  no_link tinyint(1) unsigned not null default '0',
		  PRIMARY KEY  (id)
		)
		");
	
	$db->execute("
		CREATE TABLE " . $_table_prefix . "_settings_ (
		  id varchar(50) NOT NULL default '',
		  value text NOT NULL,
		  ereg varchar(50) NOT NULL default '',
		  PRIMARY KEY  (id)
		)
		");
	
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_TITLE', 'Stadel.dk CMS Demo', '^.+$')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_DESCRIPTION', 'Demo-side for Stadel.dk CMS', '^.+$')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_KEYWORDS', 'cms,stadel,demo,webdesign', '^.+$')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_LAYOUT', 'Standard', '^.+$')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_EMAIL', 'info@demosider.dk', '^[a-zA-Z0-9\\._-]+@[a-zA-Z0-9\\.-]+\\.[a-zA-Z]+$')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('MODULE_LAYOUT', '', '')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('SITE_EMAIL_NAME', 'Stadel.dk CMS Demo', '')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('EMAIL_METHOD', '', '')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('EMAIL_SMTP_HOST', '', '')
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_settings_ VALUES ('EMAIL_SMTP_PORT', '25', '')
		");
		
	$db->execute("
		CREATE TABLE " . $_table_prefix . "_user_admin (
		  id int(10) unsigned NOT NULL auto_increment,
		  cvr varchar(10) NOT NULL default '',
		  username varchar(50) NOT NULL default '',
		  password varchar(32) NOT NULL default '',
		  company varchar(50) NOT NULL default '',
		  name varchar(50) NOT NULL default '',
		  address varchar(255) NOT NULL default '',
		  zipcode varchar(10) NOT NULL default '',
		  city varchar(50) NOT NULL default '',
		  phone varchar(15) NOT NULL default '',
		  mobile varchar(15) NOT NULL default '',
		  email varchar(50) NOT NULL default '',
		  birthday date NOT NULL default '0000-00-00',
		  create_ip varchar(15) NOT NULL default '',
		  create_time datetime default NULL,
		  login_ip varchar(15) NOT NULL default '',
		  login_time datetime default NULL,
		  extra_administrator tinyint(1) unsigned NOT NULL default '0',
		  extra_rights text NOT NULL,
		  active TINYINT(1) NOT NULL DEFAULT '1',
		  `rules` VARCHAR(250) NOT NULL DEFAULT '',
		  lang_id VARCHAR(2) NOT NULL DEFAULT 'da',
		  PRIMARY KEY  (id),
		  UNIQUE KEY username (username)
		)
		");
		
	$db->execute("
		INSERT INTO " . $_table_prefix . "_user_admin
			(id, username, password, create_ip, create_time, extra_administrator, active)
			VALUES
			(1, 'Administrator', '7b7bc2512ee1fedcd76bdc68926d4f7b', '1.2.3.4', NOW(), 1, 1)
		");
		
	$db->execute("
		CREATE TABLE " . $_table_prefix . "_user_admin_log (
		  id int(10) unsigned NOT NULL auto_increment,
		  user_id int(10) unsigned NOT NULL default '0',
		  time datetime NOT NULL default '0000-00-00 00:00:00',
		  ip varchar(15) NOT NULL default '',
		  action varchar(255) NOT NULL default '',
		  PRIMARY KEY  (id)
		)
		");
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_languages_` (
		  `id` char(2) NOT NULL default '',
		  `title` varchar(25) NOT NULL default '',
		  `default` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		)
		");

	$db->execute("
		INSERT INTO `" . $_table_prefix . "_languages_` VALUES ('da', 'Dansk', 1);
		");
		
	$db->execute("
		CREATE TABLE " . $_table_prefix . "_admin_links (
			`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`title` VARCHAR( 50 ) NOT NULL ,
			`icon` VARCHAR( 100 ) NOT NULL ,
			`url` VARCHAR( 100 ) NOT NULL
		)
		");
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_domains_` (
			`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`domain` VARCHAR( 75 ) NOT NULL ,
			`layout` VARCHAR( 50 ) NOT NULL ,
			`lang_id` VARCHAR( 2 ) NOT NULL ,
			`site_title` VARCHAR( 100 ) NOT NULL ,
			`site_description` VARCHAR( 100 ) NOT NULL ,
			`site_keywords` VARCHAR( 100 ) NOT NULL ,
			`site_email_name` VARCHAR( 100 ) NOT NULL ,
			`site_email` VARCHAR( 100 ) NOT NULL ,
			`user_settings` TEXT NOT NULL,
			redirect VARCHAR(255) NOT NULL DEFAULT '',
			email_attach_images VARCHAR( 1 ) NOT NULL ,
			email_method VARCHAR( 5 ) NOT NULL ,
			email_smtp_host VARCHAR( 100 ) NOT NULL ,
			email_smtp_port VARCHAR( 5 ) NOT NULL ,
			email_smtp_user VARCHAR( 100 ) NOT NULL ,
			email_smtp_pass VARCHAR( 100 ) NOT NULL ,
			return_email VARCHAR( 100 ) NOT NULL ,
			return_email_server VARCHAR( 100 ) NOT NULL ,
			return_email_user VARCHAR( 100 ) NOT NULL ,
			return_email_pass VARCHAR( 100 ) NOT NULL ,
			`default` TINYINT(1) NOT NULL DEFAULT '0'
			)
		");
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_settings_module` (
			`module` VARCHAR( 50 ) NOT NULL ,
			`id` VARCHAR( 50 ) NOT NULL ,
			`value` VARCHAR( 255 ) NOT NULL ,
			PRIMARY KEY ( `module` , `id` )
		)	
		");
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_smart_urls` (
		  `real_url` varchar(255) NOT NULL default '',
		  `smart_url` varchar(255) NOT NULL default '',
		  PRIMARY KEY  (`smart_url`),
		  UNIQUE KEY `real_url` (`real_url`)
		)	
		");
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_log_messages` (
			`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`time` DATETIME NOT NULL ,
			`message` TEXT NOT NULL
		)
		");
		
	$db->execute("
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
		
	$db->execute("
		CREATE TABLE `" . $_table_prefix . "_admin_notes` (
		  `module` varchar(50) NOT NULL,
		  `page` varchar(50) NOT NULL,
		  `note` text NOT NULL,
		  PRIMARY KEY  (`module`,`page`)
		)
		");
		
	cms_setting("writable_file_mode", "0777");
	cms_setting("default_file_mode", "0644");
	cms_setting("default_folder_mode", "0755");
	cms_setting("convert_date_today", "%H:%M");
	cms_setting("convert_date_year", "%d. %B");
	cms_setting("convert_date_default", "%d. %B %Y");
?>