<?php
/**
Plugin Name: 优刻得UCloud对象存储插件
Plugin URI: https://www.laojiang.me/contact/
Description: WordPress同步附件内容远程至UCloud对象存储中，实现网站数据与静态资源分离，提高网站加载速度。公众号：老蒋朋友圈。
Version: 3.0
Author: 老蒋和他的小伙伴
Author URI: https://www.laojiang.me
Requires PHP: 7.4
 */
if (!defined('ABSPATH')) die();

// 插件激活时检测 PHP 版本，不满足则阻止激活
register_activation_hook(__FILE__, function() {
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            '<p><strong>优刻得UCloud对象存储插件</strong> 需要 PHP 7.4 或更高版本，当前 PHP 版本为 ' . esc_html(PHP_VERSION) . '。请升级 PHP 后重试激活。</p>',
            '插件激活失败',
            array('back_link' => true)
        );
    }
});

// 插件仅支持 PHP 7.4 及以上版本
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>优刻得UCloud对象存储插件</strong> 需要 PHP 7.4 或更高版本，当前 PHP 版本为 ' . esc_html(PHP_VERSION) . '，请升级 PHP 后使用。</p></div>';
    });
    return;
}

if (!class_exists('WPUFile')) {
    class WPUFile {

        private $option_name     = 'WPUFile_options';             // 插件参数保存名称
        private $menu_title      = 'UCloud云存储设置';            // 设置菜单的菜单名
        private $page_title      = 'UCloud云存储设置';            // 设置菜单的页面title
        private $capability      = 'manage_options';              // 设置页面管理所需权限
        private $version         = '3.0';                         // 插件数据版本， 每次修改应与上方的Version值相同
        private $setting_notices = [
            'update_success' => 'UCloud对象存储插件设置完毕',  // post数据保存成功时提示内容
            'update_failed'  => 'UCloud对象存储插件设置失败',  // 失败时提示
        ];

        private $base_folder;
        private $wp_upload_dir;
        private $object_storage;
        private $options;

        function __construct() {
            # 插件 activation 函数当一个插件在 WordPress 中”activated(启用)”时被触发。
            register_activation_hook(__FILE__, array($this, 'init_options'));
            register_deactivation_hook(__FILE__, array($this, 'restore_options'));  # 禁用时触发钩子

            $this->includes();
            $this->constants();           

            # 避免上传插件/主题被同步到对象存储
            if (isset($_SERVER['REQUEST_URI']) && substr_count($_SERVER['REQUEST_URI'], '/update.php') <= 0) {
                add_filter('wp_handle_upload', array($this, 'upload_attachments'));
                if ( version_compare(get_bloginfo('version'), 5.3, '<') ){
                    add_filter( 'wp_update_attachment_metadata', array($this, 'upload_and_thumbs') );
                } else {
                    add_filter( 'wp_generate_attachment_metadata', array($this, 'upload_and_thumbs') );
                    add_filter( 'wp_save_image_editor_file', array($this, 'save_image_editor_file') );
                }
            }

            # 检测不重复的文件名
            add_filter('wp_unique_filename', array($this, 'unique_filename') );

            # 删除文件时触发删除远端文件，该删除会默认删除缩略图
            add_action('delete_attachment', array($this, 'delete_remote_attachment'));

            # 添加插件设置菜单
            add_action('admin_menu', array($this, 'admin_menu_setting'));
            add_filter('plugin_action_links', array($this, 'setting_plugin_action_links'), 10, 2);
            # 自动重命名
            add_filter( 'sanitize_file_name', array($this, 'sanitize_file_name_handler'), 10, 1 );
        }

        private function includes() {
            require_once('api.php');
        }

        private function constants() {
            $this->base_folder = plugin_basename(dirname(__FILE__));
            $this->wp_upload_dir = wp_get_upload_dir();
            $this->options = get_option($this->option_name);
            # PHP7.4 版本后，对于bool值作为array调用时，会产生警告内容。
            if (!is_array($this->options)) {
                $this->init_options();
            }
            $this->object_storage = new UFileStorageObjectApi($this->options);  // option更新后，若变动了参数，则Api实例重新创建，目前只有setting中会触发
        }

        /**
         * 文件上传功能基础函数，被其它需要进行文件上传的模块调用
         * @param $key  : 远端需要的Key值[包含路径]
         * @param $file_local_path : 文件在本地的路径。
         *
         * @return bool  : 暂未想好如何与wp进行响应。

         */
        public function _file_upload($key, $file_local_path) {
            ### 上传文件
            # 由于增加了独立文件名钩子对cos中同名文件的判断，避免同名文件的存在，因此这里直接覆盖上传。
            try {
                $this->object_storage->Upload(
                    $this->key_handler($key, get_option('upload_url_path')),
                    $file_local_path
                );
                // 如果上传成功，且不再本地保存，在此删除本地文件
                if ($this->options['no_local_file']) {
                    $this->delete_local_file($file_local_path);
                }
                return True;
            } catch (\Exception $e) {
                return False;
            }
        }

        private function remote_key_exist( $filename ) {
            return $this->object_storage->hasExist( $this->key_handler($this->wp_upload_dir['subdir'] . "/$filename",
                get_option('upload_url_path')));
        }

        /**
         * 删除远程附件（包括图片的原图）
         *   这里全部以非/开头，因此上传的函数中也要替换掉key中开头的/
         * @param $post_id
         */
        public function delete_remote_attachment($post_id) {
            // 获取要删除的对象Key的数组
            $deleteObjects = array();
            $meta = wp_get_attachment_metadata( $post_id );
            $upload_url_path = get_option('upload_url_path');

            if (isset($meta['file'])) {
                $attachment_key = $meta['file'];
                array_push($deleteObjects, $this->key_handler($attachment_key, $upload_url_path));
            } else {
                $file = get_attached_file( $post_id );
                $attached_key = str_replace( $this->wp_upload_dir['basedir'] . '/', '', $file );  # 不能以/开头
                $deleteObjects[] = $this->key_handler($attached_key, $upload_url_path);
            }

            if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
                foreach ($meta['sizes'] as $val) {
                    $attachment_thumbs_key = dirname($meta['file']) . '/' . $val['file'];
                    $deleteObjects[] = $this->key_handler($attachment_thumbs_key, $upload_url_path);
                }
            }

            if ( !empty( $deleteObjects ) ) {
                // 执行删除远程对象
                $allKeys = array_chunk($deleteObjects, 1000);  # 每次最多删除1000个，多于1000循环进行
                foreach ($allKeys as $keys){
                    //删除文件, 每个数组1000个元素
                    $this->object_storage->Delete($keys);
                }
            }
        }

        // 初始化选项
        // TODO: 让不同对象存储适用相同参数与setting
        public function init_options() {
            $options = array(
                'version' => $this->version,  # 用于以后当有数据结构升级时初始化数据
                'bucket' => "",
                'endpoint' => "",
                'UCLOUD_PUBLIC_KEY' => "",
                'UCLOUD_PRIVATE_KEY' => "",
                'no_local_file' => False,  # 不在本地保留备份
                'backup_url_path' => '',

                'opt' => array(
                    'auto_rename' => False,
                ),
            );

            if (!$this->options || !is_array($this->options)) {
                if (add_option($this->option_name, $options, '', 'yes')) {
                    $this->options = get_option($this->option_name);
                }
            } else {
                # 确保 opt 结构存在，兼容旧版本升级或数据损坏
                $need_update = false;
                if (!isset($this->options['opt']) || !is_array($this->options['opt'])) {
                    $this->options['opt'] = array('auto_rename' => False);
                    $need_update = true;
                }
                if (!isset($this->options['opt']['auto_rename'])) {
                    $this->options['opt']['auto_rename'] = False;
                    $need_update = true;
                }
                if ($need_update) {
                    update_option($this->option_name, $this->options);
                }
            }

            if ( isset($this->options['backup_url_path']) && $this->options['backup_url_path'] != '' ) {
                update_option('upload_url_path', $this->options['backup_url_path']);
                // 理论上来说，更新完upload_url_path后，这里的option的backup_url_path还需要修改为'';
                // 但因为时机上目前只有激活与禁用2种，因此就由禁用时直接赋值，这里减少一次更新。
                // 后续出现多种场景判断再考虑。
            }

        }

        public function restore_options () {
            if (!is_array($this->options)) {
                $this->options = get_option($this->option_name);
            }
            if (is_array($this->options)) {
                $this->options['backup_url_path'] = get_option('upload_url_path');
                if (update_option($this->option_name, $this->options)) {  // 此处修改的参数不影响对象存储实例
                    $this->options = get_option($this->option_name);
                }
            }
            update_option('upload_url_path', '');
        }

        /**
         * 此函数处理上传的key，用于支持 对象存储子目录
         * @param $key
         * @param $upload_url_path
         * @return string
         */
        private function key_handler($key, $upload_url_path){
            # 参数2 为了减少option的获取次数
            $url_parse = wp_parse_url($upload_url_path);
            # 约定url不要以/结尾，减少判断条件
            if (is_array($url_parse) && array_key_exists('path', $url_parse) && $url_parse['path'] !== '') {
                if ( substr($key, 0, 1) == '/' ) {
                    $key = $url_parse['path'] . $key;
                } else {
                    $key = $url_parse['path'] . '/' . $key;
                }
            }
            # $url_parse['path'] 以/开头，在七牛环境下不能以/开头，所以需要处理掉
            return ltrim($key, '/');
        }

        /**
         * 删除本地文件
         * @param $file_path : 文件路径
         * @return bool
         */
        public function delete_local_file($file_path) {
            try {
                if (!@file_exists($file_path)) {  # 文件不存在
                    return TRUE;
                }
                if (!@unlink($file_path)) { # 删除文件
                    return FALSE;
                }
                return TRUE;
            } catch (Exception $ex) {
                return FALSE;
            }
        }

        /**
         * 上传图片及缩略图
         * @param $metadata: 附件元数据
         * @return array $metadata: 附件元数据
         * 官方的钩子文档上写了可以添加 $attachment_id 参数，但实际测试过程中部分wp接收到不存在的参数时会报错，上传失败，返回报错为“HTTP错误”
         */
        public function upload_and_thumbs( $metadata ) {
            if (isset( $metadata['file'] )) {
                # 1.先上传主图
                $attachment_key = $metadata['file'];  // 远程key路径, 此路径不是以/开头
                $attachment_local_path = $this->wp_upload_dir['basedir'] . '/' . $attachment_key;  # 在本地的存储路径
                $this->_file_upload($attachment_key, $attachment_local_path);  # 调用上传函数
            }

            # 如果存在缩略图则上传缩略图
            if (isset($metadata['sizes']) && count($metadata['sizes']) > 0) {
                foreach ($metadata['sizes'] as $val) {
                    $attachment_thumbs_key = dirname($metadata['file']) . '/' . $val['file'];  // 生成object 的 key
                    $attachment_thumbs_local_path = $this->wp_upload_dir['basedir'] . '/' . $attachment_thumbs_key;  // 本地存储路径
                    $this->_file_upload($attachment_thumbs_key, $attachment_thumbs_local_path);  //调用上传函数
                }
            }

            return $metadata;
        }

        /**
         * @param array  $upload {
         *     Array of upload data.
         *
         *     @type string $file Filename of the newly-uploaded file.
         *     @type string $url  URL of the uploaded file.
         *     @type string $type File type.
         * @return array  $upload
         */
        public function upload_attachments ($upload) {
            $mime_types       = get_allowed_mime_types();
            $image_mime_types = array(
                // Image formats.
                $mime_types['jpg|jpeg|jpe'],
                $mime_types['gif'],
                $mime_types['png'],
                $mime_types['bmp'],
                $mime_types['tiff|tif'],
                $mime_types['ico'],
            );
            if ( ! in_array( $upload['type'], $image_mime_types ) ) {
                $key        = str_replace( $this->wp_upload_dir['basedir'] . '/', '', $upload['file'] );
                $local_path = $upload['file'];
                $this->_file_upload( $key, $local_path);
            }

            return $upload;
        }

        public function save_image_editor_file($override){
            add_filter( 'wp_update_attachment_metadata', array($this,'image_editor_file_save' ));
            return $override;
        }

        public function image_editor_file_save( $metadata ){
            $metadata = $this->upload_and_thumbs($metadata);
            remove_filter( 'wp_update_attachment_metadata', array($this, 'image_editor_file_save') );
            return $metadata;
        }

        /**
         * Filters the result when generating a unique file name.
         *
         * @since 4.5.0
         *
         * @param string        $filename                 Unique file name.

         * @return string New filename, if given wasn't unique
         *
         * 参数 $ext 在官方钩子文档中可以使用，部分 WP 版本因为多了这个参数就会报错。 返回“HTTP错误”
         */
        public function unique_filename( $filename ) {
            $ext = '.' . pathinfo( $filename, PATHINFO_EXTENSION );
            $number = '';

            while ( $this->remote_key_exist( $filename ) ) {
                $new_number = (int) $number + 1;
                if ( '' == "$number$ext" ) {
                    $filename = "$filename-" . $new_number;
                } else {
                    $filename = str_replace( array( "-$number$ext", "$number$ext" ), '-' . $new_number . $ext, $filename );
                }
                $number = $new_number;
            }
            return $filename;
        }

        public function sanitize_file_name_handler( $filename ){
            if (isset($this->options['opt']['auto_rename']) && $this->options['opt']['auto_rename']) {
                return date("YmdHis") . "" . mt_rand(100, 999) . "." . pathinfo($filename, PATHINFO_EXTENSION);
            } else {
                return $filename;
            }
        }

        /** 根据提交数据进行缩略图设置修改与备份。 (暂时取消在这一步对插件参数更新的步骤，留到后面一起进行更新)
         * @param $options
         * @param $set_thumb
         * @return mixed
         */
        private function set_thumbsize_handler($options, $set_thumb){
            if($set_thumb) {
                $options['opt']['thumbsize'] = array(
                    'thumbnail_size_w' => get_option('thumbnail_size_w'),
                    'thumbnail_size_h' => get_option('thumbnail_size_h'),
                    'medium_size_w'    => get_option('medium_size_w'),
                    'medium_size_h'    => get_option('medium_size_h'),
                    'large_size_w'     => get_option('large_size_w'),
                    'large_size_h'     => get_option('large_size_h'),
                    'medium_large_size_w' => get_option('medium_large_size_w'),
                    'medium_large_size_h' => get_option('medium_large_size_h'),
                );
                update_option('thumbnail_size_w', 0);
                update_option('thumbnail_size_h', 0);
                update_option('medium_size_w', 0);
                update_option('medium_size_h', 0);
                update_option('large_size_w', 0);
                update_option('large_size_h', 0);
                update_option('medium_large_size_w', 0);
                update_option('medium_large_size_h', 0);
            } else {
                if(isset($options['opt']['thumbsize'])) {
                    update_option('thumbnail_size_w', $options['opt']['thumbsize']['thumbnail_size_w']);
                    update_option('thumbnail_size_h', $options['opt']['thumbsize']['thumbnail_size_h']);
                    update_option('medium_size_w', $options['opt']['thumbsize']['medium_size_w']);
                    update_option('medium_size_h', $options['opt']['thumbsize']['medium_size_h']);
                    update_option('large_size_w', $options['opt']['thumbsize']['large_size_w']);
                    update_option('large_size_h', $options['opt']['thumbsize']['large_size_h']);
                    update_option('medium_large_size_w', $options['opt']['thumbsize']['medium_large_size_w']);
                    update_option('medium_large_size_h', $options['opt']['thumbsize']['medium_large_size_h']);
                    unset($options['opt']['thumbsize']);
                }
            }
            return $options;
        }

        // 在插件列表页添加设置按钮
        public function setting_plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/index.php')) {
                $links[] = '<a href="admin.php?page=' . $this->base_folder . '/index.php">设置</a>';
            }
            return $links;
        }

        // 在导航栏“设置”中添加条目
        public function admin_menu_setting() {
            add_options_page($this->page_title, $this->menu_title, $this->capability, __FILE__, array($this, 'setting_page'));
        }

        /**
         *  插件设置页面
         */
        public function setting_page() {
            // 如果当前用户权限不足
            if (!current_user_can( $this->capability )) wp_die('Insufficient privileges!');

            $this->options = get_option($this->option_name);
            if ($this->options && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce']) && !empty($_POST)) {
                if($_POST['type'] == 'info_set') {
                    if (!isset($this->options['opt']) || !is_array($this->options['opt'])) {
                        $this->options['opt'] = array();
                    }
                    $this->options['no_local_file'] = isset($_POST['no_local_file']);
                    $this->options['bucket'] = isset($_POST['bucket']) ? sanitize_text_field(trim(stripslashes($_POST['bucket']))) : '';
                    $this->options['endpoint'] = isset($_POST['endpoint']) ? sanitize_text_field(trim(stripslashes($_POST['endpoint']))) : '';
                    $this->options['UCLOUD_PUBLIC_KEY'] = isset($_POST['UCLOUD_PUBLIC_KEY']) ? sanitize_text_field(trim(stripslashes($_POST['UCLOUD_PUBLIC_KEY']))) : '';
                    $this->options['UCLOUD_PRIVATE_KEY'] = isset($_POST['UCLOUD_PRIVATE_KEY']) ? sanitize_text_field(trim(stripslashes($_POST['UCLOUD_PRIVATE_KEY']))) : '';
                    $this->options['opt']['auto_rename'] = isset($_POST['auto_rename']);

                    $this->options = $this->set_thumbsize_handler($this->options, isset($_POST['disable_thumb']) );

                    update_option('upload_url_path', esc_url_raw(trim(stripslashes($_POST['upload_url_path']))));
                    update_option($this->option_name, $this->options);
                    $this->object_storage = new UFileStorageObjectApi($this->options);
                    # 原本想做update_option判断，但内容不改变时返回值为0，会当作失败处理，从业务逻辑上不合理。
                    ?>
                        <div class="notice notice-success settings-error is-dismissible"><p><?php echo($this->setting_notices['update_success']); ?></p></div>
                    <?php
                }
            }
            require_once('setting.php');
        }
    }


    global $WPUFile;
    $WPUFile = new WPUFile();
}
