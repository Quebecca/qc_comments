<?php

namespace Qc\QcComments\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Controller\v12\QcCommentsBEv12Controller;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\CommentsTabService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class CommentsBEController extends QcCommentsBEv12Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->qcBeModuleService = GeneralUtility::makeInstance(CommentsTabService::class);
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