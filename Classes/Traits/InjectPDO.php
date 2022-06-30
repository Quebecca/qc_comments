<?php
namespace Qc\QcComments\Traits;

use PDO;
use PDOException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait InjectPDO
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @return PDO
     */
    protected function getPdo()
    {
        if ($this->pdo === null) {
            /* @var Connection $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('pages');
            $host = $connection->getHost();
            $dbname = $connection->getDatabase();
            $user = $connection->getUsername();
            $password = $connection->getPassword();
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
                // set the PDO error mode to exception
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                debug("> Connection failed: " . $e->getMessage()) ;
                throw $e;
            }
            $this->pdo = $pdo;
        }

        return $this->pdo;
    }


}