<?php

namespace Qc\QcComments\Controller\Frontend;

use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class CommentsController extends ActionController
{
    protected CommentRepository $commentsRepository;
    public function injectCommentsRepository(CommentRepository $commentsRepository){
        $this->commentsRepository = $commentsRepository;
    }

    public function showAction(array $args = []){
        $comment = new Comment();
        $this->view->assignMultiple([
            "submitted" => $this->request->getArguments()['submitted'],
            "comment" => $comment
        ]);
    }

    /**
     * @param Comment|null $comment
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function addCommentAction(Comment $comment = null){
        $this->commentsRepository->add($comment);
        $this->forward('show',null, null, ['submitted' => true]);
    }

}