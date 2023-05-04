<?php

namespace addons\backgrounda;

use think\Addons;
use think\View;

class BackgroundA extends Addons
{
    public function adminLoginInit()
    {
        $addon_name = $this->getName();
        View::instance()->assign('background', "/assets/addons/$addon_name/background.jpg");
    }

    public function install(): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function enable(): bool
    {
        return true;
    }

    public function disable(): bool
    {
        return true;
    }
}
