<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 27/08/2018
 * Time: 11:03
 */

namespace AppBundle\Service;

use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class Namer implements NamerInterface
{
    /**
     * Creates a name for the file being uploaded
     *
     * @param object $obj The object the upload is attached to
     * @param string $field The name of the uploadable field to generate a name for
     *
     * @return string The file name
     */
    public function name($object, PropertyMapping $mapping)
    {

        return uniqid('', true);
    }

}