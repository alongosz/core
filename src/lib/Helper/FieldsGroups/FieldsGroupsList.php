<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Helper\FieldsGroups;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

/**
 * List of content fields groups.
 *
 * Used to group fields definitions, and apply this grouping when editing / viewing content.
 */
interface FieldsGroupsList
{
    /**
     * Returns the list of fields groups.
     * The list is a hash, with the group identifier as the key, and the human readable string as the value.
     * If groups are meant to be translated, they should be translated inside this service.
     *
     * @return array hash, with the group identifier as the key, and the human readable string as the value.
     */
    public function getGroups();

    /**
     * Returns the default field group identifier.
     *
     * @return string
     */
    public function getDefaultGroup();

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return string
     */
    public function getFieldGroup(FieldDefinition $fieldDefinition): string;
}

class_alias(FieldsGroupsList::class, 'eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList');
