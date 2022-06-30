# Module configuration
module.tx_qcComments {
    persistence {
        storagePid =
    }

    view {
        templateRootPaths.0 = EXT:qc_comments//Resources/Private/Templates/
        templateRootPaths.1 = {$module.tx_qcComments.view.templateRootPath}
        partialRootPaths.0 = EXT:qc_comments/Resources/Private/Partials/
        partialRootPaths.1 = {$module.tx_qcComments.view.partialRootPath}
        layoutRootPaths.0 = EXT:qc_comments/Resources/Private/Layouts/
        layoutRootPaths.1 = {$module.tx_qcComments.view.layoutRootPath}
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