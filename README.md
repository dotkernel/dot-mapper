# dot-ems

This package provides abstraction over entity objects to backend storage using the data mapper pattern.
It offers entity services that are able to apply  basic CRUD operations on entities that are reflected in the backend storage with minimum amount of effort.

The package is written with configuration files in mind. You can create hierarchies of entities through relations just by using configuration files.

## Installation

Run the following composer command in your project directory
```bash
$ composer require dotkernel/dot-ems
```

Merge the `ConfigProvider` to your application's configuration, in order to register the required dependencies.

In your `config/autoload` directory create a configuration file `ems.global.php` that will hold the module's further configuration.

## Usage

You can define entity services by configuration, as given in the below example

##### ems.global.php

```php
return [
    'service' => [
     
        //a simple service example, for CRUD operations on a single table
        'foo_service' => [
            //enable operations to be made using transactions, if supported by the backend
            'atomic_operations' => true,
            
            //optional, can be used to specified a custom class as an entity service, must implement the package's ServiceInterface
            //'type' => 'optional concrete implementation class name, defaults to EntityService',
            
            //mapper configuration, as key=>config, where key is the mapper's name as registered in the MapperPluginManager
            'mapper' => [
                //this is a predefined mysql mapper to work with only one table
                \Dot\Ems\Mapper\DbMapper::class => [
                    //tell the mapper where it can find the db adapter in the service manager
                    'adapter' => 'database',
                    
                    //tell the mapper the table name to work with
                    'table' => 'foo',

                    //optional field name of the entity identifier, defaults to `id`
                    //'identifier_name' => 'id',

                    //optional paginator adapter, default ones will be used according to mapper type
                    //'pagination_adapter' => 'paginator adapter to use, must be registerd in the paginator adapter plugin manager',

                    'entity_prototype' => 'class name or service name of the entity object',
                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',
                ]
            ],
        ],
        
        //multiple tables linked by relations
        'bar_service' => [
        
            'atomic_operations' => true,
            //'type' => 'optional concrete implementation class name, defaults to EntityService',

            'mapper' => [
                \Dot\Ems\Mapper\RelationalDbMapper::class => [

                    'adapter' => 'database',
                    'table' => 'bar',

                    //optional field name of the entity identifier, defaults to `id`
                    //'identifier_name' => 'id',

                    //optional paginator adapter, default ones will be used according to mapper type
                    //'pagination_adapter' => 'paginator adapter to use, must be registerd in the paginator adapter plugin manager',

                    'entity_prototype' => 'class name or service name of the entity object',
                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',

                    'relations' => [

                        /**
                         * This relation uses its mapper to fetch one object linked to the parent
                         */
                        \Dot\Ems\Mapper\Relation\OneToOneRelation::class => [

                            'field_name' => 'property name of the parent object which will be populated by this relation',
                            'ref_name' => 'column name which links the mappers',

                            //delete references when parent entity is deleted
                            'delete_refs' => false,

                            //create/update references when parent is updated
                            'change_refs' => true,

                            /**
                             * Associated mapper, note you can use nested relational db mappers in order to create a hierarchy of objects
                             */
                            'mapper' => [
                                \Dot\Ems\Mapper\DbMapper::class => [
                                    'adapter' => 'database',
                                    'table' => 'table_name',

                                    //optional field name of the entity identifier, defaults to `id`
                                    //'identifier_name' => 'id',

                                    //optional paginator adapter, default ones will be used according to mapper type
                                    //'pagination_adapter' => 'paginator adapter to use, must be registerd in the paginator adapter plugin manager',

                                    'entity_prototype' => 'class name or service name of the entity object',
                                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',
                                ]
                            ]
                        ],

                        /**
                         * This relation will use its mapper to fetch an array of objects that are linked to the parent entity
                         */
                        \Dot\Ems\Mapper\Relation\OneToManyRelation::class => [

                            'field_name' => 'property name of the parent object which will be populated by this relation',
                            'ref_name' => 'column name which links the tables',

                            //delete references when parent entity is deleted
                            'delete_refs' => false,

                            //create/update references when parent is updated
                            'change_refs' => true,

                            'mapper' => [
                                \Dot\Ems\Mapper\DbMapper::class => [

                                    'adapter' => 'database',
                                    'table' => 'table_name',

                                    //optional field name of the entity identifier, defaults to `id`
                                    //'identifier_name' => 'id',

                                    //optional paginator adapter, default ones will be used according to mapper type
                                    //'pagination_adapter' => 'paginator adapter to use, must be registerd in the paginator adapter plugin manager',

                                    'entity_prototype' => 'class name or service name of the entity object',
                                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',

                                ]
                            ],
                        ],

                        /**
                         * This relation will use its mapper to fetch an array of objects that are linked to the parent entity through an intersection table
                         */
                        \Dot\Ems\Mapper\Relation\ManyToManyRelation::class => [

                            'field_name' => 'property name of the parent object which will be populated by this relation',

                            'ref_name' => 'column name which links the table to the intersection table',
                            'target_ref_name' => 'column name which links the intersection table to the target table',

                            //delete references when parent entity is deleted(in this case link from intersection table)
                            'delete_refs' => false,

                            //create/update link in the intersection table when parent property is changed
                            'change_refs' => true,

                            //enables creation of linked entities, only if they are detected as new
                            //no other operation is made through this relation on the linked entities
                            'create_target_refs' => true,

                            //if a linked entity is new, enable creation
                            'create_target_entities' => true,

                            'intersection_mapper' => [
                                \Dot\Ems\Mapper\DbMapper::class => [
                                    'adapter' => 'database',
                                    'table' => 'intersection table name',

                                    'entity_prototype' => 'class name or service name of the entity object',
                                ]
                            ],

                            'target_mapper' => [
                                \Dot\Ems\Mapper\DbMapper::class => [

                                    'adapter' => 'database',
                                    'table' => 'target table_name',

                                    //optional field name of the entity identifier, defaults to `id`
                                    //'identifier_name' => 'id',

                                    'entity_prototype' => 'class name or service name of the entity object',
                                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',

                                ],
                            ],
                        ]
                    ],
                ]
            ],
        ],
        
    ],
];
```

After defining these 2 services, you can inject them as dependencies by getting them from the service manager as
```php
$service = $container->get('dot-ems.service.foo_service');

$service->findAll(...
```

## Mappers

Mappers are classes that interact directly with a backend engine, abstracting the backend from the application.
All mappers have to implement this package's `MapperInterface` which defines the CRUD methods for an entity.
Also, in order to be used in this package's context, mappers have to be registered in the MapperPluginManager.

The role of mappers, apart from being the class responsible to work directly with the backend, is that, by using various implementation that adhere to the same interface, backends ca easily be changed just by writing a custom mapper for it.



#### DbMapper

Class based on zend-db adapter and TableGateway, to work with various PDO backends. It can work with just one table.


### RelationalMapperInterface

Interface extending the base MapperInterface, to support mappers that can be configured with relational tables. By defining relations between multiple mappers, you can apply CRUD operations on an object with sub-entities in one shot.
Nested relational mappers can be used to create complex object structures.

## Relations

Relations are used in conjunction with the relational mappers to defined the link between tables.
All relations must implement this package's `RelationInterface` and be registered in the RelationPluginManager

* `OneToOneRelation` - links the parent entity to a sub-entity from another table, storing it into one if the parent's property
* `OneToManyRelation` - links the parent entity to a list of sub-entities(as an array of sub-entity objects)
* `ManyToManyRelation` - is similar to one to many relation in many ways, as it populates the parent property with an array of sub-entities, but uses an intersection table to get the linked sub-entities

## Entity services

The default entity service is just a proxy to the underlying mapper. We don't want to work with the mappers directly, so we wrap them in services.
Additional logic can be defined in services, by extending the base service and specify it in the `type` configuration key.

Entity services have to implement this package's `ServiceInterface`.

...TO BE COMPLETED...
