<?php

namespace Qc\QcComments\Controller;

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class StatisticsTabController extends QcBackendModuleController
{
    /**
     * @param Filter|null $filter // we need to specify the filter class in the argument to prevent map error
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
        $resultData = $this->commentsRepository->getDataStats($this->pages_ids, true);
        if ($tooMuchResults || count($resultData) > $this->settings['maxStats']) {
            $message = $this->translate('tooMuchPages', [$this->settings['maxStats']]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            array_pop($resultData); // last line was there to check that limit has been reached
        }
        $this->view
             ->assign('csvButton', [
                 'href' => $this->getUrl('exportStatistics'),
                 'icon' => $this->icon,
             ])
             ->assign('resetButton', [
                 'href' => $this->getUrl('resetFilter'),
             ])
             ->assign('headers', $this->getHeaders())
             ->assign('rows', $resultData);
    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [];
        foreach (['page_uid', 'page_title', 'total_pos', 'total_neg', 'total', 'avg'] as $col) {
            $headers[$col] = $this->translate('stats.h.' . $col);
        }
        return $headers;
    }

    /**
     * @param null $filter
     * @throws Exception
     */
    public function exportStatisticsAction($filter = null)
    {
        $filter = $this->processFilter($filter);
        $this->view->assign('rows', $this->commentsRepository->getDataStats($this->pages_ids));
        $data = $this->commentsRepository->getDataStats($this->pages_ids);
        // Resort array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($data as $record) {
            foreach ($this->getHeaders() as $headerKey => $header) {
                $mappedData[$i][$header] = $record[$headerKey];
            }
            $i++;
        }
        parent::export('statistics', $this->getHeaders(), $mappedData, $filter);
    }

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(string $tabName = '')
    {
        parent::resetFilterAction('statistics');
    }
}
