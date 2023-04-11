<?php

declare(strict_types=1);

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
namespace Qc\QcComments\Util;

interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array<mixed>
     */
    public function toArray();

    /**
     * @param array<mixed> $values
     * @return mixed
     */
    public static function getInstanceFromArray(array $values);
}
