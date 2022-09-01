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
           // $pages_ids = array_slice($this->pages_ids, 0, $this->settings['maxStats']);
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
    protected function getHeaders($include_csv_headers = false): array {
        $headers = [];

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
     * Export function to export comments list to csv file
     * @param null $filter
     * @throws Exception
     */
    public function exportCommentsAction($filter = null)
    {
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

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(string $tabName = ''){
        parent::resetFilterAction('comments');
    }

}