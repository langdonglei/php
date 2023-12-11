<?php

namespace langdonglei\command\addon;

use langdonglei\File;
use think\addons\Service;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class RefreshV extends Command
{
    protected function configure()
    {
        $this->setName('refresh:v');
    }

    protected function execute(Input $input, Output $output)
    {
        $name     = 'v';
        $name_dir = ADDON_PATH . $name . '/';
        $info     = get_addon_info($name);
        $version  = $info['version'];

        $package_dir = ROOT_PATH . 'v/';
        if (!is_dir($package_dir)) {
            @mkdir($package_dir, 0755, true);
        }
        $package = $package_dir . $name . '-' . $version . '.zip';
        if (is_file($package)) {
            @unlink($package);
        }
        File::zip($name_dir, $package);

        // 冲突
        var_dump(Service::getGlobalFiles($name, true));

        // 复制 assets
        $sourceAssetsDir = ADDON_PATH . $name . '/assets/';
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, ROOT_PATH . 'public/assets/addons/' . $name . '/');
        }

        // 复制 application 和 public
        foreach (['application', 'public'] as $item) {
            if (is_dir($name_dir . $item)) {
                copydirs($name_dir . $item, ROOT_PATH . $item);
            }
        }

        Service::refresh();
    }
}