<?php

namespace Qc\QcComments\Controller\Backend;

use Qc\QcComments\Domain\Filter\HiddenCommentsFilter;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use Qc\QcComments\Service\HiddenCommentsTabService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;


class HiddenCommentsBEController extends QcCommentsBEController
{
    /**
     * @param HiddenCommentsFilter|null $filter
     * @param string $operation
     * @return ResponseInterface
     * @throws Exception
     */
    public function hiddenCommentsAction(HiddenCommentsFilter $filter = null, string $operation = ''): ResponseInterface {
        $this->addMainMenu('hiddenComments');
        if($operation === 'reset-filters'){
            $filter = new HiddenCommentsFilter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(HiddenCommentsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => 'hiddenComments'
            ]);


        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();

        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $filter = $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);
            }
            $data = $this->qcBeModuleService->getComments();
            if($data['tooMuchResults'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, [$data['maxRecords']]);
                $this->addFlashMessage($message, '', ContextualFeedbackSeverity::WARNING);
            }
            if($data['tooMuchPages'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchPages',
                        null,[$data['numberOfSubPages']]);
                $this->addFlashMessage($message, '', ContextualFeedbackSeverity::WARNING);
            }

            $this
                ->moduleTemplate
                ->assignMultiple(
                    [
                        'commentHeaders' => $data['commentHeaders'],
                        'comments' => $data['comments'],
                        'pagesId' => $data['pagesId'],
                        'currentPageId' => $data['currentPageId'],
                        'isDeleteButtonEnabled' => $this->qcBeModuleService->isDeleteButtonEnabled('hiddenComments')
                    ]
                );
        }
        $filter = $this->qcBeModuleService->processFilter($filter);
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('HiddenComments');
    }


    /**
     * This function is used to remove the comment (remove = 1)
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function deleteCommentAction(){
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(HiddenCommentsTabService::class);
        $recordUid = $this->request->getArguments()['commentUid'];
        if($recordUid){
            $this->qcBeModuleService->deleteComment($recordUid);
        }
        return new ForwardResponse('hiddenComments');
    }



    /**
     * This function is used to export comments records on a csv file
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function exportHiddenCommentsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(HiddenCommentsTabService::class);
        $root_id = intval($request->getQueryParams()['parameters']['currentPageId']);
        $this->qcBeModuleService->setRootId($root_id);
        $filter = $this->qcBeModuleService->processFilter();
        return $this->qcBeModuleService->exportHiddenCommentsData($filter);
    }


}
