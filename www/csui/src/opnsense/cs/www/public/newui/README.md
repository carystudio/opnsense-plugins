# 第一章：目录结构
> svn 路径 svn://192.168.10.120/web/carystudio

~~~ 
CS_WEB_UI\WEB
├─adm  
├─dist （实际部署使用的目录。此目录通过命令生成）
├─data （此目录用于模拟后台返回的json数据）
├─firewall
├─internet
├─plugin (插件目录)
├─static （静态资源）
│  ├─css  （自定义样式）
│  ├─images （图片资源）
│  └─js
│    ├─config.js（项目的配置文件，）
│    ├─common.js （项目共有的函数库）
│    ├─layout.js （项目的布局文件。配置：头部，菜单，底部,[vue组件的形式提供]）
│    ├─topicurl.js （接口文档树）
│    ├─main.js （项目的主js。所有的配置，设置通过引用他才生效）
│    └─language.js （语言文件）
├─wireless
├─index.html  
├─package.json (node包管理配置)
├─gulpfile.js  (自动化构建工具配置)
└─conf.json (jsdoc文档配置)
~~~

# 第二章：环境部署
> **生产环境部署的目录使用的是 `dist` 目录下的文件。**  
> 系统环境需要安装：`nodejs` 、`npm`( 推荐使用国内的 `cnpm` )    
> 项目环境需要安装：`del`、 `gulp`、 `gulp-minify-css`、 `gulp-notify`、 `gulp-rename`、 `gulp-uglify`、 `ink-docstrap`、     
> >如果以上环境都使用不了就手动进行管理吧。

### 第一步：安装`nodejs`
到官网下载 `nodejs` 并安装：[http://nodejs.cn](http://nodejs.cn)
> 推荐使用 `XXX LTS` 稳定版。

### 第二步：初始化项目
1.命令：`npm install`
> 该命令会执行安装node所需要的模块

2.命令：`npm run run`
> 生成生产目录 `dist` 及对应的文件。也就是实际项目使用的目录

> 希望在代码编译的时候加入这些命令或者执行这些命令。生成

#### 其他命令：
1. 命令：`npm run build`
> 创建文档目录 `doc` 和生产目录 `dist` 及对应的文件。也就是实际项目使用的目录
2. 命令：`npm run doc`
> 产生文档目录 `doc` 及对应的文件，生成文档。

### 温馨提示：
1. 这些命令当前代码环境的根目录下执行的。
2. 新前端界面使用了自动化编程（这里只能说用了半自动化）。如果安装不了nodejs那就只能手动去删减文件、代码里面注释、压缩文件、等等。

# 第三章：文档说明
> 其他开发文档和详细api手册

- [前端开发文档说明](tutorial-frontend.html)
- [前端公有函数库文档1](cs.html)
- [前端配置及选项文档](global.html)
- [主题（接口）文档](uiPost.html)