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

use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class StatisticsTabController extends QcBackendModuleController
{
    /**
     * @param Filter|null $filter
     */
    public function statisticsAction(Filter $filter = null)
    {
        if($filter)
            $this->processFilter($filter);
        $this->pages_ids = $this->commentsRepository->getPageIdsList();
        $maxRecords = $this->settings['statistics']['maxRecords'];
        $resultData = $this->commentsRepository->getStatistics($this->pages_ids, $maxRecords);
        if (count($resultData) > $maxRecords) {
            $message = $this->translate('tooMuchPages', [$maxRecords]);
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
     * @return Response
     */
    public function exportStatisticsAction ($filter = null): Response
    {
        $response = new Response('php://output', 200,
            ['Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . 'Statistics' . '.csv"'
            ]
        );

        //$filter->setIncludeEmptyPages(true);
        $backendSession = GeneralUtility::makeInstance(BackendSession::class);
        $filter = $backendSession->get('filter');
        $this->commentsRepository->setFilter($filter);
        $data = $this->commentsRepository->getStatistics($this->pages_ids, false);

        // Resort array elements for export
        $mappedData = [];
        $i = 0;
        foreach ($data as $record) {
            foreach ($this->getHeaders() as $headerKey => $header) {
                $mappedData[$i][$header] = $record[$headerKey];
            }
            $i++;
        }
        //return parent::export('statistics', $this->getHeaders(), $mappedData, $filter);

        $fp = fopen('php://output', 'wb');
        // BOM utf-8 pour excel
        fwrite($fp, "\xEF\xBB\xBF");
        $headers = array_keys($this->getHeaders(true));
        fputcsv($fp, $headers, ",", '"', '\\');
        foreach ($data as $row) {
            array_walk($row, function (&$field) {
                $field = str_replace("\r", ' ', $field);
                $field = str_replace("\n", ' ', $field);
            });
            foreach ($row as $item){
                fputcsv($fp, $item, ",", '"', '\\');
            }
        }
        //  rewind($fp);
        $str_data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);

        return $response;
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
