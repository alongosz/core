<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\MVC\Symfony\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldHelper;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Psr\Log\LoggerInterface;

/**
 * Integration tests for ContentExtension templates.
 *
 * Tests ContentExtension in context of site with "fre-FR, eng-US" configured as languages.
 */
class ContentExtensionTest extends FileSystemTwigIntegrationTestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldHelperMock;

    /** @var \Ibexa\Core\Repository\Values\ContentType\FieldDefinition[] */
    private $fieldDefinitions = [];

    /** @var int[] */
    private $identityMap = [];

    public function getExtensions()
    {
        $this->fieldHelperMock = $this->createMock(FieldHelper::class);
        $configResolver = $this->getConfigResolverMock();

        return [
            new ContentExtension(
                $this->getRepositoryMock(),
                new TranslationHelper(
                    $configResolver,
                    $this->createMock(ContentService::class),
                    [],
                    $this->createMock(LoggerInterface::class)
                ),
                $this->fieldHelperMock
            ),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/content_functions/';
    }

    /**
     * Creates content with initial/main language being fre-FR.
     *
     * @param string $contentTypeIdentifier
     * @param array $fieldsData
     * @param array $namesData
     *
     * @return \Ibexa\Core\Repository\Values\Content\Content
     */
    protected function getContent(string $contentTypeIdentifier, array $fieldsData, array $namesData = [])
    {
        if (!array_key_exists($contentTypeIdentifier, $this->identityMap)) {
            $this->identityMap[$contentTypeIdentifier] = count($this->identityMap) + 1;
        }

        $contentTypeId = $this->identityMap[$contentTypeIdentifier];

        $fields = [];
        foreach ($fieldsData as $fieldTypeIdentifier => $fieldsArray) {
            $fieldsArray = isset($fieldsArray['id']) ? [$fieldsArray] : $fieldsArray;
            foreach ($fieldsArray as $fieldInfo) {
                // Save field definitions in property for mocking purposes
                $this->fieldDefinitions[$contentTypeId][$fieldInfo['fieldDefIdentifier']] = new FieldDefinition(
                    [
                        'identifier' => $fieldInfo['fieldDefIdentifier'],
                        'id' => $fieldInfo['id'],
                        'fieldTypeIdentifier' => $fieldTypeIdentifier,
                        'names' => isset($fieldInfo['fieldDefNames']) ? $fieldInfo['fieldDefNames'] : [],
                        'descriptions' => isset($fieldInfo['fieldDefDescriptions']) ? $fieldInfo['fieldDefDescriptions'] : [],
                    ]
                );
                unset($fieldInfo['fieldDefNames'], $fieldInfo['fieldDefDescriptions']);
                $fields[] = new Field($fieldInfo);
            }
        }
        $content = new Content(
            [
                'internalFields' => $fields,
                'versionInfo' => new VersionInfo(
                    [
                        'versionNo' => 64,
                        'names' => $namesData,
                        'initialLanguageCode' => 'fre-FR',
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                                'mainLanguageCode' => 'fre-FR',
                                // Using as id as we don't really care to test the service here
                                'contentTypeId' => $contentTypeId,
                            ]
                        ),
                    ]
                ),
            ]
        );

        return $content;
    }

    private function getConfigResolverMock()
    {
        $mock = $this->createMock(ConfigResolverInterface::class);
        // Signature: ConfigResolverInterface->getParameter( $paramName, $namespace = null, $scope = null )
        $mock->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'languages',
                            null,
                            null,
                            ['fre-FR', 'eng-US'],
                        ],
                    ]
                )
            );

        return $mock;
    }

    protected function getField($isEmpty)
    {
        $field = new Field(['fieldDefIdentifier' => 'testfield', 'value' => null]);

        $this->fieldHelperMock
            ->expects($this->once())
            ->method('isFieldEmpty')
            ->will($this->returnValue($isEmpty));

        return $field;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryMock()
    {
        $mock = $this->createMock(Repository::class);

        $mock->expects($this->any())
            ->method('getContentTypeService')
            ->will($this->returnValue($this->getContentTypeServiceMock()));

        return $mock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContentTypeServiceMock()
    {
        $mock = $this->createMock(ContentTypeService::class);

        $mock->expects($this->any())
            ->method('loadContentType')
            ->will(
                $this->returnCallback(
                    function ($contentTypeId) {
                        return new ContentType(
                            [
                                'identifier' => $contentTypeId,
                                'mainLanguageCode' => 'fre-FR',
                                'fieldDefinitions' => new FieldDefinitionCollection(
                                    $this->fieldDefinitions[$contentTypeId]
                                ),
                            ]
                        );
                    }
                )
            );

        return $mock;
    }
}

class_alias(ContentExtensionTest::class, 'eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension\ContentExtensionTest');
