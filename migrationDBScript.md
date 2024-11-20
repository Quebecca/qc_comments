# DataBase migration script

Les enregistrements de qc_comments sont enregistrés dans la table **tx_qccomments_domain_model_comment**, d’où il faut copier les commentaires stockés dans la table **tx_gabarit_pgu_form_comments_problems** par pgu_commentaires.
Pour cela, avant d’installer l’extension qc_comments, il faut lancer les Scripts SQL suivants :

## Création de la table :

        CREATE TABLE tx_qccomments_domain_model_comment LIKE tx_gabarit_pgu_form_comments_problems;

## Copie les valeurs enregistrées par pgu_commentaires dans la nouvelle table :
        INSERT INTO tx_qccomments_domain_model_comment SELECT * FROM tx_gabarit_pgu_form_comments_problems;

## Ajouter des colonnes de plus

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

## Renommer les colonnes déjà existantes :

            ALTER table tx_qccomments_domain_model_comment
                CHANGE date_heure date_hour varchar(20) DEFAULT '' NOT NULL,
                CHANGE utile useful tinyint(1) DEFAULT '0' NOT NULL,
                CHANGE commentaire comment text,
                CHANGE etat_suivi state_follow_up varchar(25) DEFAULT '' NOT NULL,
                CHANGE note_suivi note_follow_up text;

## Cacher la section des commentaires pour les pages suivantes :

Ce script cache la section des commentaires sur les pages suivantes, en se basant sur la configuration typoscript utilisée lors d’utilisation de pgu_commentaire

# cf ticket 3053 de gitlab
            Update pages
            set tx_select_comments_form_page_mode = "mode 3"
            where uid in (3, 12, 167, 664, 1210, 1426, 1428, 1415, 1348,
                                        827, 1886, 1876, 1865, 1855, 1845, 1835, 1825,
                                        1815, 1805, 1795, 1785, 1775, 1765, 1755,
                                        1745, 1735, 1725, 1715, 1685, 1705, 1695, 1675,
                                        1866, 2505, 2506, 2507, 2508, 2509, 2510, 2511, 2512, 2513, 2514,
                                        2515, 2516, 2517, 2518, 2519, 2926, 2927, 5854, 5871,
                                        6089, 6090, 6104, 6129, 6095, 6298, 6375, 6609, 6764, 6769, 2925, 6557,
                                        1514, 17, 4751, 2632, 5057, 6391, 475, 4796, 5156, 5875, 5880, 6208, 6182, 7078, 7255);

## Activer la section commentaires pour les pages suivantes :

            Update pages
            set tx_select_comments_form_page_mode = "mode 2"
            where uid in (1345 , 2212 , 4351 , 5069 , 5635 , 2211 , 4508,
                                        4502 , 4499 , 2631 , 1608 , 4753 , 5056 , 5391);


/*
    ['Non précisé', 'not specified', ''],
    ['Afficher pour cette page et ses sous-pages', 'mode 1', ''],
    ['Afficher pour cette page seulement', 'mode 2', ''],
    ['Masquer pour cette page et ses sous-pages', 'mode 3', ''],
    ['Masquer pour cette page seulement', 'mode 4', ''],
*/
