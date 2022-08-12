<?php

use Doctrine\DBAL\Driver\Exception;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\View\CsvView;

class ExportService
{

    /**
     * @param Filter $filter
     * @param $base_name
     * @return string
     */
    protected function getCSVFilename(Filter $filter, $base_name): string
    {
        $format = $this->settings['csvExport']['filename']["dateFormat"];
        $now = date($format);
        $from = $filter->getDateForRange($format);
        return implode('-', array_filter([
                $this->translate($base_name),
                $filter->getLang(),
                'uid' . $this->root_id,
                $from,
                $now,
            ])) . '.csv';

    }

    public function export(string $base_name, array $data){
        $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, $base_name));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $this->getStatsHeaders());
        $this->view->assign('rows', $data);

        $filter->setIncludeEmptyPages(true);
    }



}