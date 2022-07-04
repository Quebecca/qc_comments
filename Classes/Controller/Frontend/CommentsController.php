<?php

namespace Qc\QcComments\Controller\Frontend;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class CommentsController extends ActionController
{
    public function showAction(){
        $this->view->assign('toto', 'toto value');
    }

    public function submitFormAction(){
        $this->view->assign('assigned', 'true');
    }

}