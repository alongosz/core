<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\FieldType\FieldSettings;
use Ibexa\Core\FieldType\Media\Type as MediaType;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;

class MediaConverter extends BinaryFileConverter
{
    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return \Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        parent::toStorageFieldDefinition($fieldDef, $storageDef);

        $storageDef->dataText1 = (isset($fieldDef->fieldTypeConstraints->fieldSettings['mediaType'])
            ? $fieldDef->fieldTypeConstraints->fieldSettings['mediaType']
            : MediaType::TYPE_HTML5_VIDEO);
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        parent::toFieldDefinition($storageDef, $fieldDef);
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'mediaType' => $storageDef->dataText1,
            ]
        );
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }
}

class_alias(MediaConverter::class, 'eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\MediaConverter');
