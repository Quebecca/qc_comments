<?php

namespace Qc\QcComments\Controller\Backend;

use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\TechnicalProblemsTabService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Core\Context\Context;

class TechnicalProblemsBEController extends QcCommentsBEController
{

    /**
     * @param Filter|null $filter
     * @param string $operation
     * @return ResponseInterface
     * @throws Exception
     */
    public function technicalProblemsAction(Filter $filter = null, string $operation = ''): ResponseInterface{

        $this->qcBeModuleService
            = GeneralUtility::makeInstance(TechnicalProblemsTabService::class);
        if($this->request->getArguments()['recordUidToRemove'] != null){
            $this->qcBeModuleService->technicalProblemFixed($this->request->getArguments()['recordUidToRemove']);
        }

        if($operation === 'reset-filters' || $filter == null){
            $filter = new Filter();
        }
        $filter->setUseful('NA');

        $this->qcBeModuleService->getBackendSession()->store(
            'lastAction',
            [
                'controllerName' => $this->controllerName,
                'actionName' => 'technicalProblems'
            ]);

        $this->addMainMenu('technicalProblems');

        $this->qcBeModuleService->setRootId($this->root_id);
        $this->qcBeModuleService->processFilter();

        if (!$this->root_id) {
            $this->moduleTemplate->assign('noPageSelected', true);
        }
        else {
            if ($filter) {
                $this->qcBeModuleService->processFilter($filter);
                $this->moduleTemplate->assign('filter', $filter);
            }
            $data = $this->qcBeModuleService->getComments();

           if($data['tooMuchResults'] === true){
                $message = $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'tooMuchResults',
                        null, (array)[$data['numberOfSubPages'], $data['maxRecords']]);
                $this->addFlashMessage($message, null, AbstractMessage::WARNING);
            }


            $this->moduleTemplate
             ->assignMultiple(
                 [
                     'commentHeaders' => $data['commentHeaders'],
                     'stats' => $data['stats'],
                     'comments' => $data['comments'],
                     'pagesId' => $data['pagesId'],
                     'currentPageId' => $data['currentPageId']
                 ]
             );
        }
        $filter = $this->qcBeModuleService->processFilter();
        $this->moduleTemplate->assign('filter', $filter);
        return $this->moduleTemplate->renderResponse('TechnicalProblems');
    }


}
