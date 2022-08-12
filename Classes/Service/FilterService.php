<?php

use Qc\QcComments\Domain\Dto\Filter;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class FilterService
{
    /**
     * @param Filter|null $filter
     * @return mixed|Filter
     */
    protected function processFilter(Filter $filter = null)
    {
        // Add filtering to records

        if ($filter === null) {
            // Get filter from session if available
            $filter = $this->backendSession->get('filter');
            if (!$filter instanceof Filter) {
                // No filter available, create new one
                $filter = new Filter();
            }
        } else {
            if($filter->getDateRange() != 'userDefined'){
                $filter->setStartDate(null);
                $filter->setEndDate(null);
            }
            $this->backendSession->store('filter', $filter);
        }
        $this->view->assign('filter', $filter);
        $this->commentsRepository->setFilter($filter);
        return $filter;

    }

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(){
        $filter = $this->processFilter(new Filter());
        $this->redirect('list', NULL, NULL, ['filter' => $filter]);
    }


}