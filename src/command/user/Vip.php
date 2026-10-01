<?php

namespace app\zz\command\user;

use zz\model\UserM;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Vip extends Command
{
    protected function configure()
    {
        $this->setName('vip')->setDescription('vip');
    }

    protected function execute(Input $input, Output $output)
    {
        UserM::where('is_vip', 1)->chunk(100, function ($users) use ($output) {
            foreach ($users as $user) {
                if ($user['vip_expire'] < time()) {
                    $output->writeln($user['id']);
                    $user->save([
                        'is_vip'     => 0,
                        'vip_expire' => null
                    ]);
                }
            }
        });
        $output->writeln('over');
    }
}
