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
use Psr\Http\Message\ServerRequestInterface;
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
            $message = $this->localizationUtility->translate(self::QC_LANG_FILE.'tooMuchPages',null, [$maxRecords]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            array_pop($resultData); // last line was there to check that limit has been reached
        }
        $this->view->assignMultiple([
           'csvButton' => [
               'href' => $this->getUrl('exportStatistics'),
               'icon' => $this->icon,
           ],
            'resetButton' => [
                'href' => $this->getUrl('resetFilter'),
            ],
            'headers' => $this->getHeaders(),
            'rows' => $resultData,
            'pagesId' => $this->pages_ids,
            'settings'
        ]);

    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [];
        foreach (['page_uid', 'page_title', 'total_pos', 'total_neg', 'total', 'avg'] as $col) {
            $headers[$col] = $this->localizationUtility->translate(self::QC_LANG_FILE.'stats.h.' . $col);
        }
        return $headers;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportStatisticsAction (ServerRequestInterface  $request): Response
    {
        $backendSession = GeneralUtility::makeInstance(BackendSession::class);
        $filter = $backendSession->get('filter');
        $this->commentsRepository->setFilter($filter ?? new Filter());
        $pagesData = $request->getQueryParams()['pagesId'];
        $csvDateFormat = $request->getQueryParams()['csvFormat'] ?? 'YmdHi';

        $fileName = $this->getCSVFilename($filter,'stats', $csvDateFormat,$pagesData[0]);
        $data = $this->commentsRepository->getStatistics($pagesData, false);
        // Resort array elements for export
        $mappedData = [];
        $i = 0;
        $headers = array_keys($this->getHeaders());
        foreach ($data as $record) {
            foreach ($this->getHeaders() as $headerKey => $header) {
                $mappedData[$record['pages_uid']][$i][$headerKey] = $record[$headerKey];
            }
            $i++;
        }
        return parent::export($fileName,$headers, $mappedData);
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
