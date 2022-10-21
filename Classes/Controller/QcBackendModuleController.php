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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\Domain\Session\BackendSession;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

abstract class QcBackendModuleController extends BackendModuleActionController
{
    /**
     * @var int|mixed
     */
    protected $root_id;

    /**
     * @var Icon
     */
    protected ?Icon $icon = null;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var string
     */
    protected $tsControllerKey = '';

    /**
     * @var string
     */
    protected $extensionName = '';

    /**
     * @var string
     */
    protected string $controllerName = '';

    protected array $pages_ids = [];

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    public function injectBackendSession(BackendSession $backendSession)
    {
        $this->backendSession = $backendSession;
    }

    public function injectCommentRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    public function __construct(
    ) {
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
    }

    /**
     * Forward to the last selected action in case the current action is the default one
     * @throws StopActionException
     */
    protected function forwardToLastSelectedAction()
    {

        // if no menu, no forward is done
        if (!$this->menuItems) {
            return;
        }
        $currentAction = $this->controllerName . '::' . $this->request->getControllerActionName();
        $arguments = $this->request->getArguments();
        if (!$arguments) { // no arguments means no action was selected
            $lastAction = $this->backendSession->get('lastAction');
            if ($lastAction && $lastAction != $currentAction) {
                list($controller, $action) = explode('::', $lastAction);
                $this->forward($action, $controller);
            }
        }
        if ($this->isMenuAction($currentAction)) {
            $this->backendSession->store('lastAction', $currentAction);
        }
    }

    /**
     * This function is used to check if the action key is in the menu of actions
     * @param $actionKey
     * @return bool
     */
    protected function isMenuAction($actionKey): bool
    {
        list($controller, $action) = explode('::', $actionKey);
        foreach ($this->menuItems as $item) {
            if ($item['action'] == $action
               && $item['controller'] == $controller) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws StopActionException
     */
    public function initializeAction()
    {
        $this->extKey = $this->request->getControllerExtensionKey();
        $this->moduleName = $this->request->getPluginName();
        $this->extensionName = $this->request->getControllerExtensionName();
        $this->controllerName = $this->request->getControllerName();
        $this->tsControllerKey = lcfirst($this->controllerName);
        $this->setMenu();
        $this->forwardToLastSelectedAction();
        $this->root_id = GeneralUtility::_GP('id');
        $this->commentsRepository->setRootId((int)$this->root_id);
        $this->commentsRepository->setSettings($this->settings);
    }

    /**
     * This function is used to set the functions elements of the module
     */
    protected function setMenu()
    {
        // Define menu items
        $this->setMenuIdentifier('commentsMenu');
        $menuItems = [
            [
                'label' => $this->localizationUtility->translate(self::QC_LANG_FILE . 'menu.stats'),
                'action' => 'statistics',
                'controller' => 'StatisticsTab'
            ],
            [
                'label' => $this->localizationUtility->translate(self::QC_LANG_FILE . 'menu.list'),
                'action' => 'comments',
                'controller' => 'CommentsTab'
            ],

        ];
        $this->setMenuItems($menuItems);
    }

    /**
     * @param $action
     * @param array $arguments
     * @param null $controller
     * @return string
     */
    protected function getUrl($action, array $arguments = [], $controller = null): string
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->uriFor($action, $arguments, $controller);
    }

    /**
     * @param ViewInterface $view
     * @throws RouteNotFoundException
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);


        $moduleTemplate = $view->getModuleTemplate();
        if ($this->root_id && $moduleTemplate) {
            $record = BackendUtility::readPageAccess($this->root_id, $this->getBackendUser()->getPagePermsClause(1));
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($record);
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');
            $this->pageRenderer->addCssFile('EXT:qc_comments/Resources/Public/Css/be_qc_comments.css');
            $this->pageRenderer->addJsFile('EXT:qc_comments/Resources/Public/JavaScript/AdministrationModule.js');
        }
        $this->processFilter();

    }



    /**
     * @throws NoSuchArgumentException
     */
    public function initializeListAction()
    {
        if (!isset($this->settings['dateFormat'])) {
            $this->settings['dateFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'd-m-Y';
        }
        if (!isset($this->settings['timeFormat'])) {
            $this->settings['timeFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
        }
        $constraintConfiguration = $this->arguments->getArgument('filter')->getPropertyMappingConfiguration();
        $constraintConfiguration->allowAllProperties();

    }





    /**
     * Returns join clauses for pagetree depth levels
     * @param $depth
     * @return string
     */
    protected function getPageTreeView($depth): string
    {
        $child = $parent = 'lvl_0';
        $clauses = [];
        for ($i = 1; $i <= $depth; $i++) {
            $child = 'lvl_' . $i;
            $clauses[] = "left join pages $child on $parent.uid in ($child.uid, $child.pid) and !$child.deleted";
            $parent = $child;
        }
        $joins = implode("\n", $clauses);
        return "select distinct $child.* from pages lvl_0 $joins where lvl_0.uid = $this->root_id";
    }

    /**
     * This function is used to get the filter from the backend session
     * @param Filter|null $filter
     * @return Filter|null
     */
    protected function processFilter(Filter $filter = null): ?Filter
    {
        // Add filtering to records
        if ($filter === null) {
            // Get filter from session if available
            $filter = $this->backendSession->get('filter');
            if ($filter == null) {
                $filter = new Filter();
            }
        } else {
            if ($filter->getDateRange() != 'userDefined') {
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
    public function resetFilterAction(string $tabName = '')
    {
        $this->processFilter(new Filter());
        $this->forward($tabName);
    }

    /**
     * @param Filter $filter
     * @param $fileName
     * @param $csvDateFormat
     * @param $pageId
     * @return string
     */
    protected function getCSVFilename(Filter $filter, $fileName, $csvDateFormat, $pageId): string
    {
        $format = $csvDateFormat;
        if($filter->getDateRange() == 'userDefined'){
            $from = date($format,strtotime($filter->getStartDate()));
            $now = date($format,strtotime($filter->getEndDate()));
        }
        else
            $now = date($format, strtotime('-'.$filter->getDateRange(), strtotime(date($format))));

        return implode('-', array_filter([
                $this->localizationUtility->translate(self::QC_LANG_FILE . $fileName),
                $filter->getLang(),
                'uid' . $pageId,
                $from,
                $now,
            ])) . '.csv';
    }

    /**
     * @param Filter $filter
     * @param ServerRequestInterface $request
     * @param string $fileName
     * @param array $headers
     * @param array $data
     * @return ResponseInterface
     */
    public function export(Filter $filter, ServerRequestInterface  $request,string $fileName,array $headers, array $data): ResponseInterface
    {
        $pageId = $request->getQueryParams()['parameters']['currentPageId'];
        $csvSettings = $request->getQueryParams()['parameters']['csvSettings'];
        $separator = $csvSettings['separator'] ?? ',';
        $enclosure = $csvSettings['enclosure'] ?? '"';
        $escape = $csvSettings['escape'] ?? '\\';
        $csvDateFormat = $csvSettings['dateFormat'] ?? 'YmdHi';
        $fileName = $this->getCSVFilename($filter, $fileName, $csvDateFormat, $pageId);

        $response = new Response(
            'php://output',
            200,
            ['Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]
        );

        $fp = fopen('php://output', 'wb');
        // BOM utf-8 pour excel
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers, $separator, $enclosure, $escape);
        foreach ($data as $row) {
            foreach ($row as $item) {
                fputcsv($fp, $item, $separator, $enclosure, $escape);
            }
        }
        //  rewind($fp);
        $str_data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);
        return $response;
    }

    abstract protected function getHeaders(): array;
}
