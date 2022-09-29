<?php
declare(strict_types = 1);

/***
 *
 * This file is part of the "Backend Module" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2016 Christian Fries <christian.fries@lst.team>
 *
 ***/

namespace Qc\QcComments\Domain\Session;

use __PHP_Incomplete_Class;
use Qc\QcComments\Util\Arrayable;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendSession
{
    /**
     * The backend session object
     *
     * @var BackendUserAuthentication
     */
    public $sessionObject;

    protected const STORAGE_KEY_DEFAULT = 'qc_comments';


    /**
     * Unique key to store data in the session.
     * Overwrite this key in your initializeAction method.
     *
     * @var string
     */
    protected $storageKey = 'qc_comments';

    public function __construct()
    {
        $this->sessionObject = $GLOBALS['BE_USER'];
    }

    public function setStorageKey($storageKey)
    {
        $this->storageKey = $storageKey;
    }

    /**
     * Store a value in the session
     *
     * @param string $key
     * @param mixed $value
     */
    public function store($key, $value)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        $sessionData[$key] = $value;
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Delete a value from the session
     *
     * @param string $key
     */
    public function delete($key)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        unset($sessionData[$key]);
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }


    /**
     * @param string $key
     * @return false|mixed|Arrayable|null
     */
    public function get(string $key)
    {
        $sessionData = $this->sessionObject->getSessionData(self::STORAGE_KEY_DEFAULT);
        if (!isset($sessionData[$key]) || !$sessionData[$key]) {
            return null;
        }
        $result = $sessionData[$key];
        // safeguard: check for incomplete class
        if (is_object($result) && is_a($result, __PHP_Incomplete_Class::class)) {
            $this->delete($key);
            return null;
        }
        if ((is_object($result) && is_a($result, Arrayable::class)) || $key == 'lastAction') {
            return $result;
        }
        if (is_array($result) && isset($this->registeredKeys[$key])) {
            return call_user_func([$this->registeredKeys[$key], 'getInstanceFromArray'], $result);
        }
        return null;
    }
}
