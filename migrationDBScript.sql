CREATE TABLE tx_qccomments_domain_model_comment LIKE tx_gabarit_pgu_form_comments_problems;

INSERT INTO tx_qccomments_domain_model_comment SELECT * FROM tx_gabarit_pgu_form_comments_problems;

ALTER TABLE tx_qccomments_domain_model_comment
    ADD COLUMN  pid int(11) DEFAULT '0' NOT NULL,
     ADD COLUMN   tstamp int(11) unsigned DEFAULT '0' NOT NULL,
     ADD COLUMN   crdate int(11) unsigned DEFAULT '0' NOT NULL,
     ADD COLUMN   hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
     ADD COLUMN   starttime int(11) unsigned DEFAULT '0' NOT NULL,
     ADD COLUMN   endtime int(11) unsigned DEFAULT '0' NOT NULL,
     ADD COLUMN  sorting int(11) DEFAULT '0' NOT NULL,
     ADD COLUMN  sys_language_uid int(11) DEFAULT '0' NOT NULL,
     ADD COLUMN   l10n_parent int(11) DEFAULT '0' NOT NULL,
     ADD COLUMN   l10n_diffsource mediumblob;

ALTER table tx_qccomments_domain_model_comment
    CHANGE date_heure date_houre varchar(20) DEFAULT '' NOT NULL,
    CHANGE utile useful tinyint(1) DEFAULT '0' NOT NULL,
    CHANGE commentaire comment text,
    CHANGE etat_suivi state_follow_up varchar(25) DEFAULT '' NOT NULL,
    CHANGE note_suivi note_follow_up text;

