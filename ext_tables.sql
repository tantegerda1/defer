--
-- Deferred action, object or whatever
--
create table tx_defer_deferred (
	token varchar(64) DEFAULT '' NOT NULL,

	type varchar(64) DEFAULT '' NOT NULL,
	data text,
	valid_till datetime default '0000-00-00 00:00:00' NOT NULL,

	PRIMARY KEY (token),
	KEY tstamp (valid_till)
);
