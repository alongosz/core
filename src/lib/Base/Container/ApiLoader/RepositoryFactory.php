<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Core\Base\Container\ApiLoader;

use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Contracts\Core\Persistence\Filter\Content\Handler as ContentFilteringHandler;
use Ibexa\Contracts\Core\Persistence\Filter\Location\Handler as LocationFilteringHandler;
use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\PasswordHashService;
use Ibexa\Contracts\Core\Repository\PermissionService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Ibexa\Contracts\Core\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use Ibexa\Contracts\Core\Repository\Validator\ContentValidator;
use Ibexa\Contracts\Core\Search\Handler as SearchHandler;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\FieldTypeRegistry;
use Ibexa\Core\Repository\Helper\RelationProcessor;
use Ibexa\Core\Repository\Mapper;
use Ibexa\Core\Repository\Permission\LimitationService;
use Ibexa\Core\Repository\ProxyFactory\ProxyDomainMapperFactoryInterface;
use Ibexa\Core\Repository\Repository as CoreRepository;
use Ibexa\Core\Repository\User\PasswordValidatorInterface;
use Ibexa\Core\Search\Common\BackgroundIndexer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class RepositoryFactory implements RepositoryFactoryInterface
{
    use ContainerAwareTrait;

    /**
     * Policies map.
     */
    protected array $policyMap = [];

    private LanguageResolver $languageResolver;

    private LoggerInterface $logger;

    private ConfigResolverInterface $configResolver;

    private PersistenceHandler $persistenceHandler;

    private SearchHandler $searchHandler;

    private BackgroundIndexer $backgroundIndexer;

    private RelationProcessor $relationProcessor;

    private FieldTypeRegistry $fieldTypeRegistry;

    private PasswordHashService $passwordHashService;

    private ThumbnailStrategy $thumbnailStrategy;

    private ProxyDomainMapperFactoryInterface $proxyDomainMapperFactory;

    private Mapper\ContentDomainMapper $contentDomainMapper;

    private Mapper\ContentTypeDomainMapper $contentTypeDomainMapper;

    private Mapper\RoleDomainMapper $roleDomainMapper;

    private Mapper\ContentMapper $contentMapper;

    private ContentValidator $contentValidator;

    private LimitationService $limitationService;

    private PermissionService $permissionService;

    private ContentFilteringHandler $contentFilteringHandler;

    private LocationFilteringHandler $locationFilteringHandler;

    private PasswordValidatorInterface $passwordValidator;

    public function __construct(
        ConfigResolverInterface $configResolver,
        LanguageResolver $languageResolver,
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler,
        BackgroundIndexer $backgroundIndexer,
        RelationProcessor $relationProcessor,
        FieldTypeRegistry $fieldTypeRegistry,
        PasswordHashService $passwordHashService,
        ThumbnailStrategy $thumbnailStrategy,
        ProxyDomainMapperFactoryInterface $proxyDomainMapperFactory,
        Mapper\ContentDomainMapper $contentDomainMapper,
        Mapper\ContentTypeDomainMapper $contentTypeDomainMapper,
        Mapper\RoleDomainMapper $roleDomainMapper,
        Mapper\ContentMapper $contentMapper,
        ContentValidator $contentValidator,
        LimitationService $limitationService,
        PermissionService $permissionService,
        ContentFilteringHandler $contentFilteringHandler,
        LocationFilteringHandler $locationFilteringHandler,
        PasswordValidatorInterface $passwordValidator,
        array $policyMap,
        ?LoggerInterface $logger = null
    ) {
        $this->policyMap = $policyMap;
        $this->languageResolver = $languageResolver;
        $this->configResolver = $configResolver;
        $this->persistenceHandler = $persistenceHandler;
        $this->searchHandler = $searchHandler;
        $this->backgroundIndexer = $backgroundIndexer;
        $this->relationProcessor = $relationProcessor;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->passwordHashService = $passwordHashService;
        $this->thumbnailStrategy = $thumbnailStrategy;
        $this->proxyDomainMapperFactory = $proxyDomainMapperFactory;
        $this->contentDomainMapper = $contentDomainMapper;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->roleDomainMapper = $roleDomainMapper;
        $this->contentMapper = $contentMapper;
        $this->contentValidator = $contentValidator;
        $this->limitationService = $limitationService;
        $this->permissionService = $permissionService;
        $this->contentFilteringHandler = $contentFilteringHandler;
        $this->locationFilteringHandler = $locationFilteringHandler;
        $this->passwordValidator = $passwordValidator;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Builds the main repository, heart of Ibexa API.
     *
     * This always returns the true inner Repository. Please inject \Ibexa\Contracts\Core\Repository\Repository directly
     * instead of using this factory, in order to get an instance wrapped inside Event / Cache / * functionality.
     */
    public function buildRepository(RepositoryConfigurationProvider $repositoryConfigurationProvider): Repository
    {
        $config = $repositoryConfigurationProvider->getRepositoryConfig();

        return new CoreRepository(
            $this->persistenceHandler,
            $this->searchHandler,
            $this->backgroundIndexer,
            $this->relationProcessor,
            $this->fieldTypeRegistry,
            $this->passwordHashService,
            $this->thumbnailStrategy,
            $this->proxyDomainMapperFactory,
            $this->contentDomainMapper,
            $this->contentTypeDomainMapper,
            $this->roleDomainMapper,
            $this->contentMapper,
            $this->contentValidator,
            $this->limitationService,
            $this->languageResolver,
            $this->permissionService,
            $this->contentFilteringHandler,
            $this->locationFilteringHandler,
            $this->passwordValidator,
            $this->configResolver,
            [
                'role' => [
                    'policyMap' => $this->policyMap,
                ],
                'languages' => $this->configResolver->getParameter('languages'),
                'content' => [
                    'default_version_archive_limit' => $config['options']['default_version_archive_limit'],
                    'remove_archived_versions_on_publish' => $config['options']['remove_archived_versions_on_publish'],
                ],
            ],
            $this->logger
        );
    }

    /**
     * Returns a service based on a name string (content => contentService, etc).
     *
     * @param \Ibexa\Contracts\Core\Repository\Repository $repository
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return mixed
     */
    public function buildService(Repository $repository, string $serviceName)
    {
        $methodName = 'get' . $serviceName . 'Service';
        if (!method_exists($repository, $methodName)) {
            throw new InvalidArgumentException($serviceName, 'No such service');
        }

        return $repository->$methodName();
    }
}

class_alias(RepositoryFactory::class, 'eZ\Publish\Core\Base\Container\ApiLoader\RepositoryFactory');
