<?php return array(
    'root' => array(
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '505423cc3022a407360fb548f799388e716b165c',
        'name' => 'jcvignoli/lumiere-movies',
        'dev' => true,
    ),
    'versions' => array(
        'imdbphp/imdbphp' => array(
            'pretty_version' => 'v8.1.0',
            'version' => '8.1.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../imdbphp/imdbphp',
            'aliases' => array(),
            'reference' => '7d4b65fe18693d791dace6a8eb88d18fdc5bbbff',
            'dev_requirement' => false,
        ),
        'jcvignoli/lumiere-movies' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '505423cc3022a407360fb548f799388e716b165c',
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
            'pretty_version' => 'v5.2.3',
            'version' => '5.2.3.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../twbs/bootstrap',
            'aliases' => array(),
            'reference' => 'cb021439c683d9805e2864c58095b92d405e9b11',
            'dev_requirement' => false,
        ),
        'twitter/bootstrap' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => 'v5.2.3',
            ),
        ),
    ),
);
