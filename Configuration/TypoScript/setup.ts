# Module configuration

plugin.commentsForm {
    view {
        templateRootPath = {$plugin.commentsForm.view.templateRootPath}
        partialRootPath = {$plugin.commentsForm.view.partialRootPath}
        layoutRootPath = {$plugin.commentsForm.view.layoutRootPath}
    }
    settings{

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
        maxComments = 2000
        maxStats = 100
        cropMaxLength = 95
        csvExport {
            filename {
                dateFormat = YmdHi
            }
        }
    }
}