<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SimpleCMS\Framework\Models\Dict;

class DictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->getList();
        foreach ($data as $sql) {
            if ($dict = Dict::create(['name' => $sql['name'], 'code' => $sql['code']])) {
                $dict->items()->createMany($sql['children']);
            }
        }
    }

    public function getList()
    {
        return [
            [
                'name' => '菜单类型',
                'code' => 'menu_type',
                'children' => [
                        [
                            'name' => '后台菜单',
                            'content' => 1,
                        ],
                        [
                            'name' => '前台菜单',
                            'content' => 2,
                        ]
                    ]
            ],
            [
                'name' => '角色类型',
                'code' => 'role_type',
                'children' => [
                        [
                            'name' => '后台角色',
                            'content' => 1,
                        ],
                        [
                            'name' => '前台角色',
                            'content' => 2,
                        ]
                    ]
            ]
        ];
    }
}
