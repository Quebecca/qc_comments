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

namespace Qc\QcCommentsTest\Tests\Functional\Comments;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Qc\QcComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \Qc\QcComments\Domain\Repository\CommentRepository
 */
class QcCommentsRepositoryFunctionalTest extends FunctionalTestCase
{
    /**
     * @var CommentRepository
     */
    protected CommentRepository $commentRepository;

    /**
     * @var array<int,string>
     */
    protected $coreExtensionsToLoad = [
        'backend',
        'beuser',
        'fluid',
        'info',
        'install',
        'core'
    ];
    /**
     * @var array<int, non-empty-string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/qc_comments'];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->getContainer()->get(LanguageServiceFactory::class)->create('default');
        /** @var Typo3Version $versionInformation */
        $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
        if ($versionInformation->getMajorVersion() >= 11) {
            $this->commentRepository = $this->getContainer()->get(CommentRepository::class);
        } else {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->commentRepository = $objectManager->get(CommentRepository::class);
        }
    }

    /**
     * @test
     * @throws \Doctrine\DBAL\Driver\Exception|\TYPO3\TestingFramework\Core\Exception
     */
    public function getComments(): void
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/Comments/Comments.xml');

        /*  $row = $this->referenceRepository->getReferences(3, 0, 1)['paginatedData'][0];
          $recordTitle = $row['recordTitle'];
          $tablename = $row['tablename'];
          $path = $row['path'];
          $groupName = $row['groupName'];
          $pid = $row['pid'];
          self::assertNotNull($row);
          self::assertSame('my header', $recordTitle);
          self::assertSame('tt_content', $tablename);
          self::assertSame('/Page 2/', $path);
          self::assertSame('Group 1', $groupName);
          self::assertSame(2, $pid);*/
    }
}
