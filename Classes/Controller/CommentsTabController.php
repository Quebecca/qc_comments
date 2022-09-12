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

class CommentsTabController extends QcBackendModuleController
{
    /**
     * This function is used to get the list of comments in BE module
     *  We need to specify the filter class in the argument to prevent a map error
     * @param Filter|null $filter
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

        $this->pages_ids = $this->commentsRepository->getPageIdsList($filter->getDepth());
        $maxRecords = $this->settings['comments']['maxRecords'];
        $numberOfSubPages = $this->settings['comments']['numberOfSubPages'];
        $tooMuchPages = count($this->pages_ids) > $numberOfSubPages;
        $this->pages_ids = array_slice(
            $this->pages_ids,
            0,
            $numberOfSubPages
        );
        $stats = $this->commentsRepository->getDataStats($this->pages_ids, $maxRecords);
        $comments = $this->commentsRepository->getDataList($this->pages_ids, $maxRecords);

        if ($this->commentsRepository->getListCount() > $maxRecords || $tooMuchPages) {
            $message = $this->translate('tooMuchResults', [$numberOfSubPages, $maxRecords]);
            $this->addFlashMessage($message, null, AbstractMessage::WARNING);
        }

        $commentHeaders = $this->getHeaders();
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
     * This function is used to return the headers used in the exported file and the BE module table
     * @param false $include_csv_headers
     * @return array
     */
    protected function getHeaders($include_csv_headers = false): array
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
     * Export function to export comments list to csv file
     * @param null $filter
     * @throws Exception
     */
    public function exportCommentsAction($filter = null)
    {
        $filter = $this->processFilter($filter);
        $filter->setIncludeEmptyPages(true);
        $data = $this->commentsRepository->getDataList($this->pages_ids, false);
        $rows = [];
        foreach ($data as $row) {
            foreach ($row as $item) {
                $rows[] = $item;
            }
        }
        parent::export('comments', $this->getHeaders(true), $rows, $filter);
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
