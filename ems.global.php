<?php

return [
    'dot_ems' => [

        'foo_service' => [

            'atomic_operations' => true,
            //'type' => 'optional concrete implementation class name, defaults to EntityService',

            'mapper' => [
                \Dot\Ems\Mapper\RelationalDbMapper::class => [

                    'adapter' => 'database',
                    'table' => 'foo_table',

                    //optional field name of the entity identifier, defaults to `id`
                    //'identifier_name' => 'id',

                    //optional paginator adapter, default ones will be used according to mapper type
                    //'pagination_adapter' => 'paginator adapter to use, must be registerd in the paginator adapter plugin manager',

                    'entity_prototype' => 'class name or service name of the entity object',
                    //'entity_hydrator' => 'optional entity hydrator class or service name, defaults to ClassMethods',

                    'delete_cascade' => false,

                    'relations' => [

                        /**
                         * This relation uses its mapper to fetch one object linked to the parent
                         */
                        \Dot\Ems\Mapper\Relation\OneToOneRelation::class => [

                            'field_name' => 'property name of the parent object which will be populated by this relation',
                            'ref_name' => 'column name which links the mappers',

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

                        ]
                    ],
                ]
            ],
        ],

    ],
];