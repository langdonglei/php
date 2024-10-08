<?php

namespace langdonglei;

use app\common\library\Auth;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\Token;
use think\Cache;
use think\Config;
use think\Db;
use think\Exception;
use Throwable;
use vv\User;

class FastAdmin
{
    const DIR_SRC        = __DIR__ . '/../addon/';
    const DIR_PACKAGE    = __DIR__ . '/../../../runtime/zz/addon/';
    const DIR_OUTPUT     = __DIR__ . '/../../../addons/';
    const DIR_MOVE_ASSET = __DIR__ . '/../../../public/assets/addons/';

    const ADDON_JS    = __DIR__ . '/../../../public/assets/js/addons.js';
    const ADDON_EXTRA = __DIR__ . '/../../../application/extra/addons.php';

    public static function register(): array
    {
        $param = ThinkPHP::validate([
            'type'             => 'require|in:username',
            'username'         => 'requireIf:type,username',
            'password'         => 'requireIf:type,username',
            'password_confirm' => 'requireIf:type,username|confirm:password',
        ]);
        $auth  = Auth::instance();
        if ($auth->register($param['username'], $param['password'])) {
            return [
                'token' => $auth->getToken(),
                'user'  => $auth->getUserinfo()
            ];
        } else {
            throw new \Exception($auth->getError());
        }
    }

    public static function password(): array
    {
        $param = ThinkPHP::validate([
            'type'     => 'require|in:root,mobile,email',
            'password' => 'require|length:10',
            'code'     => 'requireIf:type,mobile|requireIf:type,email',
            'mobile'   => 'requireIf:type,mobile|mobile',
            'email'    => 'requireIf:type,email|email'
        ]);
        if ($param['type'] == 'mobile') {
            $user = User::where('mobile', $param['mobile'])->findOrFail();
            if (!Sms::check($param['mobile'], $param['code'], 'resetpwd')) {
                throw new \Exception('验证码错误');
            }
            Sms::flush($param['mobile'], 'resetpwd');
        }
        if ($param['type'] == 'email') {
            $user = User::where('email', $param['email'])->findOrFail();
            if (!Ems::check($param['email'], $param['code'], 'resetpwd')) {
                throw new \Exception('验证码错误');
            }
            Ems::flush($param['email'], 'resetpwd');
        }
        if (!isset($user)) {
            $user = User::find(1);
        }
        $auth = Auth::instance();
        $auth->direct($user['id']);
        $auth->changepwd($param['password'], '', true);
        return [
            'token' => $auth->getToken(),
            'user'  => $auth->getUserinfo()
        ];
    }

    public static function login(): array
    {
        $param = ThinkPHP::validate([
            'type'     => 'require|in:username',
            'username' => 'requireIf:type,username',
            'password' => 'requireIf:type,username'
        ]);
        $user  = User::where('username', $param['username'])->find();
        if (!$user) {
            throw new \Exception('登录失败');
        }
        if ($user['password'] != FastAdmin::encrypt($param['password'], $user['salt'])) {
            throw new \Exception('登录失败');
        }
        $auth = Auth::instance();
        $auth->direct($user['id']);
        return [
            'token' => $auth->getToken(),
            'user'  => $auth->getUserinfo()
        ];
    }

    public static function logout()
    {
        return Auth::instance()->logout();
    }

    public static function generate_token($user_id, $expire = 0)
    {
        $auth = Auth::instance();
        $auth->keeptime($expire);
        $auth->direct($user_id);
        return $auth->getToken();
    }

    public static function auth(): array
    {
        $auth = Auth::instance();
        if ($user_id = input('user_id')) {
            $auth->direct($user_id);
            $r['user_id'] = $auth->getToken();
        }
        if ($username = input('username')) {
            if ($user = Db::table('fa_user')->where('username', $username)->find()) {
                $auth->direct($user['id']);
                $r['username'] = $auth->getToken();
            } else {
                $r['username'] = '用户不存在';
            }
        }
        if ($mobile = input('mobile/s')) {
            if ($user = Db::table('fa_user')->where('mobile', $mobile)->find()) {
                $auth->direct($user['id']);
                $r['mobile'] = $auth->getToken();
            } else {
                $r['mobile'] = '用户不存在';
            }
        }
        $config = Config::get('token');
        if ($token = input('token')) {
            $r['token'] = Db::table('fa_user')->field('id,username,mobile,nickname')->find(Db::table('fa_user_token')->where('token', hash_hmac($config['hashalgo'], $token, $config['key']))->value('user_id'));
        } else {
            $r['token'] = '';
        }
        if ($current = request()->header('token')) {
            $r['current'] = Db::table('fa_user')->field('id,username,mobile,nickname')->find(Db::table('fa_user_token')->where('token', hash_hmac($config['hashalgo'], $current, $config['key']))->value('user_id'));
        } else {
            $r['current'] = '';
        }
        return $r;
    }

    public static function where($where, $from, $to): array
    {
        $where = (new ReflectionFunction($where))->getStaticVariables()['where'];
        $ret   = [];
        foreach ($where as $item) {
            if (str_contains($item[0], $from[0])) {
                $key         = array_search($item[2], $from);
                $ret[$to[0]] = ['exp', new Expression($to[$key])];
            } else {
                $ret[$item[0]] = [$item[1], $item[2]];
            }
        }
        return $ret;
    }

    public static function package($id)
    {
        $addon_src = self::DIR_SRC . $id;
        if (!class_exists('ZipArchive')) {
            throw new Exception('缺少扩展 zipArchive');
        }
        $info_file = $addon_src . DIRECTORY_SEPARATOR . 'info.ini';
        if (!is_file($info_file)) {
            throw new Exception('插件不存在 或 插件没有info.ini');
        }
        $info_arr = parse_ini_file($info_file);
        if (!$info_arr) {
            throw new Exception('无法解析插件info.ini的数据');
        }
        $info_name = $info_arr['name'] ?? '';
        if (!$info_name || !preg_match("/^[a-z]+$/i", $info_name) || $info_name != $id) {
            throw new Exception('插件信息中的id和传入的id不一致');
        }
        $info_version = $info_arr['version'] ?? '';
        if (!$info_version || !preg_match("/^\d+\.\d+\.\d+$/i", $info_version)) {
            throw new Exception('插件信息中的版本错误');
        }
        return ZipS::zip($addon_src, self::DIR_PACKAGE . $id . '-' . $info_version . '.zip');
    }

    public static function install($id)
    {
        Db::startTrans();
        try {
            $addon_output = self::DIR_OUTPUT . $id;
            $zip          = self::package($id);
            ZipS::unzip($zip, $addon_output);
            $class = get_addon_class($id);
            if (class_exists($class)) {
                $class = new $class();
                $class->install();
                if (method_exists($class, "enable")) {
                    $class->enable();
                }
            }
            $file_sql = $addon_output . '/install.sql';
            if (is_file($file_sql)) {
                $lines = file($file_sql);
                $sql   = '';
                foreach ($lines as $line) {
                    if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                        continue;
                    }
                    $sql .= $line;
                    if (substr(trim($line), -1, 1) == ';') {
                        Db::execute($sql);
                        $sql = '';
                    }
                }
            }

            FileS::cpdir($addon_output . '/assets', self::DIR_MOVE_ASSET . $id);

            Db::commit();
        } catch (Throwable $e) {
            Db::rollback();
            self::uninstall($id);
            throw $e;
        } finally {
            self::refresh();
        }
    }

    public static function uninstall($id)
    {
        $config = Config::get('addons');

        foreach ($config['hooks'] as $key => $item) {
            if (count($item) == 1) {
                $config['hooks'] = [];
            } else {
                foreach ($item as $k => $v) {
                    if ($v == $id) {
                        unset($config['hooks'][$key][$k]);
                        break;
                    }
                }
            }
        }

        FileS::write_array(self::ADDON_EXTRA, $config);

        FileS::rmdir(self::DIR_OUTPUT . $id);
        FileS::rmdir(self::DIR_MOVE_ASSET . $id);
    }

    private static function refresh()
    {
        $js = [];
        foreach (get_addon_list() as $id => $info) {
            $file = self::DIR_OUTPUT . $id . '/bootstrap.js';
            if ($info['state'] && is_file($file)) {
                $js[] = file_get_contents($file);
            }
        }
        $handle = fopen(self::ADDON_JS, 'w');
        fwrite($handle, str_replace("{__JS__}", implode(PHP_EOL, $js), <<< EOF
define([], function () {
    {__JS__}
});
EOF
        ));
        fclose($handle);

        Cache::rm("addons");
        Cache::rm("hooks");

        $config = get_addon_autoload_config(true);
        if (!$config['autoload']) {
            FileS::write_array(self::ADDON_EXTRA, $config);
        }
    }

    public static function encrypt($password, $salt): string
    {
        return md5(md5($password) . $salt);
    }

    public static function encrypt_token($token)
    {
        $config = Config::get('token');
        $algo   = $config['hashalgo'];
        $key    = $config['key'];
        return hash_hmac($algo, $token, $key);
    }

    public static function get_user_id_by_token($token)
    {
        return Db::table('fa_user_token')
            ->where('token', FastAdmin::encrypt_token($token))
            ->where(function ($query) {
                $query->whereOr('expiretime', 0)
                    ->whereOr('expiretime', null)
                    ->whereOr('expiretime', '>', time());
            })->value('user_id');
    }

    public static function get_user_by_token($token)
    {
        return Db::table('fa_user')->where('id', FastAdmin::get_user_id_by_token($token))->find();
    }

    public static function who()
    {
        $token = request()->header('token');
        if (!$token) {
            throw new Exception('头部缺少token');
        }

        $config          = Config::get('token');
        $token_encrypted = hash_hmac($config['hashalgo'], $token, $config['key']);
        $user_id         = Db::table('fa_user_token')->where([
            'token'      => $token_encrypted,
            'expiretime' => ['>', time()]
        ])->value('user_id');
        if (!$user_id) {
            throw new Exception('无效的token');
        }

        return Db::table('fa_user')->where([
            'id' => $user_id
        ])->field([
            'id',
            'username',
            'mobile',
            'nickname'
        ])->findOrFail();
    }

    public static function area_for_uni_data_picker()
    {
        $data = Db::table('area')->column('id as value, pid, name as text, level');
        $r    = [];
        foreach ($data as $province) {
            if ($province['level'] === 1) {
                unset($province['level'], $province['pid']);
                $r[$province['value']] = $province;
                foreach ($data as $city) {
                    if ($city['level'] === 2 && $city['pid'] === $province['value']) {
                        unset($city['level'], $city['pid']);
                        $r[$province['value']]['children'][$city['value']] = $city;
                        foreach ($data as $area) {
                            if ($area['level'] === 3 && $area['pid'] === $city['value']) {
                                unset($area['level'], $area['pid']);
                                $r[$province['value']]['children'][$city['value']]['children'][$area['value']] = $area;
                            }
                        }
                    }
                }
            }
        }
        array_walk($r, function (&$province) {
            $province['children'] = array_values($province['children']);
            array_walk($province['children'], function (&$city) {
                $city['children'] = array_values($city['children']);
            });
        });
        return array_values($r);
    }

    public static function html($toolbar = 'refresh'): string
    {
        return \think\View::instance()->display(<<< EOD
<div class="panel panel-default panel-intro">
    {:build_heading()}
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div class="toolbar">
                        {:build_toolbar('$toolbar')}
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"></table>
                </div>
            </div>
        </div>
    </div>
</div>
EOD
        );
    }
}
