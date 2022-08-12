<?php

namespace Qc\QcComments\Controller;

use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\View\CsvView;
use TYPO3\CMS\Core\Messaging\AbstractMessage;

class StatisticsTabController extends QcBackendModuleController
{

    /**
     * @param Filter|null $filter // we need to specify the filter class in the argument to prevent map error
     * @return void
     * @throws Exception
     */
    public function statisticsAction(Filter $filter = null)
    {
         $filter = $this->processFilter($filter);
         $this->pages_ids = $this->commentsRepository->getPageIdsList($filter->getDepth());
         $tooMuchResults = false;
         if (count($this->pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
             $tooMuchResults = true;
             $pages_ids = array_slice($this->pages_ids, 0, $this->settings['maxStats']);
         }
         $resultData = $this->commentsRepository->getDataStats($this->pages_ids,true);
         $rows = [];
         foreach ($resultData as $item){
             $item['total_neg'] = $item['total'] - $item['total_pos'];
             $item['avg'] = number_format((($item['total_pos'] - $item['total_neg']) / $item['total']), 3)  ;
             $rows[] = $item;
         }
         if ($tooMuchResults || count($rows) > $this->settings['maxStats']) {
             $message = $this->translate('tooMuchPages', [$this->settings['maxStats']]);
             $this->addFlashMessage($message, null, AbstractMessage::WARNING);
             array_pop($rows); // last line was there to check that limit has been reached
         }
         $this->view
             ->assign('csvButton', [
                 'href' => $this->getUrl('exportStatistics'),
                 'icon' => $this->icon,
             ])
             ->assign('resetButton', [
                 'href' => $this->getUrl('resetFilter'),
             ])
             ->assign('headers', $this->getStatsHeaders())
             ->assign('rows', $rows);
    }

    /**
     * @param null $filter
     * @throws Exception
     */
    public function exportStatisticsAction($filter = null)
    {
        $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, 'statistics'));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $this->getStatsHeaders());
        $filter->setIncludeEmptyPages(true);
        $this->view->assign('rows', $this->commentsRepository->getDataStats($this->pages_ids, [], false));
    }

}