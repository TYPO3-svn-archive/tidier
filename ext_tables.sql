

CREATE TABLE tx_tidier_error (
	uid int(11) NOT NULL auto_increment,
	error_pid int(11) DEFAULT '0' NOT NULL,
	error_code_uid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	error_line mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	error_column mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid)
);

CREATE TABLE tx_tidier_errorcode (
	uid int(11) NOT NULL auto_increment,
	error_type varchar(20) DEFAULT '' NOT NULL,
	error_code varchar(255) DEFAULT '' NOT NULL,
	error_priority tinyint(3) UNSIGNED DEFAULT '0' NOT NULL,
	error_text text,
	PRIMARY KEY (uid)
);