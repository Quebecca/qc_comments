<?php

namespace Qc\QcComments\Controller;

/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 <techno@quebec.ca>
 *
 ***/

use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Service\QcBackendModuleService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
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
     * @var string
     */
    protected string $controllerName = '';

    /**
     * @var array
     */
    protected array $pages_ids = [];

    protected QcBackendModuleService  $qcBeModuleService;


    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    const QC_LANG_FILE = 'LLL:EXT:qc_comments/Resources/Private/Language/locallang.xlf:';

    public function __construct(
    ) {
        $this->localizationUtility
            = GeneralUtility::makeInstance(LocalizationUtility::class);
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
        if (!$arguments) { // no arguments mean no action was selected
            $lastAction = $this->qcBeModuleService->getBackendSession()->get('lastAction');
            if ($lastAction && $lastAction != $currentAction) {
                list($controller, $action) = explode('::', $lastAction);
                $this->redirect($action, $controller);
            }
        }
        if ($this->isMenuAction($currentAction)) {
            $this->qcBeModuleService->getBackendSession()->store('lastAction', $currentAction);
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
        $this->controllerName = $this->request->getControllerName();
        $this->setMenu();
        $this->forwardToLastSelectedAction();
        $this->root_id = GeneralUtility::_GP('id') ?? 0;
        $this->qcBeModuleService->setRootId($this->root_id);
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
                'label' => $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'menu.stats'),
                'action' => 'statistics',
                'controller' => 'StatisticsBE'
            ],
            [
                'label' => $this->localizationUtility
                    ->translate(self::QC_LANG_FILE . 'menu.list'),
                'action' => 'comments',
                'controller' => 'CommentsBE'
            ]

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
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class)
            ->setRequest($this->request);
        return $uriBuilder->uriFor($action, $arguments, $controller);
    }


    protected function initializeView( $view)
    {
        parent::initializeView($view);
        //$moduleTemplate = $view->getModuleTemplate();
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        if ($this->root_id && $moduleTemplate) {
            $record = BackendUtility::readPageAccess(
                $this->root_id,
                $this->getBackendUser()
                    ->getPagePermsClause(1)
            );
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($record);
            $this->pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/Backend/DateTimePicker'
            );
            $this->pageRenderer->addCssFile(
                'EXT:qc_comments/Resources/Public/Css/be_qc_comments.css'
            );

            $this->pageRenderer->loadRequireJsModule(
                'TYPO3/CMS/QcComments/AdministrationModule'
            );
        }
        $filter = $this->qcBeModuleService->processFilter();
        $this->view->assign('filter', $filter);

    }

    /**
     * @throws NoSuchArgumentException
     */
    public function initializeListAction()
    {
        if (!isset($this->settings['dateFormat'])) {
            $this->settings['dateFormat']
                = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']
                    ?: 'd-m-Y';
        }
        if (!isset($this->settings['timeFormat'])) {
            $this->settings['timeFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
        }
        $constraintConfiguration = $this->arguments
                                    ->getArgument('filter')
                                    ->getPropertyMappingConfiguration();
        $constraintConfiguration->allowAllProperties();

    }

    /**
     * This function will reset the search filter
     * @throws StopActionException
     */
    public function resetFilterAction(string $tabName = '')
    {
        $this->qcBeModuleService->processFilter(new Filter());
        $this->redirect($tabName);
    }

}
