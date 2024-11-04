<?php

namespace Qc\QcComments\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Comment extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $submittedFormUid = '0';

    /**
     * @var string
     */
    protected string $useful = '0';

    /**
     * @var string
     */
    protected string $comment = '';

    /**
     * @var string
     */
    protected string $urlOrig = '';

    /**
     * @var int
     */
    protected int $uidOrig = 0;

    /**
     * @var int
     */
    protected int $uidPermsGroup = 0;

    /**
     * @var string
     */
    protected string $dateHour = '';

    /**
     * @var string
     */
    protected string $reasonCode = '';

    /**
     * @var string
     */
    protected string $reasonLongLabel = '';

    /**
     * @var string
     */
    protected string $reasonShortLabel = '';


    protected int $deletedByUserUid = 0;

    /**
     * @var string
     */
    protected string $deletingDate = '';

    /**
     * @var int
     */
    protected int $hiddenByUserUid = 0;

    /**
     * @var string
     */
    protected string $hiddenDate = '';

    /**
     * @var int
     */
    protected int $deleted = 0;

    /**
     * @var int
     */
    protected int $fixed = 0;

    protected int $fixedByUserUid = 0;

    /**
     * @var string
     */
    protected string $fixedDate = '';

    /**
     * @param int $hiddenByUserUid
     */

    /**
     * @var int
     */
    protected int $hidden = 0;

    protected int $hiddenComment = 0;

    /**
     * @return int
     */
    public function getHiddenComment(): int
    {
        return $this->hiddenComment;
    }

    public function getHiddenByUserUid(): int
    {
        return $this->hiddenByUserUid;
    }

    public function setHiddenByUserUid(int $hiddenByUserUid): void
    {
        $this->hiddenByUserUid = $hiddenByUserUid;
    }

    public function getHiddenDate(): string
    {
        return $this->hiddenDate;
    }

    public function setHiddenDate(string $hiddenDate): void
    {
        $this->hiddenDate = $hiddenDate;
    }



    /**
     * @param int $hiddenComment
     */
    public function setHiddenComment(int $hiddenComment): void
    {
        $this->hiddenComment = $hiddenComment;
    }

    public function getHidden(): int
    {
        return $this->hidden;
    }

    public function setHidden(int $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return int
     */
    public function getUseful(): string
    {
        return $this->useful;
    }

    /**
     * @param string $useful
     */
    public function setUseful(string $useful): void
    {
        $this->useful = $useful;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getUrlOrig(): string
    {
        return $this->urlOrig;
    }

    /**
     * @param string $urlOrig
     */
    public function setUrlOrig(string $urlOrig): void
    {
        $this->urlOrig = $urlOrig;
    }

    /**
     * @return int
     */
    public function getUidOrig(): int
    {
        return $this->uidOrig;
    }

    /**
     * @param int $uidOrig
     */
    public function setUidOrig(int $uidOrig): void
    {
        $this->uidOrig = $uidOrig;
    }

    /**
     * @return int
     */
    public function getUidPermsGroup(): int
    {
        return $this->uidPermsGroup;
    }

    /**
     * @param int $uidPermsGroup
     */
    public function setUidPermsGroup(int $uidPermsGroup): void
    {
        $this->uidPermsGroup = $uidPermsGroup;
    }

    /**
     * @return string
     */
    public function getDateHour(): string
    {
        return $this->dateHour;
    }

    /**
     * @param string $dateHour
     */
    public function setDateHour(string $dateHour): void
    {
        $this->dateHour = $dateHour;
    }

    /**
     * @param int $deletedByUserUid
     */
    public function setDeletedByUserUid(int $deletedByUserUid): void
    {
        $this->deletedByUserUid = $deletedByUserUid;
    }

    /**
     * @return int
     */
    public function getDeletedByUserUid(): int
    {
        return $this->deletedByUserUid;
    }
    /**
     * @return string
     */
    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    /**
     * @param string $reasonCode
     */
    public function setReasonCode(string $reasonCode): void
    {
        $this->reasonCode = $reasonCode;
    }
    /**
     * @param string $reasonShorLabel
     */
    public function setReasonShortLabel(string $reasonShortLabel): void
    {
        $this->reasonShortLabel = $reasonShortLabel;
    }

    /**
     * @param string $reasonLongLabel
     */
    public function setReasonLongLabel(string $reasonLongLabel): void
    {
        $this->reasonLongLabel = $reasonLongLabel;
    }

    /**
     * @return string
     */
    public function getReasonLongLabel(): string
    {
        return $this->reasonLongLabel;
    }

    /**
     * @return string
     */
    public function getReasonShortLabel(): string
    {
        return $this->reasonShortLabel;
    }

    /**
     * @param string $submittedFormUid
     */
    public function setSubmittedFormUid(string $submittedFormUid): void
    {
        $this->submittedFormUid = $submittedFormUid;
    }

    /**
     * @return string
     */
    public function getSubmittedFormUid(): string
    {
        return $this->submittedFormUid;
    }


    /**
     * @return string
     */
    public function getDeletingDate(): string
    {
        return $this->deletingDate;
    }

    /**
     * @param string $deletingDate
     */
    public function setDeletingDate(string $deletingDate): void
    {
        $this->deletingDate = $deletingDate;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    public function getFixed(): int
    {
        return $this->fixed;
    }

    public function setFixed(int $fixed): void
    {
        $this->fixed = $fixed;
    }

    public function getFixedByUserUid(): int
    {
        return $this->fixedByUserUid;
    }

    public function setFixedByUserUid(int $fixedByUserUid): void
    {
        $this->fixedByUserUid = $fixedByUserUid;
    }

    public function getFixedDate(): string
    {
        return $this->fixedDate;
    }

    public function setFixedDate(string $fixedDate): void
    {
        $this->fixedDate = $fixedDate;
    }


}
