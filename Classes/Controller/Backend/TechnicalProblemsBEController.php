<?php

namespace Qc\QcComments\Controller\Backend;

use Qc\QcComments\Domain\Filter\TechnicalProblemsFilter;
use Qc\QcComments\Service\CommentsTabService;
use Qc\QcComments\Service\TechnicalProblemsTabService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

class TechnicalProblemsBEController extends QcCommentsBEController
{

    /**
     * @param TechnicalProblemsFilter|null $filter
     * @param string $operation
     * @return ResponseInterface
     */
    public function technicalProblemsAction(TechnicalProblemsFilter $filter = null, string $operation = ''): ResponseInterface{

        $this->qcBeModuleService
            = GeneralUtility::makeInstance(TechnicalProblemsTabService::class);
        if($this->request->getArguments()['recordUidToRemove'] ?? false) {
            $this->qcBeModuleService->technicalProblemFixed($this->request->getArguments()['recordUidToRemove']);
        }

        if($operation === 'reset-filters'){
            $filter = new TechnicalProblemsFilter();
        }

        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => 'technicalProblems'
            ]);

        $this->addMainMenu('technicalProblems');

        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();

        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);
            }
            $data = $this->qcBeModuleService->getComments();

           if($data['tooMuchResults'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, [$data['maxRecords']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
           }

            if($data['tooMuchPages'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchPages',
                        null, [$data['numberOfSubPages']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }
            $this->moduleTemplate
             ->assignMultiple(
                 [
                     'commentHeaders' => $data['commentHeaders'],
                     'stats' => $data['stats'] ?? [],
                     'comments' => $data['comments'],
                     'pagesId' => $data['pagesId'],
                     'currentPageId' => $data['currentPageId'],
                     'isFixButtonEnabled' => $this->qcBeModuleService->isFixButtonEnabled(),
                     'isDeleteButtonEnabled' => $this->qcBeModuleService->isDeleteButtonEnabled('technicalProblems')
                 ]
             );
        }
        $filter = $this->qcBeModuleService->processFilter();
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('TechnicalProblems');
    }

    /**
     * This function is used to mark technical problem as solved
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function markProblemAsFixedAction()
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(TechnicalProblemsTabService::class);
        $recordUid = $this->request->getArguments()['technicalProblemUid'];
        if($recordUid){
            $this->qcBeModuleService->markProblemAsFixed($recordUid);
        }
        return new ForwardResponse('technicalProblems');
    }

    /**
     * This function is used to delete the comment (deleted = 1)
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function deleteTechnicalProblemsAction(): ForwardResponse
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $recordUid = $this->request->getArguments()['commentUid'];
        if($recordUid){
            $this->qcBeModuleService->deleteComment($recordUid);
        }
        return new ForwardResponse('technicalProblems');
    }

    /**
     * This function is used to export technical problems records on a csv file
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function exportTechnicalProblemsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(TechnicalProblemsTabService::class);
        $root_id = intval($request->getQueryParams()['parameters']['currentPageId']);
        $this->qcBeModuleService->setRootId($root_id);
        $filter = $this->qcBeModuleService->processFilter();
        $currentPageId = intval($request->getQueryParams()['parameters']['currentPageId']);
        return $this->qcBeModuleService->exportTechnicalProblemsData($filter);
    }
}
