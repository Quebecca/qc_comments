<?php
namespace Qc\QcComments\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Comment extends AbstractEntity
{
    /**
     * @var int
     */
    protected int $useful = 0;

    /**
     * @var string
     */
    protected string $comment = '';

    /**
     * @var int
     */
    protected int $urlOrig = 0;

    /**
     * @var int
     */
    protected int $uidOrig = 0;

    /**
     * @var int
     */
    protected int $uid_perms_group = 0;

    /**
     * @return int
     */
    public function getUseful(): int
    {
        return $this->useful;
    }

    /**
     * @param int $useful
     */
    public function setUseful(int $useful): void
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
     * @return int
     */
    public function getUrlOrig(): int
    {
        return $this->urlOrig;
    }

    /**
     * @param int $urlOrig
     */
    public function setUrlOrig(int $urlOrig): void
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
        return $this->uid_perms_group;
    }

    /**
     * @param int $uid_perms_group
     */
    public function setUidPermsGroup(int $uid_perms_group): void
    {
        $this->uid_perms_group = $uid_perms_group;
    }



}