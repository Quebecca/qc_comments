<?php

namespace Qc\QcComments\Controller\Backend;

use Qc\QcComments\Domain\Filter\CommentsFilter;
use Qc\QcComments\Domain\Filter\DeletedCommentsFilter;
use Qc\QcComments\Service\CommentsTabService;
use Qc\QcComments\Service\DeletedCommentsTabService;
use Qc\QcComments\Service\TechnicalProblemsTabService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;


class DeletedCommentBEController extends QcCommentsBEController
{
    /**
     * @param DeletedCommentsFilter|null $filter
     * @param string $operation
     * @return ResponseInterface
     * @throws Exception
     */
    public function deletedCommentsAction(DeletedCommentsFilter $filter = null, string $operation = ''): ResponseInterface {
        $this->addMainMenu('deletedComments');
        if($operation === 'reset-filters'){
            $filter = new DeletedCommentsFilter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(DeletedCommentsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => 'deletedComments'
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
                        null, (array)[$data['numberOfSubPages'], $data['maxRecords']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }

            $this
                ->moduleTemplate
                ->assignMultiple(
                    [
                        'commentHeaders' => $data['commentHeaders'],
                        'stats' => $data['stats'],
                        'comments' => $data['comments'],
                        'pagesId' => $data['pagesId'],
                        'currentPageId' => $data['currentPageId'],
                        'isDeleteButtonEnabled' => $this->qcBeModuleService->isDeleteButtonEnabled()
                    ]
                );
        }
        $filter = $this->qcBeModuleService->processFilter($filter);
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('DeletedComments');
    }


    /**
     * This function is used to export comments records on a csv file
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportCommentsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $filter = $this->qcBeModuleService->getFilterFromRequest($request);
        $filter->setDepth( intval($request->getQueryParams()['parameters']['depth']));
        $filter->setUseful($request->getQueryParams()['parameters']['useful']);
        $currentPageId = intval($request->getQueryParams()['parameters']['currentPageId']);
        return $this->qcBeModuleService->exportCommentsData($filter, $currentPageId);
    }


}
