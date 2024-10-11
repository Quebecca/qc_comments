# table for the users comments tx_qccomments_users_comments
CREATE TABLE tx_qccomments_domain_model_comment (

       uid int(11) NOT NULL auto_increment,
       pid int(11) DEFAULT '0' NOT NULL,

       date_hour varchar(20) DEFAULT '' NOT NULL,
       useful varchar(4) DEFAULT '0' NOT NULL,
       comment text,
       uid_orig int(11) UNSIGNED DEFAULT '0' NOT NULL,
       url_orig text,
       theme varchar(200) DEFAULT '' NOT NULL,
       uid_perms_group smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
       state_follow_up varchar(25) DEFAULT '' NOT NULL,
       note_follow_up text,
			 reason_code varchar(100),
			 reason_long_label text,
			 reason_short_label text,
			 submitted_form_uid text,
			 user_uid_fixing_problem int,
			 fixing_date varchar(20) DEFAULT '' NOT NULL,

       tstamp int(11) unsigned DEFAULT '0' NOT NULL,
       crdate int(11) unsigned DEFAULT '0' NOT NULL,
       hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
			 deleted smallint(5) unsigned DEFAULT '0' NOT NULL,
       starttime int(11) unsigned DEFAULT '0' NOT NULL,
       endtime int(11) unsigned DEFAULT '0' NOT NULL,
       sorting int(11) DEFAULT '0' NOT NULL,

       sys_language_uid int(11) DEFAULT '0' NOT NULL,
       l10n_parent int(11) DEFAULT '0' NOT NULL,
       l10n_diffsource mediumblob,

       PRIMARY KEY (uid)

);

CREATE TABLE pages (
    tx_select_comments_form_page_mode varchar(50) DEFAULT ''
);
