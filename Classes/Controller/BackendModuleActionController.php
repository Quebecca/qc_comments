<?php

declare(strict_types=1);

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
namespace Qc\QcComments\Controller;

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder as BeUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Extend this controller to get convenience methods for backend modules
 */
class BackendModuleActionController extends ActionController
{
    /**
     * @var int
     */
    protected $pageUid = 0;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * The menu identifier for the backend module
     *
     * @var string
     */
    protected $menuIdentifier = 'menuidentifier';

    /**
     * The menu items for the backend module
     *
     * For each menu item, provide an array with these keys:
     * ['action' => 'actionName', 'controller' => 'ControllerName', 'label' => 'MenuItem Label']
     *
     * @var array
     */
    protected $menuItems = [];

    /**
     * The buttons for the backend module
     *
     * For each button, provide an array with these keys:
     * ['table' => 'table_name', 'label' => 'Button Label', 'action' => 'actionName', 'controller' => 'ControllerName']
     *
     * @var array
     */
    protected $buttons = [];

    /**
     * The extension key of the controller extending this class
     *
     * @var string
     */
    protected $extKey;

    /**
     * The module name of the backend module extending this class
     * @var string
     */
    protected $moduleName;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * Function will be called before every other action
     */
    public function initializeAction()
    {
        // Set storage pid from settings if defined
        if ((int)($this->settings['storagePid']) !== 0) {
            $this->pageUid = (int)($this->settings['storagePid']);
        }
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        // Show flash message if no storage pid defined
        if ($this->pageUid == 0) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->sL('LLL:EXT:backend_module/Resources/Private/Language/locallang.xlf:configuration.pid.description'),
                $this->getLanguageService()->sL('LLL:EXT:backend_module/Resources/Private/Language/locallang.xlf:configuration.pid.title'),
                FlashMessage::WARNING,
                true
            );

            $flashMessageService = $this->objectManager->get(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        }

        parent::initializeAction();
    }

    /**
     * @param ViewInterface $view
     * @throws RouteNotFoundException
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);

        if ($view instanceof BackendTemplateView) {
            $view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation([]);

            $this->pageRenderer = $this->view->getModuleTemplate()->getPageRenderer();
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ContextMenu');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Tooltip');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Recordlist/Tooltip');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/AjaxDataHandler');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Recordlist/Recordlist');

            $this->createMenu();
            $this->createButtons();
        }

        $this->view->assign('storagePid', $this->pageUid);

        $this->view->assign('returnUrl', $this->getControllerContextBasedReturnUrl());
    }

    /**
     * Create menu for backend module
     */
    protected function createMenu()
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier($this->menuIdentifier);

        foreach ($this->menuItems as $menuItem) {
            $item = $menu->makeMenuItem()
                ->setTitle($menuItem['label'])
                ->setHref((string)$uriBuilder->reset()->uriFor($menuItem['action'], [], $menuItem['controller']))
                ->setActive($this->request->getControllerActionName() === $menuItem['action'] && $this->request->getControllerName() === $menuItem['controller']);
            $menu->addMenuItem($item);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Create the panel of buttons for the backend module
     * @throws RouteNotFoundException
     */
    protected function createButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        foreach ($this->buttons as $key => $button) {
            if ($button === null) {
                continue;
            }

            $viewButton = $buttonBar->makeLinkButton()
                    ->setHref($button['href'])
                    ->setTitle($button['title'])
                    ->setIcon($button['icon'])
                    ->setDataAttributes($button['dataAttributes']);

            if (array_key_exists('classes', $button)) {
                $viewButton->setClasses($button['classes']);
            }

            if ($button['displayConditions'] === null ||
                (
                    array_key_exists($this->request->getControllerName(), $button['displayConditions']) &&
                    in_array($this->request->getControllerActionName(), $button['displayConditions'][$this->request->getControllerName()])
                )
            ) {
                $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, $key);
            }
        }
    }

    /**
     * Return button to create new record
     *
     * @param string $table Name of the table
     * @param string $title Title of the button
     * @param mixed $displayConditions An array configuring display conditions with key as controller name and action as array with actions
     * @param mixed $returnParameter Parameter to add to the automatic generated return url
     * @param string $returnUrl Url to return to after creating new record. If defined, $returnParameter will be ignored
     * @param string $iconIdentifier Name of the icon to use. If no icon is defined, the icon of the record will be used.
     * @param array $dataAttributes The data attributes to add to the button
     * @return array|null
     * @throws RouteNotFoundException
     */
    protected function createNewRecordButton($table, $title, $displayConditions = null, $returnParameter = [], $returnUrl = null, $iconIdentifier = 'actions-document-new', $dataAttributes = [])
    {
        if (!GeneralUtility::inList($this->getBackendUser()->groupData['tables_modify'], $table)
            && !$this->getBackendUser()->isAdmin()
        || $this->pageUid === 0) {
            return null;
        }

        $icon = $this->iconFactory->getIcon($iconIdentifier, Icon::SIZE_SMALL);

        if ($returnUrl === null) {
            if (!empty($returnParameter)) {
                $returnParameter = [
                    $this->getFullPluginName() => $returnParameter
                ];
            }
            $returnUrl = $this->getReturnUrl($returnParameter);
        }

        /** @var BeUriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(BeUriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('record_edit', [
            'edit[' . $table . '][' . $this->pageUid . ']' => 'new',
            'returnUrl' => $returnUrl
        ]);

        return [
            'type' => 'new',
            'href' => $url,
            'title' => $title,
            'icon' => $icon,
            'dataAttributes' => $dataAttributes,
            'displayConditions' => $displayConditions
        ];
    }

    /**
     * Return button to call some extbase action
     *
     * @param string $action Name of the action
     * @param string $controller Name of the controller
     * @param string $title Title of the button
     * @param string $icon Icon of the button
     * @param mixed $displayConditions An array configuring display conditions with key as controller name and action as array with actions
     * @param array $arguments Arguments to add to the button
     * @param array $dataAttributes The data attributes to add to the button
     * @return array
     */
    protected function createActionButton($action, $controller, $title, $icon, $displayConditions = null, $arguments = [], $dataAttributes = [])
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $url = (string)$uriBuilder->reset()->setRequest($this->request)->uriFor($action, $arguments, $controller);

        return [
            'type' => 'action',
            'href' => $url,
            'title' => $title,
            'icon' => $icon,
            'dataAttributes' => $dataAttributes,
            'displayConditions' => $displayConditions
        ];
    }

    /**
     * Return button to paste record(s) from clipboard
     *
     * @param string $table The name of the table to show the clipboard button for
     * @param array $displayConditions An array configuring display conditions with key as controller name and action as array with actions
     * @param array $dataAttributes The data attributes to add to the button
     * @return array|null
     */
    protected function createClipboardButton($table, $displayConditions = null, $dataAttributes = [])
    {
        $clipBoard = GeneralUtility::makeInstance(Clipboard::class);
        $clipBoard->initializeClipboard();
        $elFromTable = $clipBoard->elFromTable($table);

        if (!empty($elFromTable)) {
            $url = $clipBoard->pasteUrl('', $this->pageUid);
            $title = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:clip_pasteInto');
            $icon = $this->iconFactory->getIcon('actions-document-paste-into', Icon::SIZE_SMALL);

            $dataAttributes['content'] = $clipBoard->confirmMsgText('pages', BackendUtility::getRecord('pages', $this->pageUid), 'into', $elFromTable);
            $dataAttributes['title'] = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:clip_paste');

            return [
                'type' => 'clipboard',
                'href' => $url,
                'title' => $title,
                'icon' => $icon,
                'classes' => 't3js-modal-trigger',
                'dataAttributes' => $dataAttributes,
                'displayConditions' => $displayConditions
            ];
        }
        return null;
    }

    /**
     * Return button to trigger JS action
     *
     * @param string $title Title of the button
     * @param string $icon Icon of the button
     * @param array $dataAttributes The data attributes to add to the button
     * @param array $displayConditions An array configuring display conditions with key as controller name and action as array with actions
     * @return array|null
     */
    protected function createJsButton($title, $icon, $dataAttributes = [], $displayConditions = null)
    {
        return [
            'type' => 'js',
            'href' => '#',
            'title' => $title,
            'icon' => $icon,
            'dataAttributes' => $dataAttributes,
            'displayConditions' => $displayConditions
        ];
    }

    /**
     * Redirect to TCEFORM to create a new record
     *
     * @param string $table table name
     * @throws \Exception
     */
    protected function redirectToCreateNewRecord($table)
    {
        if (!isset($this->moduleName)) {
            throw new \Exception('The module name is not defined. Define $this->moduleName in the initializeAction method in your controller extending the BackendActionController.', '1471456225');
        }

        $returnUrl = 'index.php?M=' . $this->moduleName . '&id=' . $this->pageUid . $this->getToken($this->moduleName);

        /** @var BeUriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(BeUriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('record_edit', [
            'edit[' . $table . '][' . $this->pageUid . ']' => 'new',
            'returnUrl' => $returnUrl
        ]);

        HttpUtility::redirect($url);
    }

    /**
     * Redirect to TCEFORM to edit a record
     *
     * @param string $table table name
     * @throws \Exception
     */
    protected function redirectToEditRecord($table, $recordId)
    {
        if (!isset($this->moduleName)) {
            throw new \Exception('The module name is not defined. Define $this->moduleName in the initializeAction mehtod in your controller extending the BackendActionController.', '1471456225');
        }

        $returnUrl = 'index.php?M=' . $this->moduleName . '&id=' . $this->pageUid . $this->getToken($this->moduleName);

        /** @var BeUriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(BeUriBuilder::class);
        $url = (string)$uriBuilder->buildUriFromRoute('record_edit', [
            'edit[' . $table . '][' . $recordId . ']' => 'edit',
            'returnUrl' => $returnUrl
        ]);

        HttpUtility::redirect($url);
    }

    /**
     * Get a CSRF token
     *
     * @param string $moduleName The full name of the module e.g. tools_ExtensionnameModulename
     * @param bool $tokenOnly Set it to TRUE to get only the token, otherwise including the &moduleToken= as prefix
     * @return string
     */
    private function getToken($moduleName, $tokenOnly = false)
    {
        $token = FormProtectionFactory::get()->generateToken('moduleCall', $moduleName);
        if ($tokenOnly) {
            return $token;
        }
        return '&moduleToken=' . $token;
    }

    /**
     * Get return url based on the current controller context
     *
     * @return string
     * @throws RouteNotFoundException
     */
    public function getControllerContextBasedReturnUrl()
    {
        $parameter = [];
        if ($this->controllerContext) {
            $currentRequest = $this->controllerContext->getRequest();
            $fullPluginName = $this->getFullPluginName();
            $parameter[$fullPluginName] = [
                'action' => $currentRequest->getControllerActionName(),
                'controller' => $currentRequest->getControllerName()
            ];
        }

        /** @var BeUriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(BeUriBuilder::class);
        $returnUrl = (string)$uriBuilder->buildUriFromRoute($this->moduleName, $parameter);

        return $returnUrl;
    }

    /**
     * Get return url
     *
     * @param $parameter
     * @return string
     * @throws RouteNotFoundException
     */
    public function getReturnUrl($parameter)
    {
        /** @var BeUriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(BeUriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($this->moduleName, $parameter);
    }

    /**
     * @return string
     */
    private function getFullPluginName()
    {
        $extensionKey = str_replace('_', '', $this->extKey);
        return 'tx_' . $extensionKey . '_' . strtolower($this->moduleName);
    }

    /**
     * @param $menuIdentifier
     */
    public function setMenuIdentifier($menuIdentifier)
    {
        $this->menuIdentifier = $menuIdentifier;
    }

    /**
     * @param $menuItems
     */
    public function setMenuItems($menuItems)
    {
        $this->menuItems = $menuItems;
    }

    /**
     * @param $buttons
     */
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
