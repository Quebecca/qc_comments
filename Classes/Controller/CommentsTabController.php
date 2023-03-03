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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
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
        if (!$this->root_id) {
            $this->view->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->processFilter($filter);
            }
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

            $orderType = in_array($this->settings['comments']['orderType'], ['DESC', 'ASC'])
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
                $message = $this->localizationUtility->translate(self::QC_LANG_FILE . 'tooMuchResults', null, (array)[$numberOfSubPages, $maxRecords]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }
            $pagesId = $this->pages_ids;
            $currentPageId = $this->root_id;
            $commentHeaders = $this->getHeaders();
            $this
                ->view
                ->assignMultiple(compact(
                    'csvButton',
                    'resetButton',
                    'commentHeaders',
                    'stats',
                    'comments',
                    'pagesId',
                    'currentPageId'
                ));
        }


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
            $headers[$col] = $this->localizationUtility->translate(self::QC_LANG_FILE . 'comments.h.' . $col);
        }
        if ($include_csv_headers) {
            $headers = array_merge([
                'page_uid' => $this->localizationUtility->translate(self::QC_LANG_FILE . 'csv.h.page_uid'),
                'page_title' => $this->localizationUtility->translate(self::QC_LANG_FILE . 'stats.h.page_title'),
            ], $headers);
        }
        return $headers;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function exportCommentsAction(ServerRequestInterface $request): ResponseInterface
    {
       // $backendSession = GeneralUtility::makeInstance(BackendSession::class);
       // $filter = $backendSession->get('filter') ?? new Filter() ;
        $filter = new Filter();
        $filter->setLang($request->getQueryParams()['parameters']['lang']);
        $filter->setDepth(intval($request->getQueryParams()['parameters']['depth']));
        $filter->setDateRange($request->getQueryParams()['parameters']['selectDateRange']);
        $filter->setStartDate($request->getQueryParams()['parameters']['startDate']);
        $filter->setEndDate($request->getQueryParams()['parameters']['endDate']);
        $filter->setUseful($request->getQueryParams()['parameters']['useful']);

        $this->commentsRepository->setRootId($request->getQueryParams()['parameters']['currentPageId']);
        $this->commentsRepository->setFilter($filter);

        $pagesData = $this->commentsRepository->getPageIdsList();
        if(intval($request->getQueryParams()['parameters']['depth']) == 0)
            $pagesData = [$request->getQueryParams()['parameters']['currentPageId']];

        $data = $this->commentsRepository->getComments($pagesData, false, self::DEFAULT_ORDER_TYPES);
        $headers = $this->getHeaders(true);
        foreach ($data as $row) {
            array_walk($row, function (&$field) {
                $field = str_replace("\r", ' ', $field);
                $field = str_replace("\n", ' ', $field);
            });
        }
        return parent::export($filter,$request,'comments', $headers, $data);
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
