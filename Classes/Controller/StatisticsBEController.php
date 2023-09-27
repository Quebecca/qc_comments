<?php

namespace Qc\QcComments\Controller;

use Qc\QcComments\Controller\v12\QcCommentsBEv12Controller;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\StatisticsTabService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;

class StatisticsBEController extends QcCommentsBEv12Controller
{

    public function __construct()
    {
      //  parent::__construct();
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
    }

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(string $tabName = ''): ResponseInterface
    {
        parent::resetFilterAction('statistics');
        return $this->htmlResponse();
    }

    /**
     * This function is used to export statistics records on a csv file
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportStatisticsAction(ServerRequestInterface $request): ResponseInterface
    {
        $filter = $this->qcBeModuleService->getFilterFromRequest($request);
        $filter->setDepth( intval($request->getQueryParams()['parameters']['depth']));
        $currentPageId = intval($request->getQueryParams()['parameters']['currentPageId']);
        return $this->qcBeModuleService->exportStatisticsData($filter, $currentPageId);
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->moduleTemplate->makeDocHeaderModuleMenu(['id' => 1]);
       // $this->statisticsAction();
        return $this->moduleTemplate->renderResponse('Statistics');
        //return $this->htmlResponse($this->moduleTemplate->renderContent());

    }



































}