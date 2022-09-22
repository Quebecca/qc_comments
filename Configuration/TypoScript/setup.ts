# Module configuration

plugin.commentsForm {
    view {
        templateRootPath = {$plugin.commentsForm.view.templateRootPath}
        partialRootPath = {$plugin.commentsForm.view.partialRootPath}
        layoutRootPath = {$plugin.commentsForm.view.layoutRootPath}
    }
    settings {
        comments {
            // if empty default will be 500
            maxCharacters = 5
            // if empty default value will be assigned
            formSectionTitle = Évaluation de page
            isThePageUseful = L’information sur cette page vous a-t-elle été utile?
            haveYouAComment = Avez-vous un commentaire à nous transmettre ou un problème à signaler ?
            haveYouACommentDescription = Évitez d’inscrire des renseignements personnels. Notez que vous ne recevrez aucune réponse.
            formSubmittedSuccessfully = Votre message a été envoyé. Merci de nous aider à améliorer Québec.ca. Vos commentaires sont importants pour nous.
        }

        // Activate recaptcha
        recaptcha {
            enabled = 1
            sitekey = {$plugin.tx_invisiblerecaptcha.sitekey}
            secret = {$plugin.tx_invisiblerecaptcha.secretkey}
        }
    }
    persistence {
        classes {
            Qc\QcComments\Domain\Model\Comment {
                mapping {
                    columns {
                        date_houre.mapOnProperty = dateHoure
                        url_orig.mapOnProperty = urlOrig
                        uid_orig.mapOnProperty = uidOrig
                        uid_perms_group.mapOnProperty = uidPermsGroup
                    }
                }
            }
        }
    }
}


module.tx_qccomments {
    persistence {
        storagePid =
    }

    view {
        templateRootPaths.0 = EXT:qc_comments/Resources/Private/Templates/
        templateRootPaths.1 = {$module.tx_qccomments.view.templateRootPath}
        partialRootPaths.0 = EXT:qc_comments/Resources/Private/Partials/
        partialRootPaths.1 = {$module.tx_qccomments.view.partialRootPath}
        layoutRootPaths.0 = EXT:qc_comments/Resources/Private/Layouts/
        layoutRootPaths.1 = {$module.tx_qccomments.view.layoutRootPath}
    }
    settings {
        comments {
            maxRecords = 100
            numberOfSubPages = 50
            maxCharacters = 10
        }
        statistics {
            maxRecords = 30
        }

        csvExport {
            filename {
                dateFormat = YmdHi
            }
        }
    }
}