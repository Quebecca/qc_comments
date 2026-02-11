<?php

namespace Qc\QcComments\Controller\Backend;

use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\CommentsFilter;
use Qc\QcComments\Service\CommentsTabService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

class CommentsBEController extends QcCommentsBEController
{
    /**
     * @param CommentsFilter|null $filter
     * @param string              $operation
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function commentsAction(CommentsFilter $filter = null, string $operation = ''): ResponseInterface
    {

        $this->addMainMenu('comments');
        if ($operation === 'reset-filters') {
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
        } else {
            if ($filter) {
                $filter = $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);
            }
            $data = $this->qcBeModuleService->getComments();
            if ($data['tooMuchResults'] === true) {
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, [$data['maxRecords']]);
                $this->addFlashMessage($message, null, ContextualFeedbackSeverity::WARNING);
            }

            if ($data['tooMuchPages'] === true) {
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchPages',
                        null, [$data['numberOfSubPages']]);
                $this->addFlashMessage($message, null, ContextualFeedbackSeverity::WARNING);
            }

            $this
                ->moduleTemplate
                ->assignMultiple(
                    [
                        'commentHeaders' => $data['commentHeaders'],
                        'stats' => $data['stats'] ?? [],
                        'comments' => $data['comments'],
                        'pagesId' => $data['pagesId'],
                        'currentPageId' => $data['currentPageId'],
                        'isRemoveButtonEnabled' => $this->qcBeModuleService->isRemoveButtonEnabled(),
                        'isDeleteButtonEnabled' => $this->qcBeModuleService->isDeleteButtonEnabled('comments')
                    ]
                );
        }

        $filter = $this->qcBeModuleService->processFilter($filter);
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('Comments');
    }


    /**
     * This function is used to delete the comment (deleted = 1)
     *
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function deleteCommentAction(): ForwardResponse
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $recordUid = $this->request->getArguments()['commentUid'];
        if ($recordUid) {
            $this->qcBeModuleService->deleteComment($recordUid);
        }
        return new ForwardResponse('comments');
    }

    /**
     * This function is used to remove the comment (remove = 1)
     *
     * @return ForwardResponse
     * @throws AspectNotFoundException
     */
    public function hideCommentAction()
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $recordUid = $this->request->getArguments()['commentUid'];
        if ($recordUid) {
            $this->qcBeModuleService->hideComment($recordUid);
        }
        return new ForwardResponse('comments');
    }

    /**
     * This function is used to export comments records on a csv file
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function exportCommentsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(CommentsTabService::class);
        $root_id = intval($request->getQueryParams()['parameters']['currentPageId']);
        $this->qcBeModuleService->setRootId($root_id);
        $filter = $this->qcBeModuleService->processFilter();
        return $this->qcBeModuleService->exportCommentsData($filter);
    }

}
