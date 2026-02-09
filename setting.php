<link rel='stylesheet'  href='<?php echo plugin_dir_url( __FILE__ );?>layui/css/layui.css' />
<link rel='stylesheet'  href='<?php echo plugin_dir_url( __FILE__ );?>layui/css/laobuluo.css'/>
<script src='<?php echo plugin_dir_url( __FILE__ );?>layui/layui.js'></script>

<style>
    .site-text {
        border: 1px solid #ddd;
        padding: 30px 0 20px 0;
        margin: 30px auto;
    }

    .site-block .layui-form-label {
        width: 100%;
        max-width: 100px;
    }

    .site-block .layui-input-block {
        width: 100%;
        max-width: 315px;
        margin-left: 130px;
    }

    .site-block .layui-input-inline {
        width: 90px;
    }

    .laobuluo-wp-hidden {
        position: relative;
    }

    .laobuluo-wp-hidden .laobuluo-wp-eyes {
        padding: 5px;
        position: absolute;
        top: 3px;
        z-index: 999;
        display: none;
        cursor:pointer; 
        background-color: #fff;
    }

    .laobuluo-wp-hidden i {
        font-size: 20px;
        color: #666;
    }

    /* 移动端隐藏右侧关注公众号 */
    @media screen and (max-width: 768px) {
        .laobuluo-wechat-panel {
            display: none !important;
        }
    }
</style>

<!-- nav -->
<div class="container-laobuluo-main">
    <div class="laobuluo-wbs-header" style="margin-bottom: 15px;">
        <div class="laobuluo-wbs-logo"><span class="wbs-span">UCloud对象存储插件</span><span class="wbs-free">Free V3.0</span></div>
        <div class="laobuluo-wbs-btn">
            <a class="layui-btn layui-btn-primary" href="https://www.lezaiyun.com/1101.html" target="_blank"><i class="layui-icon layui-icon-home"></i> 插件主页</a>
            <a class="layui-btn layui-btn-primary" href="https://www.lezaiyun.com/contact/" target="_blank"><i class="layui-icon layui-icon-release"></i> 技术支持</a>
        </div>
    </div>
</div>
<!-- nav -->
<!-- 内容 -->
<div class="container-laobuluo-main">
    <div class="layui-container container-m">
        <div class="layui-row layui-col-space15">
            <!-- 左边 -->
            <div class="layui-col-md9">
                <div class="laobuluo-panel">
                    <div class="laobuluo-controw">
                        <fieldset class="layui-elem-field layui-field-title site-title">
                            <legend><a name="get">UCloud UFile存储设置</a></legend>
                        </fieldset>
                        <div class="site-text site-block">
                            <form class="layui-form wpcosform" action="<?php echo wp_nonce_url('./admin.php?page=' . $this->base_folder . '/index.php'); ?>" name="wpupfile_form" method="post">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">存储空间名称：</label>
                                    <div class="layui-input-block">
                                        <input type="text" class="layui-input" name="bucket" value="<?php echo esc_attr($this->options['bucket']); ?>" placeholder="比如：laobuluo" />
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">所属地域：</label>
                                    <div class="layui-input-block">
                                        <input type="text" class="layui-input" name="endpoint" value="<?php echo esc_attr($this->options['endpoint']); ?>" placeholder="例：.cn-bj.ufileos.com">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">远程URL：</label>
                                    <div class="layui-input-block">
                                        <input type="text" class="layui-input" name="upload_url_path" value="<?php echo esc_url(get_option('upload_url_path')); ?>" placeholder="例：http://laojiang.cn-bj.ufileos.com">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">令牌公钥：</label>
                                    <div class="layui-input-block">
                                        <div class="laobuluo-wp-hidden">
                                            <input type="password" class="layui-input" name="UCLOUD_PUBLIC_KEY" value="<?php echo esc_attr($this->options['UCLOUD_PUBLIC_KEY']); ?>" placeholder="UCLOUD_PUBLIC_KEY">
                                            <span class="laobuluo-wp-eyes"><i class="dashicons dashicons-hidden"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">令牌私钥：</label>
                                    <div class="layui-input-block">
                                        <div class="laobuluo-wp-hidden">
                                            <input type="password" class="layui-input" name="UCLOUD_PRIVATE_KEY" value="<?php echo esc_attr($this->options['UCLOUD_PRIVATE_KEY']); ?>" placeholder="UCLOUD_PRIVATE_KEY">
                                            <span class="laobuluo-wp-eyes"><i class="dashicons dashicons-hidden"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">自动重命名：</label>
                                    <div class="layui-input-inline">
                                        <input type="checkbox" title="开启" name="auto_rename" <?php
                                                                                                if (isset($this->options['opt']['auto_rename']) and $this->options['opt']['auto_rename']) {
                                                                                                    echo 'checked="TRUE"';
                                                                                                }
                                                                                                ?>>
                                    </div>
                                    <div class="layui-form-mid layui-word-aux"><p style="padding-bottom: 5px;">上传文件自动重命名，解决中文文件名或者重复文件名问题></p></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">是否本地保存：</label>
                                    <div class="layui-input-inline">
                                        <input type="checkbox" title="保存" name="no_local_file" <?php
                            if ($this->options['no_local_file']) {
                                echo 'checked="TRUE"';
                            }
					    ?>/>
                                    </div>
                                    <div class="layui-form-mid layui-word-aux"><p style="padding-bottom: 5px;">如果不想同步在服务器中备份静态文件就 "勾选"。</p></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">禁止缩略图：</label>
                                    <div class="layui-input-inline">
                                        <input type="checkbox" title="禁止" name="disable_thumb" <?php
                        if (isset($this->options['opt']['thumbsize'])) {
                            echo 'checked="TRUE"';
                        }
                        ?>
                    />
                                    </div>
                                    <div class="layui-form-mid layui-word-aux"><p style="padding-bottom: 5px;">上传文件自动重命名，解决中文文件名或者重复文件名问题</p></div>
                                </div>
                        </div>


                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <input type="submit" name="submit" value="保存设置" class="layui-btn" />
                            </div>
                        </div>
                        <input type="hidden" name="type" value="info_set">
                        </form>
                    </div>
                  <blockquote class="layui-elem-quote">
                        <p>1. 如果我们有多个网站需要使用WPUFile插件，可以远程URL后面加上不同目录。</p>
                        <p>2. 使用WPUpFile插件分离图片、附件文件，存储在UCloud UFile云存储空间根目录，比如：2019、2018、2017这样的直接目录，不会有wp-content这样目录。</p>
                    </blockquote>
                   
                </div>
            </div>
        <!-- 左边 -->
        <!-- 右边  -->
        <div class="layui-col-md3 laobuluo-wechat-panel">
            <div id="nav">
                
                <div class="laobuluo-panel">
                        <div class="laobuluo-panel-title">关注公众号</div>
                        <div class="laobuluo-code">
                            <img src="<?php echo plugin_dir_url(__FILE__); ?>layui/images/qrcode.png">
                            <p>微信扫码关注 <span class="layui-badge layui-bg-blue">老蒋朋友圈</span> 公众号</p>
                            <p><span class="layui-badge">优先</span> 获取插件更新 和 更多 <span class="layui-badge layui-bg-green">免费插件</span> </p>
                        </div>
                    </div>
                
            </div>
        </div>
        <!-- 右边 end -->
    </div>
</div>
</div>
<!-- footer -->
   <div class="container-laobuluo-main">
	   <div class="layui-container container-m">
		   <div class="layui-row layui-col-space15">
			   <div class="layui-col-md12">
				<div class="laobuluo-footer-code">
					 <span class="codeshow"></span>
				</div>
                <div class="laobuluo-links">
                    <a href="https://www.lezaiyun.com/"  target="_blank">乐在云工作室</a>
                    <a href="https://www.zhujipingjia.com/pianyivps.html" target="_blank">便宜VPS推荐</a>
                    <a href="https://www.zhujipingjia.com/hkcn2.html" target="_blank">香港VPS推荐</a>
                    <a href="hhttps://www.zhujipingjia.com/uscn2gia.html" target="_blank">美国VPS推荐</a>
                </div>
			   </div>
		   </div>
	   </div>
   </div>
   <!-- footer -->
<script>
    layui.use(['jquery', 'form', 'element'], function() {

        var $ = layui.jquery;
        var form = layui.form;

        function menuFixed(id) {
            var obj = document.getElementById(id);
            var _getHeight = obj.offsetTop;
            var _Width = obj.offsetWidth
            window.onscroll = function() {
                changePos(id, _getHeight, _Width);
            }
        }

        function changePos(id, height, width) {
            var obj = document.getElementById(id);
            obj.style.width = width + 'px';
            var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            var _top = scrollTop - height;
            if (_top < 150) {
                var o = _top;
                obj.style.position = 'relative';
                o = o > 0 ? o : 0;
                obj.style.top = o + 'px';

            } else {
                obj.style.position = 'fixed';
                obj.style.top = 50 + 'px';

            }
        }



        $(window).resize(function() {
			if ($(window).width() > 1024) {
        
				menuFixed('nav');
			}
	    })


        var laobueys = $('.laobuluo-wp-hidden')

        laobueys.each(function() {

            var inpu = $(this).find('.layui-input');
            var eyes = $(this).find('.laobuluo-wp-eyes')
            var width = $(this).width() - 35;
            eyes.css('left', width + 'px').show();
            eyes.click(function() {
                if (inpu.attr('type') == "password") {

                    inpu.attr('type', 'text')
                    eyes.html('<i class="dashicons dashicons-visibility"></i>')
                } else {
                    inpu.attr('type', 'password')
                    eyes.html('<i class="dashicons dashicons-hidden"></i>')
                }
            })
        })

    })
</script>