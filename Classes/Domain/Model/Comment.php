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
     * @var string
     */
    protected string $url_orig = '';

    /**
     * @var string
     */
    protected string $uid_orig = '';

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
     * @return string
     */
    public function getUrlOrig(): string
    {
        return $this->url_orig;
    }

    /**
     * @param string $url_orig
     */
    public function setUrlOrig(string $url_orig): void
    {
        $this->url_orig = $url_orig;
    }

    /**
     * @return string
     */
    public function getUidOrig(): string
    {
        return $this->uid_orig;
    }

    /**
     * @param string $uid_orig
     */
    public function setUidOrig(string $uid_orig): void
    {
        $this->uid_orig = $uid_orig;
    }

}