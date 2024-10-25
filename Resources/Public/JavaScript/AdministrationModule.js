/**
 * Module: TYPO3/CMS/QcComments/AdministrationModule
 */
var exportFunction;
var technicalProblemFixed;
define(['jquery'], function($) {
    'use strict';
    $(document).ready(function (){
         exportFunction  = function (event, actionName){
            event.preventDefault();
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
                'commentReason' : $('#commentReason').val()
            }
            let url = '';
            switch (actionName){
              case 'comments' :  url = TYPO3.settings.ajaxUrls.export_comments; break;
              case 'statistics' :  url = TYPO3.settings.ajaxUrls.export_statistics; break;
              case 'technicalProblems' :  url = TYPO3.settings.ajaxUrls.export_technicalProblems; break;
            }
            require(['TYPO3/CMS/Core/Ajax/AjaxRequest'], function (AjaxRequest) {
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
        }
         $('.technicalProblemFixed').on('click', function(){
           let technicalProblemUid = $(this).attr('technical-probelm-uid');
           let url = TYPO3.settings.ajaxUrls.problem_fixed;
           require(['TYPO3/CMS/Core/Ajax/AjaxRequest'], function (AjaxRequest) {
             new AjaxRequest(url)
               .withQueryArguments({technicalProblemUid: technicalProblemUid})
               .get()
               .then(async function (response) {
                 response.resolve().then(function (result){
                   if(result != null){
                     alert('success')
                     var link=document.getElementById('problem-'+technicalProblemUid);
                     link.href=response.response.url
                     console.log(link.href)
                     link.click();
                   }
                 });
               });
           })
         })
    })
})
