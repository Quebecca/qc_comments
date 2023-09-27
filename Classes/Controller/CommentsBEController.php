<?php

namespace Qc\QcComments\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\CommentsTabService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class CommentsBEController extends QcBackendModuleController
{

    public function __construct()
    {
        parent::__construct();
        $this->qcBeModuleService = GeneralUtility::makeInstance(CommentsTabService::class);
    }

    /**
     * This function is used to get the list of comments in BE module
     * @param Filter|null $filter
     * @throws Exception
     * @throws DBALException
     */
    public function commentsAction(Filter $filter = null): ResponseInterface
    {
        if (!$this->root_id) {
            $this->view->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->view->assign('filter', $filter);
            }
            $csvButton = [
                'href' => $this->getUrl('exportComments'),
                'icon' => $this->icon,
            ];

            $resetButton = [
                'href' => $this->getUrl('resetFilter')
            ];

            $data = $this->qcBeModuleService->getComments();
            if($data['tooMuchResults'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, (array)[$data['numberOfSubPages'], $data['maxRecords']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }

            $this
                ->view
                ->assignMultiple(
                   [
                       'csvButton' => $csvButton,
                       'resetButton' => $resetButton,
                       'commentHeaders' => $data['commentHeaders'],
                       'stats' => $data['stats'],
                       'comments' => $data['comments'],
                       'pagesId' => $data['pagesId'],
                       'currentPageId' => $data['currentPageId']
                   ]
               );
        }
        return $this->htmlResponse();
    }

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(string $tabName = ''): ResponseInterface
    {
        parent::resetFilterAction('comments');
        return $this->htmlResponse();
    }


    /**
     * This function is used to export comments records on a csv file
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportCommentsAction(ServerRequestInterface $request): ResponseInterface
    {
        $filter = $this->qcBeModuleService->getFilterFromRequest($request);
        $filter->setDepth( intval($request->getQueryParams()['parameters']['depth']));
        $filter->setUseful($request->getQueryParams()['parameters']['useful']);
        $currentPageId = intval($request->getQueryParams()['parameters']['currentPageId']);
        return $this->qcBeModuleService->exportCommentsData($filter, $currentPageId);
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->moduleTemplate->makeDocHeaderModuleMenu(['id' => 1]);
        return $this->moduleTemplate->renderResponse('Comments');
        //return $this->htmlResponse($this->moduleTemplate->renderContent());
    }
}