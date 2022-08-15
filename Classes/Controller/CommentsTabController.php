<?php

namespace Qc\QcComments\Controller;

use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\View\CsvView;
use TYPO3\CMS\Core\Messaging\AbstractMessage;

class CommentsTabController extends QcBackendModuleController
{
    /**
     *  We need to specify the filter class in the argument to prevent map error
     * @param Filter|null $filter
     * @return void
     * @throws Exception
     */
    public function commentsAction(Filter $filter = null)
    {
        $filter = $this->processFilter($filter);

        $csvButton = [
            'href' => $this->getUrl('exportComments'),
            'icon' => $this->icon,
        ];

        $resetButton = [
            'href' => $this->getUrl('resetFilter')
        ];
        $tooMuchPages = false;
        $tooMuchComments = $this->commentsRepository->getListCount() > $this->settings['maxComments'];
        $this->pages_ids = $this->commentsRepository->getPageIdsList($filter->getDepth());
        if (count($this->pages_ids) > $this->settings['maxStats'] && $filter->getIncludeEmptyPages()) {
            $tooMuchPages = true;
            $pages_ids = array_slice($this->pages_ids, 0, $this->settings['maxStats']);
        }

        $stats = $this->commentsRepository->getDataStats($this->pages_ids, true);
        $tooMuchPages = $tooMuchPages ?: count($stats) > $this->settings['maxStats'];
        $pages_ids = array_map(function ($row) {
            return $row['page_uid'];
        }, $stats);


        if ($tooMuchComments | $tooMuchPages) {
            $message = $this->translate('tooMuchResults', [$this->settings['maxStats'], $this->settings['maxComments']]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
        }
        $comments = $this->commentsRepository->getDataList( $pages_ids,true);
        $commentHeaders = $this->getHeaders();

        // Avg
      //  $statisticsAvg = $this->statisticsTabController->getStatisticsAvg() * 100 . '%';
        $this
            ->view
            ->assignMultiple(compact(
                'csvButton',
                'resetButton',
                'commentHeaders',
                'stats',
                'comments',
            ));

    }

    /**
     * @param false $include_csv_headers
     * @return array
     */
    protected function getHeaders($include_csv_headers = false): array {
        $headers = [];

        // foreach (['date_houre', 'comment', 'appreciation',] as $col) {
        foreach (['date_houre', 'comment', 'useful',] as $col) {
            $headers[$col] = $this->translate('comments.h.' . $col);
        }
        if ($include_csv_headers) {
            $headers = array_merge([
                'page_uid' => $this->translate('csv.h.page_uid'),
                'page_title' => $this->translate('stats.h.page_title'),
            ], $headers);
        }
        return $headers;
    }
    /**
     * @param null $filter
     * @throws Exception
     */
    public function exportCommentsAction($filter = null)
    {
     /*   $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, 'comments'));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $this->getStatsHeaders());

        $this->view->assign('rows', $rows);*/
        $filter = $this->processFilter($filter);
        $filter->setIncludeEmptyPages(true);
        $data = $this->commentsRepository->getDataList($this->pages_ids);
        $rows = [];
        foreach ($data as $row){
            foreach ($row as $item){
                $rows[] = $item;

            }
        }
        parent::export('comments',$this->getHeaders(true),$rows,$filter);
    }

}