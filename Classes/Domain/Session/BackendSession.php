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

namespace Qc\QcComments\Domain\Session;

use __PHP_Incomplete_Class;
use phpDocumentor\Reflection\Types\String_;
use Qc\QcComments\Domain\Filter\Filter;
use Qc\QcComments\Util\Arrayable;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendSession
{
    /**
     * The backend session object
     *
     * @var BackendUserAuthentication
     */
    protected $sessionObject;

    /** @var string[] */
    protected $registeredKeys = [];

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
        $this->registerFilterKey('filter', Filter::class);
        $this->registerFilterKey('lastAction', String_::class);
    }

    /**
     * This function is used to register keys
     * @param string $key
     * @param string $class
     */
    public function registerFilterKey(string $key, string $class): void
    {
        if (!$this->isClassImplementsInterface($class, Arrayable::class) && $key != 'lastAction') {
            throw new \InvalidArgumentException('Given class not instance of Arrayable');
        }
        $this->registeredKeys[$key] = $class;
    }

    /**
     * This function is used to verify if the class implements the interface Arrayable
     * @param string $class
     * @param string $interface
     * @return bool
     */
    protected function isClassImplementsInterface(string $class, string $interface): bool
    {
        $interfaces = class_implements($class);
        if ($interfaces && in_array($interface, $interfaces)) {
            return true;
        }
        return false;
    }

    /**
     * @param $storageKey
     */
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
    public function store(string $key, $value)
    {
        if (!isset($this->registeredKeys[$key])) {
            throw new \InvalidArgumentException('Unknown key ' . $key);
        }

        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        if ($key != 'lastAction') {
            $valueArray = $value->toArray();
            $sessionData[$key] = $valueArray;
        } else {
            $sessionData[$key] = $value;
        }
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Delete a value from the session
     *
     * @param string $key
     */
    public function delete(string $key)
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
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
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
