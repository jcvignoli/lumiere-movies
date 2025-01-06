<?php
add_filter('wp_head', 'irp_head');
function irp_head() {
    global $post, $irp;

    if($irp->Plugin->isActive(IRP_PLUGINS_INTELLY_RELATED_POSTS_PRO)) {
        return false;
    }

    $irp->Log->startTime('irp_head');
    if($irp->Options->getPostShown()===FALSE || is_null($irp->Options->getPostShown())) {
        $irp->Options->initRelatedPostsIds(NULL);
        $irp->Options->setPostShown(NULL);
        if($post && isset($post->ID) && is_single($post->ID)) {
            $irp->Options->setPostShown($post);
            $args=array('postId'=>$post->ID, 'shuffle'=>TRUE, 'count'=>-1);
            $ids=$irp->Manager->getRelatedPostsIds($args);
            $irp->Options->initRelatedPostsIds($ids, $post->ID);
            //$irp->Log->info('POST ID=%s IS SHOWN, RELATED POSTS=%s', $post->ID, $ids);
        }
    }
    $irp->Log->pauseTime();
    return true;
}
add_filter('wp_footer', 'irp_footer');
//add_filter('admin_footer', 'irp_footer');
function irp_footer() {
    global $irp;
    if($irp->Plugin->isActive(IRP_PLUGINS_INTELLY_RELATED_POSTS_PRO)) {
        return false;
    }

    $irp->Log->startTime('irp_footer');
    $array=$irp->Options->getCssStyles();
    if(count($array)>0) {
        echo "<style>\n";
        foreach($array as $v) {
            echo wp_kses( $v, $irp->Utils->kses_allowed_html() );
            echo "\n";
        }
        echo "</style>\n";
    }
    $irp->Log->pauseTime();
    $irp->Log->stopTime();
    return true;
}

if(!shortcode_exists('irp')) {
    add_shortcode('irp', 'irp_shortcode');
}
function irp_shortcode($atts, $content='') {
    global $irp, $post;
    if($irp->Plugin->isActive(IRP_PLUGINS_INTELLY_RELATED_POSTS_PRO)) {
        return $content;
    }
    if(!$irp->Options->isActive()) {
        return '';
    }

    $default=array(
        'posts'=>''
        , 'cats'=>''
        , 'tags'=>''
        , 'count'=>1
        , 'theme'=>''
        , 'demo'=>FALSE
        , 'ctaText'=>'default'
        , 'ctaTextColor'=>'default'
        , 'postTitleColor'=>'default'
        , 'boxColor'=>'default'
        , 'borderColor'=>'default'
        , 'hasPoweredBy'=>'default'
        , 'hasShadow'=>'default'
        , 'defaultsColors'=>FALSE
        , 'includeCss'=>TRUE
    );
    $options=$irp->Utils->shortcodeAtts($default, $atts);
    if(isset($options['postId'])) {
        unset($options['postId']);
    }
    $options['demo']=$irp->Utils->isTrue($options['demo']);
    $options['count']=intval($options['count']);
    if($options['count']<=0) {
        return '';
    }

    if($options['posts']=='' && $options['cats']=='' && $options['tags']=='') {
        //dynamic
        $ids=$irp->Options->getToShowPostsIds($options['count'], TRUE);
    } else {
        if($options['posts']=='current' && $post && isset($post->ID)) {
            $options['posts']=$post->ID;
        }
        //static
        $ids=$irp->Manager->getRelatedPostsIds($options);
    }

    $keys=array('ctaText', 'ctaTextColor', 'postTitleColor', 'boxColor', 'borderColor', 'hasPoweredBy', 'hasShadow');
    foreach($keys as $k) {
        if($options[$k]=='default') {
            unset($options[$k]);
        }
    }
    $options['includeCss'] = ! $irp->Options->isDoNotIncludeCssInBox();
    $result=irp_ui_get_box($ids, $options);
    if($result!='') {
        $irp->Options->setShortcodeUsed(TRUE);
    }
    return $result;
}

function irp_ui_get_box($ids, $options=NULL) {
    global $irp;
    if($irp->Plugin->isActive(IRP_PLUGINS_INTELLY_RELATED_POSTS_PRO)) {
        return "";
    }
    if(!is_array($ids) || count($ids)==0) {
        return "";
    }
    if(!is_array($options)) {
        $options=array();
    }

    $irp->Log->startTime('irp_ui_get_box');
    $defaults=array(
        'includeCss'=>TRUE
        , 'comment'=>''
        , 'shortcode'=>FALSE
        , 'preview'=>FALSE
        , 'theme'=>''
        , 'demo'=>FALSE
        , 'array'=>FALSE
        , 'defaultsColors'=>FALSE
    );
    $options=$irp->Utils->parseArgs($options, $defaults);
    $body='';
    if($options['shortcode']) {
        $body='[irpx posts="'.implode(',', $ids).'" comment="'.$options['comment'].'"]';
    } else {
        $defaults=$irp->Options->getTemplateStyle();
        $options=$irp->Utils->parseArgs($options, $defaults);
        if($options['theme']!='') {
            $options['template']=$options['theme'];
        }
        unset($options['theme']);

        if ($options['ctaText'] == 'READ') {
            $options['ctaText'] = __('READ', IRP_PLUGIN_SLUG);
        }

        $options['ctaText'] = do_shortcode( $options['ctaText'] );

        $posts=array();
        foreach($ids as $postId) {
            $v=get_post($postId);
            if($v) {
                $posts[]=$v;
            }
        }
        if(count($posts)>0) {
            foreach($posts as $v) {
                $options['postHref']=get_permalink($v->ID);
                $options['postTitle'] = do_shortcode( $v->post_title );

                $options['postImageUrl']='';
                //$options['postImageWidth']=0;
                //$options['postImageHeight']=0;
                $attachmentId=get_post_thumbnail_id($v->ID);
                if($attachmentId!==FALSE && $attachmentId!=='' && intval($attachmentId)>0) {
                    $array=wp_get_attachment_image_src($attachmentId, 'medium');
                    if($array!==FALSE) {
                        $options['postImageUrl']=$array[0];
                        //$options['postImageWidth']=$array[1];
                        //$options['postImageHeight']=$array[2];
                    }
                }
                break;
            }

            if($irp->Utils->isTrue($options['defaultsColors'])) {
                $defaults = $irp->HtmlTemplate->getDefaults();
                $defaults = $defaults[$options['template']];
                foreach ($defaults as $k => $v) {
                    $options[$k] = $v;
                }
            }
            if($irp->Utils->isTrue($options['demo'])) {
                $options['postHref']='javascript:void(0);';
                $options['linkRel'] = IRP_DEFAULT_LINK_REL_ATTRIBUTE;
                $options['linkTarget']='';
                //$options['hasShadow']=TRUE;
                //$options['hasPoweredBy']=1;
                $ctaText=$irp->Utils->qs('ctaText');
                if($ctaText!='') {
                    $options['ctaText']=$ctaText;
                }
                $postTitle=$irp->Utils->qs('postTitle');
                $postTitle=str_replace("\\\"", "\"", $postTitle);
                if($postTitle!='') {
                    $options['postTitle']=$postTitle;
                }
                $uri=$irp->Utils->qs('postImageUrl');
                //$w=$irp->Utils->iqs('postImageWidth');
                //$h=$irp->Utils->iqs('postImageHeight');
                if($uri!='') {
                    $options['postImageUrl']=$uri;
                    //$options['postImageWidth']=$w;
                    //$options['postImageHeight']=$h;
                }
            } elseif($irp->Utils->isTrue($options['preview'])) {
                $options['postHref']='javascript:IRP_changeRelatedBox();';
                $options['linkRel'] = IRP_DEFAULT_LINK_REL_ATTRIBUTE;
                $options['linkTarget']='';
            }

            $body=$options;
            if($options['array']==FALSE) {
                $body=$irp->HtmlTemplate->html($options['template'], $options, $options);
            }
        }
    }
    $irp->Log->pauseTime();
    return $body;
}

add_filter('the_content', 'irp_the_content', intval(get_option('IRP_HookPriority','99999')));

function irp_the_content($content)
{
    global $irp, $post;

    if($irp->Plugin->isActive(IRP_PLUGINS_INTELLY_RELATED_POSTS_PRO)) {
        return $content;
    }

    $irp->Log->startTime('irp_the_content');
    if(!$post || trim($content)=='') {
        return $content;
    }

    if($irp->Options->getPostShown()===FALSE || is_null($irp->Options->getPostShown())) {
        $irp->Options->initRelatedPostsIds(NULL);
        $irp->Options->setPostShown(NULL);
        if($post && isset($post->ID) && is_single($post->ID)) {
            $irp->Options->setPostShown($post);
            $args=array('postId'=>$post->ID, 'shuffle'=>TRUE, 'count'=>-1);
            $ids=$irp->Manager->getRelatedPostsIds($args);
            $irp->Options->initRelatedPostsIds($ids, $post->ID);
            //$irp->Log->info('POST ID=%s IS SHOWN, RELATED POSTS=%s', $post->ID, $ids);
        }
    } else {
        $irp->Options->refreshRelatedPostsIds();
    }

    if($irp->Options->isPostShownExcluded()) {
        $irp->Log->error('TheContent: POST UNDEFINED OR POST EXCLUDED');
        return $content;
    }

    if(!$irp->Options->isActive() || !$irp->Options->isRewriteActive()) {
        $irp->Log->error('TheContent: NOT ACTIVE NOT REWRITE ACTIVE');
        return $content;
    }
    if($irp->Options->isShortcodeUsed()) {
        $irp->Log->error('TheContent: NOT ACTIVE DUE TO SHORTCODE USED');
        return $content;
    }
    if(!$irp->Options->hasRelatedPostsIds()) {
        $irp->Log->error('TheContent: NOT ACTIVE DUE TO WITHOUT RELATED POSTS');
        return $content;
    }

    $body=$content;
    /*if(strpos($body, '[irp')!==FALSE) {
        $irp->Log->error('TheContent: SHORTCODE DETECTED');
        $irp->Options->setShortcodeUsed(TRUE);
        return;
    }*/

    $context=new IRP_HTMLContext();
    $irp->Options->setRewriteBoxesWritten(0);
    $body=$context->execute($body);
    $irp->Log->pauseTime();
    $irp->Log->info('TheContent: BODY SUCCESSULLY CREATED');
    //$body=apply_filters('the_content', $body);
    //$post->post_content=$body;
    return $body;
}
function irp_ui_first_time() {
    global $irp;
    if($irp->Options->isShowActivationNotice()) {
        //$tcmp->Options->pushSuccessMessage('FirstTimeActivation');
        //$tcmp->Options->writeMessages();
        $irp->Options->setShowActivationNotice(FALSE);
    }
}
function irp_get_list_posts()
{
    if ( isset($_GET['q']) ) {
        $search = trim( esc_attr( sanitize_text_field( $_GET['q']) ) );
        if ( strlen($search) > 0 ) {
            add_filter('posts_where', function( $where ) use ($search) {
                $where .= (" AND post_title LIKE '%" . $search . "%'");
                return $where;
            });
        }
    }

    $postType = '';
    if ( isset($_REQUEST['irp_post_type']) ) {
        // could be an input of 'post, page, etc.'
        $postType = sanitize_text_field( $_REQUEST['irp_post_type'] );
    }

    // Fix this for custom post types
    $allowedPostTypes = array('post', 'page');

    $postType = array_filter(array_map('trim', explode(',', $postType)));

    $result = array();

    if (!empty($postType)) {
        if (empty(array_diff($postType, $allowedPostTypes))) {
            $query = array(
                'posts_per_page' => 100,
                'post_status' => 'publish',
                'post_type' => $postType,
                'order' => 'DESC',
                'orderby' => 'date',
                'suppress_filters' => false,
                'has_password' => false,
            );

            $posts = get_posts( $query );

            foreach ($posts as $this_post) {
                $post_title = $this_post->post_title;
                $id = $this_post->ID;

                $result[] = array(
                    'text' => $post_title,
                    'id' => $id,
                );
            }
        }
    }

    $posts['items'] = $result;
    echo wp_json_encode($posts);

    die();
}
add_action( 'wp_ajax_irp_list_posts', 'irp_get_list_posts' );
