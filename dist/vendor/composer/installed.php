<?php return array(
    'root' => array(
        'name' => 'jcvignoli/lumiere-movies',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'c716cb6c665999fcef1a14f38ce8ec26f7354e69',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'jcvignoli/imdbphp' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '4b6766b89d09830edc3c4a2f08660994d4516fd4',
            'type' => 'library',
            'install_path' => __DIR__ . '/../jcvignoli/imdbphp',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'jcvignoli/lumiere-movies' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'c716cb6c665999fcef1a14f38ce8ec26f7354e69',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'monolog/monolog' => array(
            'pretty_version' => '2.9.2',
            'version' => '2.9.2.0',
            'reference' => '437cb3628f4cf6042cc10ae97fc2b8472e48ca1f',
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
