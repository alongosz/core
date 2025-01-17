<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Core\Repository\Service\Mock;

use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\TextLine\Value as TextLineValue;
use Ibexa\Core\Repository\Helper\NameSchemaService;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Tests\Core\Repository\Service\Mock\Base as BaseServiceMockTest;

/**
 * @covers \Ibexa\Core\Repository\Helper\NameSchemaService
 */
class NameSchemaTest extends BaseServiceMockTest
{
    public function testResolveUrlAliasSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['resolve']);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<urlalias_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveUrlAliasSchema($content, $contentType);

        self::assertEquals(42, $result);
    }

    public function testResolveUrlAliasSchemaFallbackToNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['resolve']);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType('<name_schema>', '');

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveUrlAliasSchema($content, $contentType);

        self::assertEquals(42, $result);
    }

    public function testResolveNameSchema()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['resolve']);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($content->fields),
            $this->equalTo($content->versionInfo->languageCodes)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveNameSchema($content, [], [], $contentType);

        self::assertEquals(42, $result);
    }

    public function testResolveNameSchemaWithFields()
    {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['resolve']);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $fields = [];
        $fields['text3']['cro-HR'] = new TextLineValue('tri');
        $fields['text1']['ger-DE'] = new TextLineValue('ein');
        $fields['text2']['ger-DE'] = new TextLineValue('zwei');
        $fields['text3']['ger-DE'] = new TextLineValue('drei');
        $mergedFields = $fields;
        $mergedFields['text1']['cro-HR'] = new TextLineValue('jedan');
        $mergedFields['text2']['cro-HR'] = new TextLineValue('dva');
        $mergedFields['text1']['eng-GB'] = new TextLineValue('one');
        $mergedFields['text2']['eng-GB'] = new TextLineValue('two');
        $mergedFields['text3']['eng-GB'] = new TextLineValue('');
        $languages = ['eng-GB', 'cro-HR', 'ger-DE'];

        $serviceMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            '<name_schema>',
            $this->equalTo($contentType),
            $this->equalTo($mergedFields),
            $this->equalTo($languages)
        )->will(
            $this->returnValue(42)
        );

        $result = $serviceMock->resolveNameSchema($content, $fields, $languages, $contentType);

        self::assertEquals(42, $result);
    }

    /**
     * @dataProvider resolveDataProvider
     *
     * @param string[] $schemaIdentifiers
     * @param string $nameSchema
     * @param string[] $languageFieldValues field value translations
     * @param string[] $fieldTitles [language => [field_identifier => title]]
     * @param array $settings NameSchemaService settings
     */
    public function testResolve(
        array $schemaIdentifiers,
        $nameSchema,
        $languageFieldValues,
        $fieldTitles,
        $settings = []
    ) {
        $serviceMock = $this->getPartlyMockedNameSchemaService(['getFieldTitles'], $settings);

        $content = $this->buildTestContentObject();
        $contentType = $this->buildTestContentType();

        $index = 0;
        foreach ($languageFieldValues as $languageCode => $fieldValue) {
            $serviceMock->expects(
                $this->at($index++)
            )->method(
                'getFieldTitles'
            )->with(
                $schemaIdentifiers,
                $contentType,
                $content->fields,
                $languageCode
            )->will(
                $this->returnValue($fieldTitles[$languageCode])
            );
        }

        $result = $serviceMock->resolve($nameSchema, $contentType, $content->fields, $content->versionInfo->languageCodes);

        self::assertEquals($languageFieldValues, $result);
    }

    /**
     * Data provider for the @see testResolve method.
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            [
                ['text1'],
                '<text1>',
                [
                    'eng-GB' => 'one',
                    'cro-HR' => 'jedan',
                ],
                [
                    'eng-GB' => ['text1' => 'one'],
                    'cro-HR' => ['text1' => 'jedan'],
                ],
            ],
            [
                ['text2'],
                '<text2>',
                [
                    'eng-GB' => 'two',
                    'cro-HR' => 'dva',
                ],
                [
                    'eng-GB' => ['text2' => 'two'],
                    'cro-HR' => ['text2' => 'dva'],
                ],
            ],
            [
                ['text1', 'text2'],
                'Hello, <text1> and <text2> and then goodbye and hello again',
                [
                    'eng-GB' => 'Hello, one and two and then goodbye...',
                    'cro-HR' => 'Hello, jedan and dva and then goodb...',
                ],
                [
                    'eng-GB' => ['text1' => 'one', 'text2' => 'two'],
                    'cro-HR' => ['text1' => 'jedan', 'text2' => 'dva'],
                ],
                [
                    'limit' => 38,
                    'sequence' => '...',
                ],
            ],
        ];
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    protected function getFields()
    {
        return [
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('one'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('two'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text1',
                    'value' => new TextLineValue('jedan'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text2',
                    'value' => new TextLineValue('dva'),
                ]
            ),
            new Field(
                [
                    'languageCode' => 'cro-HR',
                    'fieldDefIdentifier' => 'text3',
                    'value' => new TextLineValue(''),
                ]
            ),
        ];
    }

    /**
     * @return \Ibexa\Core\Repository\Values\ContentType\FieldDefinition[]
     */
    protected function getFieldDefinitions()
    {
        return [
            new FieldDefinition(
                [
                    'id' => '1',
                    'identifier' => 'text1',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => '2',
                    'identifier' => 'text2',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
            new FieldDefinition(
                [
                    'id' => '3',
                    'identifier' => 'text3',
                    'fieldTypeIdentifier' => 'ezstring',
                ]
            ),
        ];
    }

    /**
     * Build Content Object stub for testing purpose.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     */
    protected function buildTestContentObject()
    {
        return new Content(
            [
                'internalFields' => $this->getFields(),
                'versionInfo' => new VersionInfo(
                    [
                        'languageCodes' => ['eng-GB', 'cro-HR'],
                    ]
                ),
            ]
        );
    }

    /**
     * Build ContentType stub for testing purpose.
     *
     * @param string $nameSchema
     * @param string $urlAliasSchema
     *
     * @return \Ibexa\Core\Repository\Values\ContentType\ContentType
     */
    protected function buildTestContentType($nameSchema = '<name_schema>', $urlAliasSchema = '<urlalias_schema>')
    {
        return new ContentType(
            [
                'nameSchema' => $nameSchema,
                'urlAliasSchema' => $urlAliasSchema,
                'fieldDefinitions' => $this->getFieldDefinitions(),
            ]
        );
    }

    /**
     * Returns the content service to test with $methods mocked.
     *
     * Injected Repository comes from {@see getRepositoryMock()}
     *
     * @param string[] $methods
     * @param array $settings
     *
     * @return \Ibexa\Core\Repository\Helper\NameSchemaService|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedNameSchemaService(array $methods = null, array $settings = [])
    {
        return $this->getMockBuilder(NameSchemaService::class)
            ->setMethods($methods)
            ->setConstructorArgs(
                [
                    $this->getPersistenceMock()->contentTypeHandler(),
                    $this->getContentTypeDomainMapperMock(),
                    $this->getFieldTypeRegistryMock(),
                    $settings,
                ]
            )
            ->getMock();
    }
}

class_alias(NameSchemaTest::class, 'eZ\Publish\Core\Repository\Tests\Service\Mock\NameSchemaTest');
