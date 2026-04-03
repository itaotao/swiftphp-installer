# SwiftPHP 框架使用文档

## 1. 安装脚手架

### 1.1 系统要求
- PHP 7.4 或更高版本
- Composer 包管理器
- Windows/Linux/MacOS 操作系统

### 1.2 安装步骤
1. **克隆或下载** SwiftPHP 脚手架到本地
   ```bash
   git clone https://github.com/itaotao/swiftphp-installer.git
   cd swiftphp-installer
   ```

2. **安装依赖**
   ```bash
   composer install
   ```

3. **验证安装**
   ```bash
   php swiftphp --version
   ```

## 2. 创建新项目

### 2.1 基本创建命令
```bash
# 在当前目录创建项目
php swiftphp new myproject

# 指定目标目录
php swiftphp new myproject --dir /path/to/directory
```

### 2.2 项目初始化
创建项目后，进入项目目录并安装依赖：
```bash
cd myproject
composer install
```

## 3. 项目结构

```
myproject/
├── app/              # 应用目录
│   ├── controller/   # 控制器
│   ├── model/        # 模型
│   ├── middleware/   # 中间件
│   ├── validate/     # 验证器
│   ├── lang/         # 语言文件
│   └── common.php    # 公共函数
├── config/           # 配置文件
├── public/           # 公共目录
│   └── index.php     # Web 入口
├── route/            # 路由配置
├── runtime/          # 运行时目录
├── vendor/           # 依赖包
├── .env              # 环境配置
├── start.php         # 服务器启动文件
├── start.bat         # Windows 启动脚本
└── composer.json     # Composer 配置
```

## 4. 核心功能使用

### 4.1 启动服务器
```bash
# 方法 1：直接运行
php start.php

# 方法 2：使用 Windows 脚本
start.bat

# 方法 3：使用 Composer 脚本
composer run start
```

服务器默认运行在 `http://localhost:8787`

### 4.2 路由配置
编辑 `route/route.php` 文件：

```php
<?php

use SwiftPHP\Routing\Router;

$router = Router::getInstance();

// GET 请求
$router->get('/', function() {
    return Response::json([
        'code' => 200,
        'message' => 'Hello SwiftPHP!'
    ]);
});

// 带参数的路由
$router->get('/user/{id}', function($id) {
    return Response::json([
        'code' => 200,
        'user_id' => $id
    ]);
});

// POST 请求
$router->post('/api/login', function() {
    $username = Request::post('username');
    $password = Request::post('password');
    
    // 处理登录逻辑
    return Response::json([
        'code' => 200,
        'message' => 'Login successful'
    ]);
});
```

### 4.3 控制器
创建控制器文件 `app/controller/IndexController.php`：

```php
<?php

namespace App\Controller;

use SwiftPHP\Controller\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return $this->json([
            'code' => 200,
            'message' => 'Welcome to SwiftPHP!'
        ]);
    }
    
    public function user($id)
    {
        return $this->json([
            'code' => 200,
            'user_id' => $id
        ]);
    }
}
```

在路由中使用控制器：
```php
$router->get('/controller', 'App\Controller\IndexController@index');
$router->get('/controller/user/{id}', 'App\Controller\IndexController@user');
```

### 4.4 模型
创建模型文件 `app/model/User.php`：

```php
<?php

namespace App\Model;

use SwiftPHP\Model\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'password'];
}
```

使用模型：
```php
// 创建用户
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('123456', PASSWORD_DEFAULT)
]);

// 查询用户
$user = User::find(1);
$users = User::where('status', 1)->get();

// 更新用户
$user->name = 'Jane Doe';
$user->save();

// 删除用户
$user->delete();
```

### 4.5 中间件
创建中间件文件 `app/middleware/Auth.php`：

```php
<?php

namespace App\Middleware;

use SwiftPHP\Middleware\Middleware;
use SwiftPHP\Request\Request;
use SwiftPHP\Response\Response;

class Auth extends Middleware
{
    public function handle(Request $request, callable $next)
    {
        // 验证用户是否登录
        if (!isset($_SESSION['user_id'])) {
            return Response::json([
                'code' => 401,
                'message' => 'Unauthorized'
            ]);
        }
        
        return $next($request);
    }
}
```

注册中间件到 `config/middleware.php`：
```php
<?php

return [
    'global' => [
        // 全局中间件
    ],
    'route' => [
        'auth' => App\Middleware\Auth::class
    ]
];
```

在路由中使用中间件：
```php
$router->get('/dashboard', function() {
    return Response::json([
        'code' => 200,
        'message' => 'Welcome to dashboard'
    ]);
})->middleware('auth');
```

## 5. 配置管理

### 5.1 环境配置
编辑 `.env` 文件：

```env
# 应用配置
APP_NAME=SwiftPHP
APP_ENV=development
APP_DEBUG=true

# 数据库配置
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swiftphp
DB_USERNAME=root
DB_PASSWORD=

# 服务器配置
SERVER_PORT=8787
SERVER_PROCESSES=1
```

### 5.2 配置文件
编辑 `config` 目录下的配置文件：

- **app.php** - 应用配置
- **database.php** - 数据库配置
- **server.php** - 服务器配置
- **route.php** - 路由配置
- **middleware.php** - 中间件配置

## 6. 部署指南

### 6.1 生产环境部署

1. **配置环境变量**
   修改 `.env` 文件，设置 `APP_ENV=production` 和 `APP_DEBUG=false`

2. **优化自动加载**
   ```bash
   composer dump-autoload --optimize
   ```

3. **配置 Web 服务器**
   
   **Nginx 配置**：
   ```nginx
   server {
       listen 80;
       server_name example.com;
       root /path/to/project/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php$is_args$args;
       }
       
       location ~ \.php$ {
           fastcgi_pass 127.0.0.1:9000;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

   **Apache 配置**：
   项目已包含 `.htaccess` 文件，确保 Apache 启用了 `mod_rewrite` 模块

4. **启动 Workerman 服务器**
   ```bash
   # 后台启动
   php start.php start -d
   
   # 停止服务器
   php start.php stop
   
   # 重启服务器
   php start.php restart
   ```

### 6.2 监控和维护

- **查看服务器状态**：`php start.php status`
- **查看服务器日志**：`runtime/log` 目录
- **热更新**：修改代码后，Workerman 会自动 reload（开发环境）

## 7. 开发工具

### 7.1 脚手架命令
```bash
# 查看所有命令
php swiftphp list

# 创建控制器
php swiftphp make:controller UserController

# 创建模型
php swiftphp make:model User

# 创建中间件
php swiftphp make:middleware Auth

# 创建验证器
php swiftphp make:validate UserValidate
```

### 7.2 调试工具
- **调试模式**：设置 `APP_DEBUG=true` 查看详细错误信息
- **日志系统**：使用 `Log::info('message')` 记录日志
- **容器**：使用 `Container::get('service')` 获取服务

## 8. 常见问题

### 8.1 端口被占用
```bash
# 查看端口占用
netstat -ano | findstr :8787

# 或者修改配置文件 config/server.php 中的端口
```

### 8.2 依赖安装失败
```bash
# 清除缓存
composer clear-cache

# 重新安装
composer install
```

### 8.3 权限问题
确保 `runtime` 目录有写入权限：
```bash
chmod -R 755 runtime/
```

## 9. 性能优化

1. **启用 OPcache**：在 php.ini 中配置
2. **使用 Redis 缓存**：修改 `config/cache.php` 配置
3. **数据库优化**：使用索引，避免全表扫描
4. **代码优化**：减少不必要的计算和IO操作

## 10. 安全建议

1. **输入验证**：使用 `Validate` 类验证用户输入
2. **密码加密**：使用 `password_hash()` 加密密码
3. **CSRF 保护**：使用 CSRF token
4. **XSS 防护**：对输出进行适当转义
5. **权限控制**：实现基于角色的访问控制

---

## 总结

SwiftPHP 是一个轻量级的 PHP 框架，结合了 ThinkPHP 的易用性和 Workerman 的高性能。它提供了完整的 MVC 架构、路由系统、中间件、数据库 ORM 等功能，适合构建各种 Web 应用和 API 服务。

通过本文档的指导，您应该能够快速上手 SwiftPHP 框架，创建和部署您的项目。如果您有任何问题或建议，欢迎提交 issue 或贡献代码。

**官方仓库**：https://github.com/itaotao/swiftphp-core