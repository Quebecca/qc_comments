<?php
namespace Qc\QcComments\Controller\Backend;

use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\CommentsFilter;
use Qc\QcComments\Service\CommentsTabService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

class CommentsBEController extends QcCommentsBEController
{
    /**
     * @param CommentsFilter|null $filter
     * @param string $operation
     * @return ResponseInterface
     * @throws Exception
     */
    public function commentsAction(CommentsFilter $filter = null, string $operation = ''): ResponseInterface {
        $this->addMainMenu('comments');
        if($operation === 'reset-filters'){
            $filter = new CommentsFilter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => 'comments'
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
        return $this->moduleTemplate->renderResponse('Comments');
    }


    /**
     * This function is used to delete the comment
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function deleteCommentAction()
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $recordUid = $this->request->getArguments()['commentUid'];
        if($recordUid){
            $this->qcBeModuleService->deletedComment($recordUid);
        }
        return new ForwardResponse('comments');
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
