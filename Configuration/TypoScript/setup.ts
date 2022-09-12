# Module configuration

plugin.commentsForm {
    view {
        templateRootPath = {$plugin.commentsForm.view.templateRootPath}
        partialRootPath = {$plugin.commentsForm.view.partialRootPath}
        layoutRootPath = {$plugin.commentsForm.view.layoutRootPath}
    }
    settings{

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
        }
        statistics {
            maxRecords = 30
        }

        cropMaxLength = 95
        csvExport {
            filename {
                dateFormat = YmdHi
            }
        }
    }
}