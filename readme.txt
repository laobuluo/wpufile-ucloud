=== 优刻得UCloud对象存储插件 ===

Contributors: laobuluo
Donate link: https://www.laojiang.me/donate/
Tags:WordPress对象存储,WordPress加速,WordPress UCloud对象存储, Ufile
Requires at least: 5.3
Tested up to: 6.9.1
Stable tag: 3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

优刻得UCloud对象存储插件（WPUFile），基于UCloud UFile与WordPress实现静态资源到对象存储中。提高网站项目的访问速度，以及静态资源的安全存储功能。目前UCLOUD对象存储提供每月20GB流量，适合入门用户使用。公众号： 老蒋朋友圈。

## 主要功能

* 1、下载和激活【WPUFile】插件后，配置UCLOUD对象存储参数。
* 2、可以选择只存储到UCLOUD对象存储空间、也可以本地网站也同时备份。
* 3、选择UCLOUD对象存储可以是免费域名，也可以自定义域名。
* 4、WPUFile插件更多详细介绍和安装：<a href="https://www.laojiang.me/contact/" target="_blank" >https://www.laojiang.me/contact/</a>

## 网站支持

* [主机评价网](https://www.zhujipingjia.com/ "主机评价网")

* [乐在云](https://www.lezaiyun.com/ "乐在云")

* 欢迎加入插件和站长微信公众号：老蒋朋友圈（公众号）

== Installation ==

* 1、把WPUFile文件夹上传到/wp-content/plugins/目录下<br />
* 2、在后台插件列表中激活WPUFile<br />
* 3、在左侧【UCLOUD对象存储设置】菜单中输入UCLOUD存储空间账户信息。<br />

== Frequently Asked Questions ==

* 1.当发现插件出错时，开启调试获取错误信息。
* 2.我们可以选择备份对象存储或者本地同时备份。
* 3.如果已有网站使用WPUFile，插件调试没有问题之后，需要将原有本地静态资源上传到UCLOUD存储中，然后修改数据库原有固定静态文件链接路径。、
* 4.如果不熟悉使用这类插件的用户，一定要先备份，确保错误设置导致网站故障。

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 3.0 =
* 版本升级至 3.0
* 移除数据万象功能
* 移除一键替换功能
* 移动端隐藏右侧关注公众号
* 插件仅支持 PHP 7.4 及以上版本
* 修复逻辑和兼容性问题（opt 结构、key_handler、restore_options 等）
* 优化 PHP 版本检测，激活时阻止不兼容版本

= 2.6 =
* 调整UI以及兼容WP6.2测试

= 2.5 =
* 修复PHP8.1+安装报错问题

= 2.4 =
* 兼容最新wordpress5.9.3版本

= 2.3 =
* 兼容最新wordpress5.7版本

= 2.2 =
* 兼容最新wordpress5.6版本

= 2.1 =
* 重构全部代码 执行效率更高
* 兼容最新wordpress版本
* 解决编辑图片兼容问题
* 新增部分功能 禁止缩略图、随机命名、图片编辑等

= 1.0 =
* 完成WPUFile插件基础架设；
* 解决上传图片比较慢问题
* 解决默认SDK无法上传图片问题
* 审核插件遇到点问题，重新提交审核




== Upgrade Notice ==

= 3.0 =
建议所有用户升级。此版本需要 PHP 7.4 或更高版本，移除了数据万象和一键替换功能，并修复了多项兼容性问题。 