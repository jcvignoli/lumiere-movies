<?php return array(
    'root' => array(
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '443635c51a21f595284890bf3129fe15d53ac72d',
        'name' => 'jcvignoli/lumiere-movies',
        'dev' => true,
    ),
    'versions' => array(
        'jcvignoli/imdbphp' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'library',
            'install_path' => __DIR__ . '/../jcvignoli/imdbphp',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'reference' => 'a30f281db3ff1d740c8067209acaccf0ee234761',
            'dev_requirement' => false,
        ),
        'jcvignoli/lumiere-movies' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '443635c51a21f595284890bf3129fe15d53ac72d',
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.9.1',
            'version' => '2.9.1.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'reference' => 'f259e2b15fb95494c83f52d3caad003bbf5ffaa1',
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '2.0.0',
            'version' => '2.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'reference' => 'ef29f6d262798707a9edd554e2b82517ef3a9376',
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
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/simple-cache',
            'aliases' => array(),
            'reference' => '408d5eafb83c57f6365a3ca330ff23aa4a5fa39b',
            'dev_requirement' => false,
        ),
        'twbs/bootstrap' => array(
            'pretty_version' => 'v5.3.1',
            'version' => '5.3.1.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../twbs/bootstrap',
            'aliases' => array(),
            'reference' => '2a1bf52b73fc9a97f6fef75aa1b29b3e9f0288b3',
            'dev_requirement' => false,
        ),
        'twitter/bootstrap' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => 'v5.3.1',
            ),
        ),
    ),
);
