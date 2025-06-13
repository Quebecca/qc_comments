/**
 * Module: TYPO3/CMS/QcComments/AdministrationModule
 */
import $ from 'jquery';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';

$(document).ready(function () {
  $("#exportBtn").on('click', function (event) {
    event.preventDefault();
    const exportLabels = {
      progress: $('#exporting-progress').data('tr-label'),
      progressMessage: $('#exporting-progress-message').data('tr-label'),
      success: $('#exporting-success').data('tr-label'),
      successMessage: $('#exporting-success-message').data('tr-label'),
      fail: $('#exporting-fail').data('tr-label'),
      failMessage: $('#exporting-fail-message').data('tr-label')
    };
    Modal.advanced({
      title: exportLabels.progress,
      content: exportLabels.progressMessage,
      severity: Severity.info,
      staticBackdrop: true,
      closable: false,  // This prevents closing by clicking backdrop
      buttons: [],      // No buttons shown
      callback: function(currentModal) {
        // Remove close button from modal header
        const closeButton = currentModal.querySelector('.t3js-modal-close');
        if (closeButton) {
          closeButton.remove();
        }
      }
    });

    const escHandler = function(e) {
      if (e.key === 'Escape') {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    };
    document.addEventListener('keydown', escHandler);

    let actionName = document.querySelector("#exportBtn").dataset.action;
    let pageIdsElements = $('.pageId');
    let pageIds = [];
    pageIdsElements.toArray().forEach(function (item) {
      pageIds.push($(item).attr('data-tr-label'));
    });

    let csvSettings = {
      'separator': $('#separator').attr('data-tr-label'),
      'enclosure': $('#enclosure').attr('data-tr-label'),
      'escape': $('#escape').attr('data-tr-label'),
      'dateFormat': $('#dateFormat').attr('data-tr-label'),
    };
    let parameters = {
      'lang': $('#lang option:selected').val(),
      'depth': $('#depth option:selected').val(),
      'useful': $('#useful option:selected').val(),
      'includeEmptyPages': $('#includeEmptyPages').is(":checked"),
      'selectDateRange': $('#selectDateRange option:selected').val(),
      'pagesId': pageIds,
      'csvSettings': csvSettings,
      'currentPageId': $('#currentPageId').attr('data-tr-label'),
      'startDate': $('#startDate').val(),
      'endDate': $('#endDate').val(),
      'commentReason': $('#commentReason').val(),
      'includeFixedTechnicalProblems': $('#includeFixedTechnicalProblems').is(":checked")
    };

    let url = '';
    switch (actionName) {
      case 'comments': url = TYPO3.settings.ajaxUrls.export_comments; break;
      case 'hiddenComments': url = TYPO3.settings.ajaxUrls.export_hiddenComments; break;
      case 'statistics': url = TYPO3.settings.ajaxUrls.export_statistics; break;
      case 'technicalProblems': url = TYPO3.settings.ajaxUrls.export_technicalProblems; break;
    }

    // Execute export
    new AjaxRequest(url)
      .withQueryArguments({ parameters: parameters })
      .get()
      .then(async function (response) {
        response.resolve().then(function (result) {
          setTimeout(()=>{
            if (result != null) {
              var link = document.createElement('a');
              link.href = response.response.url;
              link.click();
              Modal.dismiss();
              document.removeEventListener('keydown', escHandler);
              Modal.show(
                  exportLabels.success,
                  exportLabels.successMessage,
                  Severity.info,
                  [
                    {
                      text: 'OK',
                      active: true,
                      btnClass: 'btn-primary',
                      trigger: () => Modal.dismiss()
                    }
                  ]
              );
            }
          }, 500)
        });
      })
      .catch(function (error) {
        console.error(error);
        // Show error modal if export fails
        Modal.dismiss();
        document.removeEventListener('keydown', escHandler);
        Modal.show(
          exportLabels.fail,
          exportLabels.failMessage,
          Severity.error,
          [
            {
              text: 'OK',
              active: true,
              btnClass: 'btn-primary',
              trigger: () => Modal.dismiss()
            }
          ]
        );
      });

  });
});
