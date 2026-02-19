<?php
namespace Qc\QcComments\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\StatisticsFilter;
use Qc\QcComments\Service\StatisticsTabService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class StatisticsBEController extends QcCommentsBEController
{
    /**
     * @param StatisticsFilter|null $filter
     * @param string $operation
     * @return ResponseInterface
     */
    public function statisticsAction(?StatisticsFilter $filter = null,  string $operation = ''): ResponseInterface
    {
        if($operation === 'reset-filters'){
            $filter = new StatisticsFilter();
        }
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => "statistics"
            ]);

        $this->qcBeModuleService->getBackendSession()->store('lastAction', 'statistics');

        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();
        $this->addMainMenu('statistics');
        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);

            }
            $data = $this->qcBeModuleService->getPageStatistics();
            if($data['tooMuchResults'] == true){
                $message = $this->localizationUtility
                    ->translate(
                        self::QC_LANG_FILE . 'tooMuchPages',
                        null,
                        [$data['maxRecords']]
                    );
                $this->addFlashMessage($message, null, ContextualFeedbackSeverity::WARNING);
            }
            $statsByDepth = $this->qcBeModuleService->getStatisticsByDepth();

            $this->moduleTemplate->assignMultiple([
                'headers' => $data['headers'],
                'rows' => $data['rows'],
                'currentPageId' => $data['currentPageId'],
                'totalSection_headers' => $statsByDepth['headers'],
                'totalSection_row' => $statsByDepth['row']
            ]);
        }
        $filter = $this->qcBeModuleService->processFilter();

        $this->moduleTemplate->assign('filter', $filter);

        return $this->moduleTemplate->renderResponse('Statistics');
    }


    /**
     * This function is used to export statistics records on a csv file
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function exportStatisticsAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->qcBeModuleService
            = GeneralUtility::makeInstance(StatisticsTabService::class);
        $root_id = intval($request->getQueryParams()['parameters']['currentPageId']);
        $this->qcBeModuleService->setRootId($root_id);
        $filter = $this->qcBeModuleService->processFilter();
        return $this->qcBeModuleService->exportStatisticsData($filter);
    }

}
