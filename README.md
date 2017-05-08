# dot-mapper

DotKernel backend abstraction that implements the [Data Mapper pattern](https://martinfowler.com/eaaCatalog/dataMapper.html).
It does not offer a full ORM solution but rather a middle-ground solution for entity to database transfer with the possibility to be used with relationships too.

**Required PHP version >= 7.1**

## Installation

Run the following command in your project root directory
```bash
$ composer required dotkernel/dot-mapper
```

## Entities

Entities are part of the domain model. They model real life objects or concepts. Usually in a web application they model the database objects and their relationships.
In DotKernel we like to keep entities simple and as close to the database structure as possible. Usually they will model a table's row and can be composed into more complex objects via object composition
To use our mapper package to save entities to the database, you have to implement the `EntityInterface` interface or better, extend the `Entity` class.
Among some utility methods defined for object property handling, the Entity class defines internally the zend hydrator that is to be used when fetching or saving data to the backend.
The mappers do this automatically when using the CRUD operations.

To set the prefered hydrator, you can extend the Entity class and override the `protected $hydrator` property with the class name of the desired hydrator. The hydrator has to be registered in the hydrator manager beforehand.

We give below an entity example

##### entity example
```php
namespace SomeNamespace;

use Dot\Mapper\Entity\Entity;
//...

class MyEntity extends Entity
{
    protected $id;
    
    protected $field1;
    
    protected $field2;
    
    /**
     * This field is inherited from the base Entity class and it indicates to the mappers what hydrator to be used
     * The default value is ClassMethodsCamelCase so you don't have to write the following line if you are using that
     */
    protected $hydrator = ClassMethodsCamelCase::class;
    
    //...
    
    public function getField1()
    {
        return $this->field1;
    }
    
    public function setField1($value)
    {
        $this->field1 = $value;
    }
    
    // etc...
}
```

## Mappers

DotKernel mappers must implement the `MapperInterface` which defines the basic CRUD operations along with some specific database functions like transaction management, generated value and so on.

We already provide an abstract mapper implementation for SQL databases. The abstract mapper is based on functions offered by [zend-db](https://github.com/zendframework/zend-db). We'll implement other types of backend support in the future.

If you use an SQL database, in order to create a mapper, you should extend the `AbstractDbMapper` class. You don't have to write any database related code if all you need is CRUD operations. We'll detail later in this lesson how to create more complex mappers.

The following mapper example is all you need if you want to select, insert, update or delete an entity.
##### example 1
```php
//...
class MyEntityDbMapper extends AbstractDbMapper
{
    // an empty db mapper does support CRUD operations and has already implemented the MapperInterface's methods
}
```

## Basic mapper functions
### Selecting list of items
```php
public function find(string $type = 'all', array $options = []): array
```
* finder method for select operations. There could be multiple finder methods defined, using the following name convetions `findFinderName` where FinderName is the name of the finder. There is a findAll finder defined by default that will leave the select query intact. You can defined custom finder methods too in order to modify the select for your needs. To specify which finder to use, the `find` method's first parameter is the `$type`. Parameters:
    * `type` - the finder method to use
    * `options` - an array containing find options.

#### Find options(for SQL databases)
* `fields` - the column/field names to select from the database
* `conditions` - where conditions using boolean AND. For more complex conditions, you should use the custom finder method or define your own mapper method.
* `group` - group by select clause
* `having` - having select clause
* `order` - order by clause
* `limit` - limit number of results
* `offset` - offset where the select should start
* `page` - alternative to limit/offset pair(it will be converted to them internally)
* `joins` - array of join conditions. This needs to be detailed below

#### Join options
* join options goes into the `joins` key of the find options array. The joins options must be an array of join configurations. The join format is as following
```php
$options['joins'] = [
    'join_table_alias[optional]' => [
        'table' => 'joined table name',
        'on' => 'ON condition as string',
        'fields' => 'joined table fields to select',
        'type' => Select::INNER_JOIN,
    ],
    //...
];
```

### Counting items
```php
public function count($type = 'all', array $options = []): int
```
* used to do a count on the database. Can be used paired with the find method in order to get the total items count for pagination as an example. If used with the find method, make sure you pass the same type and options to the count method too.
* in case something went wrong, the returned value will be `-1`

### Selecting one item/entity
```php
public function get($primaryKey, array $options = [])
```
* used to select one entity based on it primary key/id value. Internally, it uses the `find` method and limits the result to one element which is returned. If no element were found, the return will be `null`.
* the options array are the same as for the `find` method with one additional supported parameter, `finder` which you can use to specify which finder method the `find` will use.

### Saving an entity
```php
public function save(EntityInterface $entity, array $options = [])
```
* saves the given entity to the database. It will do an insertion if the entity's primary key is null, meaning it is a newly created entity. It will do an update otherwise, using the entity's primary key as a where condition.
* the only default option supported is the `atomic` options($options['atomic'] = true|false) which you can use to toggle atomic save operation. It enabled the query will be wrapped in a transaction(on by default)
* of course, other options might be created if you were to extend the saving method.

### Deleting an entity
```php
public function delete(EntityInterface $entity, array $options = [])
```
* deletes the given entity. The entity should exist in the database, and the object should have its primary key/id present. The options are the same as for the save method.

### Bulk delete
```php
public function deleteAll(array $conditions)
```
* deletes many rows at once using the provided array of conditions. **Note that using this method does not work on entity level, but rather directly to the database. Also delete event do not trigger for this method.**

### Bulk update
```php
public function updateAll(array $fields, array $conditions)
```
* updates multiple rows at once, similar to bulk delete. **Again, this does not trigger save or update events.**

### Creating new empty entities
```php
public function newEntity(): EntityInterface;
```
* this can be used to dynamically create empty/new entities at runtime by using the mapper instead to create it. It might be useful in situations were the entity creation process should be more dynamic, or you don't know beforehand what kind of entity you need and you rely on the mapper, which is already set with the prototype. This method also makes sure the returned object is new, by cloning the entity prototype in the mapper.

### Other useful functions
```php
public function lastGeneratedValue(string $name = null);
```
* get the last generated id value(if supported)

```php
public function getPrototype(): EntityInterface;

public function getHydrator(): HydratorInterface;
```
* get the entity prototype and its hydrator associated with the mapper

Other methods will be described in the advanced mapper usage section. Their availability might be conditioned by the underlying backend engine used.

## The Mapper Manager

All defined mappers have to be registered and fetched from the mapper manager. The mapper manager is a special case of service container. It is an instance of the `AbstractPluginManager` type provided by Zend Service Manager.
The mapper manager is responsible for proper mapper initialization. Another feature is mapper caching - multiple calls to a mapper will result in just one initialization. The mapper manager initializes the mapper following the configuration set including the backend adapters, associated entity, event listeners and so on.

You'll use the mapper manager's single public method: `get($name, array $options = null)`; 

To access the mapper manager you can inject it manually in your classes by fetching it from the container.
```php
$container->get(MapperManager::class);
//OR
$container->get('MapperManager');
```

OR you can implement the `MapperManagerAwareInterface` along with the `MapperManagerAwareTrait`. This way you won't need to inject it yourself, and if this is the only dependency needed, you won't have to define a factory class because the mapper manager will be automatically injected by an initializer.

The mapper configuration structure is as follows
```php
return [
    'dot_mapper' => [
        'mapper_manager' => [
            'factories' => [
                //...
            ],
            'aliases' => [
                //...
            ]
        ]
    ]
];
```

Even though it is a regular zend service plugin manager, in the case of mappers the mapper registration needs to be defined more strictly. Let's see next how to setup a mapper.

## Mapper setup

* first thing to do, after you have defined a mapper class, is to register it in the mapper manager. You can do this through configuration. Mappers need to be registered with a special factory class that we provide called `DbMapperFactory` in case of SQL mappers or an extended class version if you have to customize the way the mapper is initialized.
* another requirement when registering it in the mapper manager is to define an alias for it. **The mapper alias HAVE to be the associated entity class name**. This way, when fetching the mapper, it will be initialized with the proper entity prototype and its hydrator.
```php
return [
    'dot_mapper' => [
        'mapper_manager' => [
            'factories' => [
                MyEntityDbMapper::class => DbMapperFactory::class,
            ],
            'aliases' => [
                MyEntity::class => MyEntityDbMapper::class
            ]
        ]
    ]
];
```

## Mapper configuration options

The abstract db mapper support multiple options, the majority can be overriden through configuration. We'll list them below through a configuration example and the explanations
```php
return [
    'dot_mapper' => [
        'mapper_manager' => [
            //...
        ],
        'options' => [
            'mapper' => [
                //under this key you can setup or override mapper options
                //the mapper options must be specified using the associated alias(its entity class name)
                MyEntityDbMapper::class => [
                    'adapter' => 'name of the db adapter service(database by default)',
                    
                    'table' => 'database table name(by default it will be generated from the mapper class name converting the camel case to underscore notation)',
                    
                    'alias' => 'alias of the table to use(autogenerated by default)',
                    
                    'event_listeners' => [
                        //array of mapper event listeners, to listen for mapper CRUD operation events(detailed later in the documentation)
                    ]
                ]
            ]
        ]
    ]
];
```

## Using the mapper

At this point the mapper is ready to be used if configured corectly and the backend was setup as well. In your class that implements the bussiness logic and has the mapper manager defined you can fetch the mapper from the mapper manager as below
```php
//...
class MyService implements MapperManagerAwareInterface
{
    use MapperManagerAwareTrait;
    
    //...
    
    public function saveAnEntity(MyEntity $entity)
    {
        /** @var MapperInterface $mapper **/
        $mapper = $this->getMapperManager()->get(MyEntity::class);
        return $mapper->save($entity);
    }
    
    //...
}
```

## Listening to mapper events

A mapper event listener can be created by implementing the `MapperEventListenerInterface`. In order to make it easier, we provide an abstract listener and a trait to help you setup the listener.

The mapper event object is defined in the `MapperEvent` class.

Please note that the provided abstract mappers act also as event listeners to themselfs. This can be usefull for adding additional functionality directly to the mapper.

## Mapper events

We list below the mapper event along with some tips on how you could use them and for what purpose. The grouped the events based on the mapper operation that triggers them and for each group we kept the order in which they are triggered.

## Select(find) related events

These are triggered when calling the `find` method of the mapper or the `get` method

#### MapperEvent::EVENT_MAPPER_BEFORE_FIND
* triggered after calling the mapper's `find` method. It is triggered before the query is run. It allows you to change the query at runtime or add find options. The parameters carried by this event are
    * `select` - the query object specific to the underlying database adapter
    * `type` - the finder type(defaults to 'all')
    * `options` - the array of options that was set for find operation

#### MapperEvent::EVENT_MAPPER_BEFORE_LOAD
* this event is triggered after the query was run but the result was not hydrated into entities. The event is triggered for each entity individually, in case a list of entities are fetched. The event carries the following parameters
    * `data` - the raw data of the entity as an associative array
    * `options` - the options array that was used to select the results

#### MapperEvent::EVENT_MAPPER_AFTER_LOAD
* this is also triggered for each individual entity in the result, so it can trigger many times on a find operation. It is triggered after the raw data was used to hydrate the entity prototype. Can be used to further process the entity or load additional data into it. The event object carries the following parameters:
    * `entity` - the single entity result object that was hydrated
    * `data` - the raw entity data that was used to hydrate the prototype
    * `options` - the same options array used to query the database

#### MapperEvent::EVENT_MAPPER_AFTER_FIND
* triggered after the query was run and the results were fetched and hydrated. It is triggered only once, no matter how many objects are in the result. It allows you to introspect the results and post-process them or add functionality after select operations. It can be used to load more data from other tables for example. Parameters carried by this event are:
    * `entities` - the query result as an array of entities or an empty array if no results were found
    * `type` - the finder type
    * `options` - the find options array used

## Save(insert/update) related events

The following events are triggered in the order listed below when calling the `save` method of a mapper. The save method can act as a create or update function depending on the entity saved(it has an id or not). You can check if it's a create or update operation by reading an event parameter that we'll see below.

#### MapperEvent::EVENT_MAPPER_BEFORE_SAVE
* the first event in the insert/update process, it is triggered before the actual database operation. You can do pre-save operation here or event stop the process. The event parameters are:
    * `entity` - the entity object that is to be saved
    * `options` - the options array sent to the save function
    * `isNew` - a boolean flag indicating if it is a new entity(create) or existing one(update)

#### MapperEvent::EVENT_MAPPER_AFTER_SAVE
* triggered after the entity was successfully created or updated in the database. The event parameters are:
    * `entity` - the saved entity. If it was a create operation, it will have the autogenerated id filled in
    * `options` - the options array as sent to the save method
    * `isNew` - flag indicating if it was an insert or an update operation

#### MapperEvent::EVENT_MAPPER_AFTER_SAVE_COMMIT
* additional event that marks that the transaction was committed successfully. It is the same as the previous event, but is triggered only if the operation was wrapped in an atomic transaction(as it is by default). Use this event if the atomic flag is on in order to be notified of a successful save operation. The event paramters are
    * `entity` - entity that was created or updated
    * `options` - options array as set on the save method

## Delete related events

Triggered when an entity is to be deleted by calling the mapper's `delete` method. It works only with the single entity deletion not with the `deleteAll`.

#### MapperEvent::EVENT_MAPPER_BEFORE_DELETE
* triggered before the actual entity deletion. Useful to add pre-delete operations or even stop the deletion at runtime. The event paramteres are:
    * `entity` - entity object to be deleted
    * `options` - the delete options array that was passed to the delete method

#### MapperEvent::EVENT_MAPPER_AFTER_DELETE
* triggered after the delete query was run. Useful to add post-delete operations(delete related entities etc.). It is triggered only if the deletion was succesful.
    * `entity` - entity object that was deleted
    * `options` - the delete options array as passed to the delete method

#### MapperEvent::EVENT_MAPPER_AFTER_DELETE_COMMIT
* triggered after the delete transaction was successfully commited. If you let the delete operation as atomic(by default) the delete will be wrapped in a transaction. This event marks that the transaction was a success and consequently the delete operation(along with any pre or post operations done in other delete events). The parameters are the same as for the previously described event.

## Advanced mapper usage

@ TODO: write more documentation
