<?php
namespace App\Services\PlaceMedia;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

/**
 * NamerInterface.
 *
 */
class PlaceFoldername implements DirectoryNamerInterface
{
    /**
     * Creates a directory name for the file being uploaded.
     *
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        return (string)$object->getPlace()->getId();
    }
}