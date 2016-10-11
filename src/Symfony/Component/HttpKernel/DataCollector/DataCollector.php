<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * DataCollector.
 *
 * Children of this class must store the collected data in the data property.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class DataCollector implements DataCollectorInterface, \Serializable
{
    protected $data;

    public function serialize()
    {
        $this->removeProxies($this->data);

        return serialize($this->data);
    }

    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

    /**
     * @param mixed $data
     *
     * remove proxy objects as these are not supposed to be serialized
     *
     * @link http://davidbu.ch/mann/blog/2012-01-23/symfony2-profiler-trying-serialize-objects-or-how-build-your-own-router.html
     */
    private function removeProxies($data)
    {
        if (is_array($data)) {
            foreach ($data as $single) {
                $this->removeProxies($single);
            }
        } else if (is_object($data)) {

            $reflection = new \ReflectionClass($data);
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                if (is_object($property->getValue($data)) && strpos(get_class($property->getValue($data)), 'Proxy') !== false) {
                    $property->setValue($data, null);
                }
            }
        }
    }
}
