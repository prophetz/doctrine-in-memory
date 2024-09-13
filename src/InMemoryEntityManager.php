<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use ReflectionProperty;
use RuntimeException;
use DateTimeInterface;

class InMemoryEntityManager implements EntityManagerInterface
{
    /**
     * This holds all of the InMemoryRepository objects, which will be lazily
     * instantiated as they are first used.
     */
    private RepositoryContainer $repos;

    /**
     * The mapping driver used for reading the Doctrine ORM mappings from
     * entities.
     */
    private MappingDriver $mappingDriver;

    /**
     * @var array<class-string, object[]>
     */
    private $needIds = [];

    /**
     * @var array<class-string, object[]>
     */
    private $pendingDeletes = [];

    /**
     * @var callable[]
     */
    private array $onFlushCallbacks = [];

    public function __construct(MappingDriver $driver)
    {
        $this->mappingDriver = $driver;
        $this->repos = new RepositoryContainer();
    }

    public function addOnFlushCallback(callable $callback): void
    {
        $this->onFlushCallbacks[] = $callback;
    }

    /**
     * Finds an object by its identifier.
     *
     * This is just a convenient shortcut for getRepository($className)->find($id).
     *
     * @template Entity of object
     * @param class-string<Entity> $className
     * @param mixed  $id        The identity of the object to find.
     *
     * @return ?Entity The found object.
     */
    public function find(
        string $className,
        mixed $id,
        LockMode|int|null $lockMode = null,
        int|null $lockVersion = null
    ): object|null {
        return $this->getRepository($className)->find($id);
    }

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     *
     * @return void
     */
    public function persist(object $object)
    {
        $class = get_class($object);
        $this->getRepository($class)->manage($object);
        $this->needIds[$class][] = $object;
    }

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object The object instance to remove.
     *
     * @return void
     */
    public function remove($object)
    {
        $this->pendingDeletes[get_class($object)][] = $object;
    }

    /**
     * Merges the state of a detached object into the persistence context
     * of this ObjectManager and returns the managed copy of the object.
     * The object passed to merge will not become associated/managed with this ObjectManager.
     *
     * @param object $object
     *
     * @return object
     */
    public function merge($object)
    {
        $repo = $this->getRepository(get_class($object));
        $repo->manage($object);
        return $object;
    }

    /**
     * Clears the ObjectManager. All objects that are currently managed
     * by this ObjectManager become detached.
     *
     * @param string|null $objectName if given, only objects of this type will get detached.
     *
     * @return void
     */
    public function clear($objectName = null)
    {
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * @return void
     */
    public function flush(): void
    {
        foreach ($this->pendingDeletes as $className => $entities) {
            $repo = $this->getRepository($className);
            foreach ($entities as $entity) {
                $repo->remove($entity);
            }
        }
        $this->pendingDeletes = [];

        foreach ($this->needIds as $className => $entities) {
            $repo = $this->getRepository($className);
            if (!$repo->isIdGenerated()) {
                continue;
            }
            $idField = $repo->getIdField();
            $idType = $repo->getIdType();
            $rp = new ReflectionProperty($className, $idField);
            $rp->setAccessible(true);
            foreach ($entities as $entity) {
                if (!$rp->isInitialized($entity) || $rp->getValue($entity) === null) {
                    $id = random_int(0, PHP_INT_MAX);
                    if ($idType === 'string') {
                        $id = (string) $id;
                    }
                    $rp->setValue($entity, $id);
                }
            }
        }
        $this->needIds = [];
        foreach ($this->onFlushCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * Gets the repository for a class.
     *
     * @template Entity of object
     * @param class-string<Entity> $className
     * @return InMemoryRepository<Entity>
     */
    public function getRepository(string $className): EntityRepository
    {
        if (!$this->repos->has($className)) {
            $this->repos->set($className, new InMemoryRepository($className, $this->mappingDriver));
        }

        return $this->repos->get($className);
    }

    public function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function isOpen(): bool
    {
        return true;
    }

    public function detach($object)
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function refresh(object $object, LockMode|int|null $lockMode = null): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getClassMetadata($className): ClassMetadata
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getMetadataFactory(): ClassMetadataFactory
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function initializeObject(object $obj)
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function contains(object $object)
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getCache(): Cache|null
    {
        return null;
    }

    public function getConnection(): Connection
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getExpressionBuilder(): Expr
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function beginTransaction(): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function commit(): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function rollback(): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function createQuery($dql = ''): Query
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function createNativeQuery(string $sql, ResultSetMapping $rsm): NativeQuery
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function createQueryBuilder(): QueryBuilder
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getReference($entityName, $id): object|null
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function close(): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function lock(object $entity, LockMode|int $lockMode, DateTimeInterface|int|null $lockVersion = null): void
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getEventManager(): EventManager
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getUnitOfWork(): UnitOfWork
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function newHydrator($hydrationMode): AbstractHydrator
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getProxyFactory(): ProxyFactory
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function getFilters(): Query\FilterCollection
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function isFiltersStateClean(): bool
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function hasFilters(): bool
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }

    public function wrapInTransaction(callable $func): mixed
    {
        throw new RuntimeException(__METHOD__ . ' not yet implemented');
    }
}
