<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2660170a07cc3fb36d97d6c17f332311
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'I' => 
        array (
            'Imdb\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Imdb\\' => 
        array (
            0 => __DIR__ . '/..' . '/jcvignoli/imdbphp/src/Imdb',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Lumiere\\Admin\\Admin' => __DIR__ . '/../..' . '/class/admin/class-admin.php',
        'Lumiere\\Admin\\Admin_General' => __DIR__ . '/../..' . '/class/admin/trait-admin-general.php',
        'Lumiere\\Admin\\Admin_Menu' => __DIR__ . '/../..' . '/class/admin/class-admin-menu.php',
        'Lumiere\\Admin\\Admin_Notifications' => __DIR__ . '/../..' . '/class/admin/class-admin-notifications.php',
        'Lumiere\\Admin\\Backoffice_Extra' => __DIR__ . '/../..' . '/class/admin/class-backoffice-extra.php',
        'Lumiere\\Admin\\Cache_Tools' => __DIR__ . '/../..' . '/class/admin/class-cache-tools.php',
        'Lumiere\\Admin\\Copy_Template_Taxonomy' => __DIR__ . '/../..' . '/class/admin/class-copy-template-taxonomy.php',
        'Lumiere\\Admin\\Cron' => __DIR__ . '/../..' . '/class/admin/class-cron.php',
        'Lumiere\\Admin\\Detect_New_Template_Taxo' => __DIR__ . '/../..' . '/class/admin/class-detect-new-template-taxo.php',
        'Lumiere\\Admin\\Metabox_Selection' => __DIR__ . '/../..' . '/class/admin/class-metabox-selection.php',
        'Lumiere\\Admin\\Save_Options' => __DIR__ . '/../..' . '/class/admin/class-save-options.php',
        'Lumiere\\Admin\\Search' => __DIR__ . '/../..' . '/class/admin/class-search.php',
        'Lumiere\\Admin\\Submenu\\Cache' => __DIR__ . '/../..' . '/class/admin/submenu/class-cache.php',
        'Lumiere\\Admin\\Submenu\\Data' => __DIR__ . '/../..' . '/class/admin/submenu/class-data.php',
        'Lumiere\\Admin\\Submenu\\General' => __DIR__ . '/../..' . '/class/admin/submenu/class-general.php',
        'Lumiere\\Admin\\Submenu\\Help' => __DIR__ . '/../..' . '/class/admin/submenu/class-help.php',
        'Lumiere\\Admin\\Widget_Selection' => __DIR__ . '/../..' . '/class/admin/class-widget-selection.php',
        'Lumiere\\Alteration\\Rewrite_Rules' => __DIR__ . '/../..' . '/class/alteration/class-rewrite-rules.php',
        'Lumiere\\Alteration\\Taxonomy' => __DIR__ . '/../..' . '/class/alteration/class-taxonomy.php',
        'Lumiere\\Alteration\\Virtual_Page' => __DIR__ . '/../..' . '/class/alteration/class-virtual-page.php',
        'Lumiere\\Cli_Commands' => __DIR__ . '/../..' . '/class/class-cli-commands.php',
        'Lumiere\\Core' => __DIR__ . '/../..' . '/class/class-core.php',
        'Lumiere\\Frontend\\Frontend' => __DIR__ . '/../..' . '/class/frontend/class-frontend.php',
        'Lumiere\\Frontend\\Main' => __DIR__ . '/../..' . '/class/frontend/trait-main.php',
        'Lumiere\\Frontend\\Movie' => __DIR__ . '/../..' . '/class/frontend/class-movie.php',
        'Lumiere\\Frontend\\Movie_Data' => __DIR__ . '/../..' . '/class/frontend/class-movie-data.php',
        'Lumiere\\Frontend\\Popups\\Head_Popups' => __DIR__ . '/../..' . '/class/frontend/popups/class-head-popups.php',
        'Lumiere\\Frontend\\Popups\\Popup_Movie' => __DIR__ . '/../..' . '/class/frontend/popups/class-popup-movie.php',
        'Lumiere\\Frontend\\Popups\\Popup_Person' => __DIR__ . '/../..' . '/class/frontend/popups/class-popup-person.php',
        'Lumiere\\Frontend\\Popups\\Popup_Search' => __DIR__ . '/../..' . '/class/frontend/popups/class-popup-search.php',
        'Lumiere\\Frontend\\Widget_Frontpage' => __DIR__ . '/../..' . '/class/frontend/class-widget-frontpage.php',
        'Lumiere\\Frontend\\Widget_Legacy' => __DIR__ . '/../..' . '/class/frontend/class-widget-legacy.php',
        'Lumiere\\Link_Makers\\AMP_Links' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-amp-links.php',
        'Lumiere\\Link_Makers\\Abstract_Link_Maker' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-abstract-link-maker.php',
        'Lumiere\\Link_Makers\\Bootstrap_Links' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-bootstrap-links.php',
        'Lumiere\\Link_Makers\\Classic_Links' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-classic-links.php',
        'Lumiere\\Link_Makers\\Highslide_Links' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-highslide-links.php',
        'Lumiere\\Link_Makers\\Link_Factory' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-link-factory.php',
        'Lumiere\\Link_Makers\\No_Links' => __DIR__ . '/../..' . '/class/frontend/link_makers/class-no-links.php',
        'Lumiere\\Plugins\\Auto\\Aioseo' => __DIR__ . '/../..' . '/class/plugins/auto/class-aioseo.php',
        'Lumiere\\Plugins\\Auto\\Amp' => __DIR__ . '/../..' . '/class/plugins/auto/class-amp.php',
        'Lumiere\\Plugins\\Auto\\Oceanwp' => __DIR__ . '/../..' . '/class/plugins/auto/class-oceanwp.php',
        'Lumiere\\Plugins\\Auto\\Polylang' => __DIR__ . '/../..' . '/class/plugins/auto/class-polylang.php',
        'Lumiere\\Plugins\\Imdbphp' => __DIR__ . '/../..' . '/class/plugins/class-imdbphp.php',
        'Lumiere\\Plugins\\Logger' => __DIR__ . '/../..' . '/class/plugins/class-logger.php',
        'Lumiere\\Plugins\\Plugins_Detect' => __DIR__ . '/../..' . '/class/plugins/class-plugins-detect.php',
        'Lumiere\\Plugins\\Plugins_Start' => __DIR__ . '/../..' . '/class/plugins/class-plugins-start.php',
        'Lumiere\\Settings' => __DIR__ . '/../..' . '/class/class-settings.php',
        'Lumiere\\Taxonomy_Items_Standard' => __DIR__ . '/../..' . '/class/theme/class-taxonomy-items-standard.php',
        'Lumiere\\Taxonomy_People_Standard' => __DIR__ . '/../..' . '/class/theme/class-taxonomy-people-standard.php',
        'Lumiere\\Tools\\Ban_Bots' => __DIR__ . '/../..' . '/class/tools/class-ban-bots.php',
        'Lumiere\\Tools\\Data' => __DIR__ . '/../..' . '/class/tools/trait-data.php',
        'Lumiere\\Tools\\Files' => __DIR__ . '/../..' . '/class/tools/trait-files.php',
        'Lumiere\\Tools\\Settings_Global' => __DIR__ . '/../..' . '/class/tools/trait-settings-global.php',
        'Lumiere\\Tools\\Utils' => __DIR__ . '/../..' . '/class/tools/class-utils.php',
        'Lumiere\\Updates' => __DIR__ . '/../..' . '/class/class-updates.php',
        'Lumiere\\Updates\\Lumiere_Update_File_01' => __DIR__ . '/../..' . '/class/updates/01.php',
        'Lumiere\\Updates\\Lumiere_Update_File_02' => __DIR__ . '/../..' . '/class/updates/02.php',
        'Lumiere\\Updates\\Lumiere_Update_File_03' => __DIR__ . '/../..' . '/class/updates/03.php',
        'Lumiere\\Updates\\Lumiere_Update_File_04' => __DIR__ . '/../..' . '/class/updates/04.php',
        'Lumiere\\Updates\\Lumiere_Update_File_05' => __DIR__ . '/../..' . '/class/updates/05.php',
        'Lumiere\\Updates\\Lumiere_Update_File_06' => __DIR__ . '/../..' . '/class/updates/06.php',
        'Lumiere\\Updates\\Lumiere_Update_File_07' => __DIR__ . '/../..' . '/class/updates/07.php',
        'Lumiere\\Updates\\Lumiere_Update_File_08' => __DIR__ . '/../..' . '/class/updates/08.php',
        'Lumiere\\Updates\\Lumiere_Update_File_09' => __DIR__ . '/../..' . '/class/updates/09.php',
        'Lumiere\\Updates\\Lumiere_Update_File_10' => __DIR__ . '/../..' . '/class/updates/10.php',
        'Lumiere\\Updates\\Lumiere_Update_File_11' => __DIR__ . '/../..' . '/class/updates/11.php',
        'Lumiere\\Updates\\Lumiere_Update_File_12' => __DIR__ . '/../..' . '/class/updates/12.php',
        'Lumiere\\Updates\\Lumiere_Update_File_13' => __DIR__ . '/../..' . '/class/updates/13.php',
        'Lumiere\\Updates\\Lumiere_Update_File_14' => __DIR__ . '/../..' . '/class/updates/14.php',
        'Lumiere\\Updates\\Lumiere_Update_File_15' => __DIR__ . '/../..' . '/class/updates/15.php',
        'Lumiere\\Updates\\Lumiere_Update_File_16' => __DIR__ . '/../..' . '/class/updates/16.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2660170a07cc3fb36d97d6c17f332311::$classMap;

        }, null, ClassLoader::class);
    }
}
