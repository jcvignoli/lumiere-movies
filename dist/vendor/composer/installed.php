<?php return array(
    'root' => array(
        'name' => 'jcvignoli/lumiere-movies',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'f54f94f125a21974a277c8e9c785a3157b449ad2',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'duck7000/imdb-graphql-php' => array(
            'pretty_version' => 'dev-jcv',
            'version' => 'dev-jcv',
            'reference' => '24d4cc28a1f22a0ac985d1317e6e2e6bbc81f750',
            'type' => 'library',
            'install_path' => __DIR__ . '/../duck7000/imdb-graphql-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'jcvignoli/lumiere-movies' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'f54f94f125a21974a277c8e9c785a3157b449ad2',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.10.0',
            'version' => '2.10.0.0',
            'reference' => '5cf826f2991858b54d5c3809bee745560a1042a7',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '2.0.0',
            'version' => '2.0.0.0',
            'reference' => 'ef29f6d262798707a9edd554e2b82517ef3a9376',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '1.0.0 || 2.0.0 || 3.0.0',
            ),
        ),
        'psr/simple-cache' => array(
            'pretty_version' => '1.0.1',
            'version' => '1.0.1.0',
            'reference' => '408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/simple-cache',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'twbs/bootstrap' => array(
            'pretty_version' => 'v5.3.3',
            'version' => '5.3.3.0',
            'reference' => '6e1f75f420f68e1d52733b8e407fc7c3766c9dba',
            'type' => 'library',
            'install_path' => __DIR__ . '/../twbs/bootstrap',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'twitter/bootstrap' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => 'v5.3.3',
            ),
        ),
    ),
);
