<?php

namespace Qc\QcComments\Controller;

use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\StatisticsTabService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;

class StatisticsBEController extends QcBackendModuleController
{
    public function __construct()
    {
        parent::__construct();
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
    }

    /**
     * @param Filter|null $filter
     * @return ResponseInterface
     */
    public function statisticsAction(Filter $filter = null): ResponseInterface
    {
        if (!$this->root_id) {
            $this->view->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->view->assign('filter', $filter);

            }
            $data = $this->qcBeModuleService->getPageStatistics();
            if($data['tooMuchResults'] == true){
                $message = $this->localizationUtility
                    ->translate(
                        self::QC_LANG_FILE . 'tooMuchPages',
                    null,
                        [$data['maxRecords']]
                    );
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }
           $statsByDepth = $this->qcBeModuleService->getStatisticsByDepth();
            $this->view->assignMultiple([
                'csvButton' => [
                    'href' => $this->getUrl('exportStatistics'),
                    'icon' => $this->icon,
                ],
                'resetButton' => [
                    'href' => $this->getUrl('resetFilter'),
                ],
                'headers' => $data['headers'],
                'rows' => $data['rows'],
                'pagesId' => $this->pages_ids,
                'settings',
                'currentPageId' => $data['currentPageId'],
                'totalSection_headers' => $statsByDepth['headers'],
                'totalSection_row' => $statsByDepth['row']
            ]);
        }
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
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
}