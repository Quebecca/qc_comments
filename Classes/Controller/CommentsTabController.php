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
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class CommentsTabController extends QcBackendModuleController
{

    protected const DEFAULT_ORDER_TYPES = 'DESC';
    protected const DEFAULT_MAX_RECORDS = '100';
    protected const DEFAULT_MAX_PAGES = '100';

    /**
     * This function is used to get the list of comments in BE module
     * @param Filter|null $filter
     * @throws Exception
     */
    public function commentsAction(Filter $filter = null)
    {
        if($filter)
            $this->processFilter($filter);
        $csvButton = [
            'href' => $this->getUrl('exportComments'),
            'icon' => $this->icon,
        ];

        $resetButton = [
            'href' => $this->getUrl('resetFilter')
        ];

        $this->pages_ids = $this->commentsRepository->getPageIdsList();

        $maxRecords = is_numeric($this->settings['comments']['maxRecords'])
            ? $this->settings['comments']['maxRecords'] : self::DEFAULT_MAX_RECORDS;

        $numberOfSubPages = is_numeric($this->settings['comments']['numberOfSubPages'])
            ? $this->settings['comments']['numberOfSubPages'] : self::DEFAULT_MAX_PAGES;

        $orderType = in_array($this->settings['comments']['orderType'],  ['DESC', 'ASC'])
            ? $this->settings['comments']['orderType'] : self::DEFAULT_ORDER_TYPES;


        $tooMuchPages = count($this->pages_ids) > $numberOfSubPages;
        $this->pages_ids = array_slice(
            $this->pages_ids,
            0,
            $numberOfSubPages
        );
        $stats = $this->commentsRepository->getStatistics($this->pages_ids, $maxRecords);
        $comments = $this->commentsRepository->getComments($this->pages_ids, $maxRecords, $orderType);

        if ($this->commentsRepository->getListCount() > $maxRecords || $tooMuchPages) {
            $message = $this->translate('tooMuchResults', [$numberOfSubPages, $maxRecords]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
        }
        $pagesId = $this->pages_ids;
        $commentHeaders = $this->getHeaders();
        $this
            ->view
            ->assignMultiple(compact(
                'csvButton',
                'resetButton',
                'commentHeaders',
                'stats',
                'comments',
                'pagesId'
            ));
    }

    /**
     * This function is used to return the headers used in the exported file and the BE module table
     * @param false $include_csv_headers
     * @return array
     */
    protected function getHeaders(bool $include_csv_headers = false): array
    {
        $headers = [];

        foreach (['date_houre', 'comment', 'useful'] as $col) {
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
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportCommentsAction(ServerRequestInterface  $request)
    {
        $response = new Response('php://output', 200,
            ['Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . 'Comments' . '.csv"'
            ]
        );

        //$filter->setIncludeEmptyPages(true);
        $backendSession = GeneralUtility::makeInstance(BackendSession::class);
        $filter = $backendSession->get('filter');
        $this->commentsRepository->setFilter($filter ?? new Filter());
        $pagesData = $request->getQueryParams()['pagesId'];
        $data = $this->commentsRepository->getComments($pagesData, false, self::DEFAULT_ORDER_TYPES);

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
        parent::resetFilterAction('comments');
    }
}
