<?php

namespace Qc\QcComments\Controller\Frontend;

use Qc\QcComments\Domain\Model\Comment;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class CommentsController extends ActionController
{
    public function showAction(){
        $comment = new Comment();
        $this->view->assign("comment", $comment);
    }

    public function submitFormAction(){
        debug('tttttttttttttttttttttttttttttt');
        $this->view->assign('assigned', 'true');
    }

}