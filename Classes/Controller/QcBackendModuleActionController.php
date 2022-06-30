<?php

namespace Qc\QcComments\Controller;

use LST\BackendModule\Controller\BackendModuleActionController;
use LST\BackendModule\Domain\Session\BackendSession;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QcBackendModuleActionController extends BackendModuleActionController
{
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


    public function injectBackendSession(BackendSession $backendSession)
    {
        $this->backendSession = $backendSession;
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
    }

    protected function setMenu() {}

    /**
     * @param $action
     * @param array $arguments
     * @param null $controller
     * @return string
     */
    protected function getUrl($action, $arguments = [], $controller = null): string
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->uriFor($action, $arguments, $controller);
    }


}