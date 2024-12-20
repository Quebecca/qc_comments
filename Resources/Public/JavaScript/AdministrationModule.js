/**
 * Module: TYPO3/CMS/QcComments/AdministrationModule
 */

import $ from 'jquery';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

var exportFunction;
var technicalProblemFixed;
$(document).ready(function (){
    $("#exportBtn").on('click', function(event){
        event.preventDefault();
        let actionName = document.querySelector("#exportBtn").dataset.action;
        let pageIdsElements = $('.pageId')
        let  pageIds = [];
        pageIdsElements.toArray().forEach(function (item) {
            pageIds.push($(item).attr('data-tr-label'))
        });

        let csvSettings = {
            'separator' : $('#separator').attr('data-tr-label'),
            'enclosure' : $('#enclosure').attr('data-tr-label'),
            'escape' : $('#escape').attr('data-tr-label'),
            'dateFormat' : $('#dateFormat').attr('data-tr-label'),
        };
        let parameters = {
            'lang' : $('#lang option:selected').val(),
            'depth' : $('#depth option:selected').val(),
            'useful' : $('#useful option:selected').val(),
            'includeEmptyPages' : $('#includeEmptyPages').is(":checked"),
            'selectDateRange' : $('#selectDateRange option:selected').val(),
            'pagesId' : pageIds,
            'csvSettings' : csvSettings,
            'currentPageId' : $('#currentPageId').attr('data-tr-label'),
            'startDate' : $('#startDate').val(),
            'endDate' : $('#endDate').val(),
            'commentReason' : $('#commentReason').val(),
            'includeFixedTechnicalProblems' : $('#includeFixedTechnicalProblems').is(":checked")
        }
        let url = '';
        switch (actionName){
            case 'comments' :  url = TYPO3.settings.ajaxUrls.export_comments; break;
            case 'hiddenComments' :  url = TYPO3.settings.ajaxUrls.export_hiddenComments; break;
            case 'statistics' :  url = TYPO3.settings.ajaxUrls.export_statistics; break;
            case 'technicalProblems' :  url = TYPO3.settings.ajaxUrls.export_technicalProblems; break;
        }
        new AjaxRequest(url)
            .withQueryArguments({parameters: parameters})
            .get()
            .then(async function (response) {
                response.resolve().then(function (result){
                    if(result != null){
                        var blob=new Blob([result]);
                        var link=document.createElement('a');
                        link.href=response.response.url
                        link.click();
                    }
                });
            });
    })

})
