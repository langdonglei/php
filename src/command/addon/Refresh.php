<?php

namespace langdonglei\command\addon;

use langdonglei\File;
use think\addons\Service;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Refresh extends Command
{
    protected function configure()
    {
        $this->setName('v:refresh');
    }

    protected function execute(Input $input, Output $output)
    {
        $root    = getcwd();
        $name    = 'v';
        $info    = get_addon_info($name);
        $version = $info['version'];

        File::zip($root . '/addons/' . $name, $root . '/v/' . $name . '-' . $version . '.zip');

        // 冲突
        var_dump(Service::getGlobalFiles($name, true));

        // 复制 assets
        if (is_dir($root . '/addons/' . $name . '/assets')) {
            copydirs($root . '/addons/' . $name . '/assets', $root . '/public/assets/addons/' . $name);
        }

        // 复制 application 和 public
        foreach (['application', 'public'] as $item) {
            if (is_dir($root . '/addons/' . $name . '/' . $item)) {
                copydirs($root . '/addons/' . $name . '/' . $item, $root . '/' . $item);
            }
        }

        Service::refresh();
    }
}