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

## Mappers


