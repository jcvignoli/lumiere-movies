<?php return array(
    'root' => array(
        'name' => 'jcvignoli/lumiere-movies',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'ccfe83de4088f9768e4c9a90fa5f4db2853033ca',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'imdbphp/imdbphp' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '0120e85209991d0eeece62be6f491564ebcb6f4a',
            'type' => 'library',
            'install_path' => __DIR__ . '/../imdbphp/imdbphp',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'jcvignoli/lumiere-movies' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'ccfe83de4088f9768e4c9a90fa5f4db2853033ca',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.8.0',
            'version' => '2.8.0.0',
            'reference' => '720488632c590286b88b80e62aa3d3d551ad4a50',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '1.1.4',
            'version' => '1.1.4.0',
            'reference' => 'd49695b909c3b7628b6289db5479a1c204601f11',
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
            'pretty_version' => 'v5.2.2',
            'version' => '5.2.2.0',
            'reference' => '961d5ff9844372a4e294980c667bbe7e0651cdeb',
            'type' => 'library',
            'install_path' => __DIR__ . '/../twbs/bootstrap',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'twitter/bootstrap' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => 'v5.2.2',
            ),
        ),
    ),
);
