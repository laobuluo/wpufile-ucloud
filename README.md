# 优刻得 UCloud 对象存储插件 (WPUFile)

WordPress 同步附件内容至 UCloud UFile 对象存储，实现网站数据与静态资源分离，提高网站加载速度。

[![WordPress](https://img.shields.io/badge/WordPress-4.5%2B-blue?logo=wordpress)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 功能特性

- **自动同步**：上传的图片、附件自动存储至 UCloud UFile
- **双重备份**：可选择仅存储到云端，或云端 + 本地同时备份
- **自动重命名**：解决中文文件名或重复文件名问题
- **禁止缩略图**：可按需禁用 WordPress 缩略图生成
- **远程删除**：删除附件时同步删除云端文件
- **灵活域名**：支持 UCloud 免费域名或自定义域名

## 环境要求

| 项目 | 要求 |
|------|------|
| WordPress | 4.5.0 及以上 |
| PHP | 7.4 及以上 |
| UCloud UFile | 已开通对象存储服务 |

## 安装

1. 将 `wpufile-ucloud` 文件夹上传至 `/wp-content/plugins/` 目录
2. 在 WordPress 后台 → 插件 → 已安装插件 中激活 WPUFile
3. 在左侧菜单 **设置 → UCloud云存储设置** 中配置存储参数

## 配置说明

在插件设置页面填写以下信息：

| 配置项 | 说明 |
|--------|------|
| 存储空间名称 | UCloud UFile 的 Bucket 名称 |
| 所属地域 | 端点域名，如 `.cn-bj.ufileos.com` |
| 远程 URL | 访问域名，如 `https://xxx.cn-bj.ufileos.com` |
| 令牌公钥 | UCloud 控制台的公钥 (UCLOUD_PUBLIC_KEY) |
| 令牌私钥 | UCloud 控制台的私钥 (UCLOUD_PRIVATE_KEY) |

### 可选配置

- **自动重命名**：上传时自动重命名为时间戳+随机数，避免中文/重复文件名问题
- **是否本地保存**：勾选后仅上传至云端，不保留本地副本
- **禁止缩略图**：勾选后禁用 WordPress 缩略图生成

## 目录结构

```
wpufile-ucloud/
├── index.php          # 插件主入口
├── api.php            # UFile API 封装
├── setting.php        # 设置页面模板
├── uninstall.php      # 卸载脚本
├── readme.txt         # WordPress 插件说明
├── README.md          # 项目说明（本文件）
├── layui/             # 前端 UI 框架
│   ├── css/           # 样式文件
│   ├── images/        # 图片资源
│   └── lay/           # 模块脚本
└── sdk/               # UCloud UFile SDK
    └── v1/ucloud/     # SDK 核心文件
```

## 常见问题

**Q: 插件出错如何排查？**  
A: 开启 WordPress 调试模式，在 `wp-config.php` 中设置 `define('WP_DEBUG', true);` 获取错误信息。

**Q: 已有网站如何迁移？**  
A: 需先将本地 `wp-content/uploads` 中的文件上传至 UFile，再修改数据库中的静态文件链接路径。建议先备份数据库。

**Q: 为什么无法激活？**  
A: 本插件要求 PHP 7.4 及以上版本，请检查服务器 PHP 版本后升级。

## 插件团队和技术支持

[老蒋](https://www.laojiang.me/)（老蒋和他的伙伴们），本着资源共享原则，在运营网站过程中用到的或者是有需要用到的主题、插件资源，有选择的免费分享给广大的网友站长，希望能够帮助到你建站过程中提高效率。

感谢团队成员，以及网友提出的优化工具的建议，才有后续产品的不断迭代适合且满足用户需要。不能确保100%的符合兼容网站，我们也仅能做到在工作之余不断的接近和满足你的需要。

| 类目            | 信息                                                         |
| --------------- | ------------------------------------------------------------ |
| 插件更新地址    | https://www.laojiang.me/7195.html                            |
| 团队成员        | [老蒋](https://www.laojiang.me/)、老赵、[CNJOEL](https://www.rakvps.com/)、木村 |
| 支持网站        | [乐在云](https://www.lezaiyun.com/)、主机评价网              |
| 建站资源推荐    | [便宜VPS推荐](https://www.zhujipingjia.com/pianyivps.html)、[美国VPS推荐](https://www.zhujipingjia.com/uscn2gia.html)、[外贸建站主机](https://www.zhujipingjia.com/wordpress-hosting.html)、[SSL证书推荐](https://www.zhujipingjia.com/two-ssls.html)、[WordPress主机推荐](https://www.zhujipingjia.com/wpblog-host.html) |
| 提交WP官网（F） | https://cn.wordpress.org/plugins/wpufile-ucloud/             |

![](wechat.png)

## 更新日志

### 3.0 (2025)

- 版本升级至 3.0
- 移除数据万象功能
- 移除一键替换功能
- 移动端隐藏右侧关注公众号
- 插件仅支持 PHP 7.4 及以上版本
- 修复逻辑和兼容性问题

### 2.6

- 调整 UI 以及兼容 WP 6.2

### 2.5

- 修复 PHP 8.1+ 安装报错问题

更多历史版本请查看 [readme.txt](readme.txt) 中的 Changelog。

## 许可证

GPLv2 or later - [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)

---

**作者**：老蒋和他的小伙伴  
**公众号**：老蒋朋友圈
