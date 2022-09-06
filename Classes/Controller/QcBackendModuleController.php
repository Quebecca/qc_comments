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

use LST\BackendModule\Controller\BackendModuleActionController;
use LST\BackendModule\Domain\Session\BackendSession;
use Qc\QcComments\Domain\Dto\Filter;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\Traits\injectT3Utilities;
use Qc\QcComments\Traits\InjectTranslation;
use Qc\QcComments\View\CsvView;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

abstract class QcBackendModuleController extends BackendModuleActionController
{
    use InjectTranslation, injectT3Utilities;

    /**
     * @var int|mixed
     */
    protected $root_id;

    /**
     * @var Icon
     */
    protected ?Icon $icon = null;

    /** @var BackendSession */
    protected $backendSession = null;

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
    protected string $controllerName;

    protected array $pages_ids = [];

    public function injectBackendSession(BackendSession $backendSession)
    {
        $this->backendSession = $backendSession;
    }

    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    public function injectCommentRepository(CommentRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
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
        $currentAction = $this->controllerName.'::'.$this->request->getControllerActionName();
        $arguments = $this->request->getArguments();
        if (!$arguments) { // no arguments means no action was selected
            $lastAction = $this->backendSession->get('lastAction');
            if ($lastAction && $lastAction != $currentAction ) {
                list($controller,$action) = explode('::',$lastAction);
                $this->forward($action,$controller);
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
        list($controller,$action) = explode('::',$actionKey);
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
        $this->backendSession->setStorageKey($this->extKey);
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
                'label' => $this->translate('menu.stats'),
                'action' => 'statistics',
                'controller' => 'StatisticsTab'
            ],
            [
                'label' => $this->translate('menu.list'),
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
    protected function getUrl($action, $arguments = [], $controller = null)
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->uriFor($action, $arguments, $controller);
    }

    /**
     * @param ViewInterface $view
     * @return void
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
          //  $this->pageRenderer->addCssFile('EXT:qc_comments/Resources/Public/Css/qc_comments.css');
        }
    }


    /**
     * @throws StopActionException
     */
    public function initializeStatsAction()
    {
        $this->sharedPreChecks();
    }

    /**
     * @throws NoSuchArgumentException|StopActionException
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
        $this->sharedPreChecks();
    }

    /**
     * @throws StopActionException
     */
    protected function sharedPreChecks()
    {
        $this->forwardIfNoPageSelected();
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->icon = $this->iconFactory->getIcon('actions-document-export-csv', Icon::SIZE_SMALL);
    }


    public function noPageSelectedAction()
    {
        $this->setMenuItems([]);
    }

    /**
     * This function will be called if there is no page selected
     * @throws StopActionException
     */
    protected function forwardIfNoPageSelected()
    {
        if (!$this->root_id) {
            $this->forward('noPageSelected');
        }
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
    public function resetFilterAction(string $tabName = ''){
        $filter = $this->processFilter(new Filter());
        $this->redirect($tabName, NULL, NULL, ['filter' => $filter]);
    }

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

    /**
     * This function is used to export csv file
     * @param string $base_name
     * @param array $headers
     * @param array $data
     * @param $filter
     */
    public function export(string $base_name,array $headers, array $data, $filter){
        $filter = $this->processFilter($filter);
        $this->view = $this->objectManager->get(CsvView::class);
        $this->view->setFilename($this->getCSVFilename($filter, $base_name));
        $this->view->setControllerContext($this->controllerContext);
        $this->view->assign('headers', $headers);
        $this->view->assign('rows', $data);
        $filter->setIncludeEmptyPages(true);
    }

    protected abstract function getHeaders() : array;

}