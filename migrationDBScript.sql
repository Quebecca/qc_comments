CREATE TABLE tx_gabarit_pgu_form_comments_problems (
   pid int(11) DEFAULT '0' NOT NULL,
   tstamp int(11) unsigned DEFAULT '0' NOT NULL,
   crdate int(11) unsigned DEFAULT '0' NOT NULL,
   hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
   starttime int(11) unsigned DEFAULT '0' NOT NULL,
   endtime int(11) unsigned DEFAULT '0' NOT NULL,
   sorting int(11) DEFAULT '0' NOT NULL,

   sys_language_uid int(11) DEFAULT '0' NOT NULL,
   l10n_parent int(11) DEFAULT '0' NOT NULL,
   l10n_diffsource mediumblob,
);

ALTER table tx_gabarit_pgu_form_comments_problems
    CHANGE date_heure date_houre varchar(20) DEFAULT '' NOT NULL,
    CHANGE utile useful tinyint(1) DEFAULT '0' NOT NULL,
    CHANGE commentaire comment text,
    CHANGE etat_suivi comment state_follow_up varchar(25) DEFAULT '' NOT NULL,
    CHANGE note_suivi comment  note_follow_up text;

ALTER TABLE tx_gabarit_pgu_form_comments_problems
    RENAME TO tx_qccomments_domain_model_comment;

