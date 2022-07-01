# table for the comment or problem form at the bottom of the page
# todo translate columns to english
# todo add T3 columns

CREATE TABLE tx_gabarit_pgu_form_comments_problems (
       uid int(11) NOT NULL auto_increment,
       date_heure varchar(20) DEFAULT '' NOT NULL,
       utile tinyint(1) DEFAULT '0' NOT NULL,
       commentaire text,
       uid_orig int(11) UNSIGNED DEFAULT '0' NOT NULL,
       url_orig text,
       theme varchar(200) DEFAULT '' NOT NULL,
       uid_perms_group smallint(5) UNSIGNED DEFAULT '0' NOT NULL,
       etat_suivi varchar(25) DEFAULT '' NOT NULL,
       note_suivi text,
       PRIMARY KEY (uid)
);