<?php

namespace Qc\QcComments\Controller\Frontend;

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

use Psr\Http\Message\ResponseInterface;
use Qc\QcComments\Configuration\TyposcriptConfiguration;
use Qc\QcComments\Domain\Model\Comment;
use Qc\QcComments\Domain\Repository\CommentRepository;
use Qc\QcComments\SpamShield\SpamShieldValidator;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

// FrontEnd Controller
class CommentsController extends ActionController
{
    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentsRepository;

    /**
     * @var TyposcriptConfiguration
     */
    protected TyposcriptConfiguration  $typoscriptConfiguration;

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var bool
     */
    protected bool $isSpamShieldEnabled = false;

    public function injectCommentsRepository(CommentRepository $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    public function __construct(
    ) {
        $this->localizationUtility =
            GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->typoscriptConfiguration =
            GeneralUtility::makeInstance(TyposcriptConfiguration::class);
        $this->isSpamShieldEnabled = $this->typoscriptConfiguration->isSpamShieldEnabled();
    }


    /**
     * This function is used to render the comments form
     * @param array $args
     * @return ResponseInterface
     */
    public function showAction(array $args = []): ResponseInterface
    {
        $commentLengthconfig = [
            'maxCharacters' => $this->typoscriptConfiguration->getCommentsMaxCharacters(),
            'minCharacters' => $this->typoscriptConfiguration->getCommentsMinCharacters()
        ];
        $recaptchaConfig = [
            'enabled' => $this->typoscriptConfiguration->isRecaptchaEnabled(),
            'sitekey' => $this->typoscriptConfiguration->getRecaptchaSitekey(),
            'secret' => $this->typoscriptConfiguration->getRecaptchaSecretKey()
        ];
        $reasonOptions = $this->typoscriptConfiguration->getReasonOptions();
        $this->view->assignMultiple([
            'submitted' => $this->request->getArguments()['submitted'] ?? false,
            'submittedFormUid' => strval($this->request->getArguments()['formUid']) ?? '',
            'submittedFormType' => $this->request->getArguments()['useful'] ?? null,
            'formUpdated' => $this->request->getArguments()['formUpdated'] ?? null,
            'validationResults' => $this->request->getArguments()['validationResults'] ?? '',
            'comment' => new Comment(),
            'config' => $commentLengthconfig,
            'recaptchaConfig' => $recaptchaConfig,
            'isSpamShieldEnabled' => $this->isSpamShieldEnabled,
            'reasonOptions' => $reasonOptions
        ]);
        return $this->htmlResponse();
    }


    /**
     * This function is used to save comment
     * @param Comment|null $comment
     * @return ForwardResponse
     * @throws IllegalObjectTypeException
     */
    public function saveCommentAction(Comment $comment = null): ForwardResponse
    {
        if($this->isSpamShieldEnabled){
            $validator = GeneralUtility::makeInstance(SpamShieldValidator::class);
            $validationResults = $validator->validate($comment);
            $spamErrors = $validationResults->hasErrors();
            if($spamErrors){
                return (
                    new ForwardResponse('show'))
                        ->withArguments([
                            'submitted' => false,
                            'validationResults' => $validationResults
                        ]);
            }
        }
        if ($comment) {
            $commentType = '';
            switch ($comment->getUseful()){
                case '0' : $commentType = 'negative_reasons';break;
                case '1' : $commentType = 'positif_reasons';break;
                case 'NA' : $commentType = 'reporting_problem';break;
            }
            $selectedReasonOption = $this->getSelectedReasonOption($commentType,$comment->getReasonCode());

            $comment->setReasonShortLabel($selectedReasonOption['short_label'] ?? '');
            $comment->setReasonLongLabel($selectedReasonOption['long_label'] ?? '');
            $pageUid = $comment->getUidOrig();
            $comment->setUidPermsGroup(
                BackendUtility::getRecord(
                'pages', $pageUid,
                'perms_groupid',
                "uid = $pageUid")['perms_groupid']
            );
            if($this->typoscriptConfiguration->isAnonymizeCommentEnabled()){
                $comment->setComment(
                    $this->anonymizeComment(
                        $comment->getComment()
                    )
                );
            }
            $comment->setComment(
                substr(
                    $comment->getComment(), 0,
                    $this->typoscriptConfiguration->getCommentsMaxCharacters()
                )
            );
            $formUpdated = false;

            $comment->setDateHour(date('Y-m-d H:i:s'));

            if($comment->getSubmittedFormUid() != '0'){
                 $exisitingComment= $this->commentsRepository->findByUid(intval($comment->getSubmittedFormUid()));
                $exisitingComment->setComment($comment->getComment());
                $this->commentsRepository->update($exisitingComment);
                $formUpdated = true;
            }else{
                $this->commentsRepository->add($comment);
                $this->commentsRepository->persistenceManager->persistAll();
            }
            $submittedFormUid = strval($comment->getUid());
            return (new ForwardResponse('show'))
                ->withArguments([
                    'submitted' => true,
                    'formUid' => $submittedFormUid,
                    'useful' => $comment->getUseful(),
                    'formUpdated' => $formUpdated
                ]);
        }
        else{
            return (new ForwardResponse('show'))
                ->withArguments(['submitted' => false]);
        }

    }

    /**
     * This function returns the associated selected option
     * @param $reasonType //Negative or Problème reporting
     * @param $reason_code // Option code
     * @return array
     */
    public function getSelectedReasonOption($reasonType, $reason_code) : array {
        $options = $this->typoscriptConfiguration->getReasonOptions()[$reasonType];
        foreach ($options as $item) {
            if ($item['code'] === $reason_code) {
                return $item;
            }
        }
        return [];
    }
    /**
     * This function is used to anonymat sensible information in a comment
     * @param $comment
     * @return string
     */
    function anonymizeComment($comment): string
    {
        $pattern = $this->typoscriptConfiguration->getAnonymizationCommentPattern();
        return preg_replace_callback($pattern, function ($match) {
            $anonymatInfo = substr($match[0], strlen($match[0]) - 4);
            return '[...'.$anonymatInfo.' ]';
        }, $comment);
    }

}
