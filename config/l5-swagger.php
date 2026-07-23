<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Diagram Coffee API Documentation',
            ],
            'routes' => [
                /*
                 * Route for accessing api documentation interface
                */
                'api' => 'api/documentation',

                /*
                 * Route for accessing parsed swagger annotations.
                */
                'docs' => 'docs',

                /*
                 * Route for Oauth2 authentication callback.
                */
                'oauth2_callback' => 'api/oauth2-callback',

                'middleware' => [
                    'api' => [
                        'restrict-swagger',
                    ],
                    'asset' => [],
                    'docs' => [],
                    'oauth2' => [],
                ],
            ],
            'paths' => [
                /*
                 * Absolute path to location where parsed annotations will be stored
                */
                'docs' => storage_path('api-docs'),

                /*
                 * Edit to include full URL in ui for assets
                */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * File name of the generated json documentation
                */
                'docs_json' => 'api-docs.json',

                /*
                 * File name of the generated yaml documentation
                */
                'docs_yaml' => 'api-docs.yaml',

                /*
                * Set this to `json` or `yaml` to determine which documentation file to use in UI
                */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Absolute paths to directory containing the swagger annotations are stored.
                */
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'swagger_version' => env('SWAGGER_VERSION', '3.0.0'),
        ],

        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
            'excludes' => [],
        ],

        'scanOptions' => [
            'pattern' => '*.php',
            'analyser' => null,
            'analysis' => null,
            'processors' => [],
            'exclude' => [],
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', '3.0.0'),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'description' => 'Sanctum Bearer Token. Example: "Bearer 1|token..."',
                    'name' => 'Authorization',
                    'in' => 'header',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
                'internalApiKey' => [
                    'type' => 'apiKey',
                    'description' => 'Header X-Internal-Secret untuk Python ML Service',
                    'name' => 'X-Internal-Secret',
                    'in' => 'header',
                ],
                'xenditWebhookToken' => [
                    'type' => 'apiKey',
                    'description' => 'Header x-callback-token untuk Xendit Webhook',
                    'name' => 'x-callback-token',
                    'in' => 'header',
                ],
            ],
            'security' => [],
        ],

        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', true),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'validator_url' => null,

        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'list'),
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),
                'oauth2' => [
                    'use_pkce_with_response_code_grant' => false,
                ],
            ],
        ],

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
        ],
    ],
];
