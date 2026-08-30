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
            if (!Dict::where('code', $sql['code'])->first()) {
                $this->addDict($sql);
            }
        }
    }

    private function addDict($data): void
    {
        $dict = Dict::create(['name' => $data['name'], 'code' => $data['code']]);
        $dict->items()->createMany($this->convertChildren($data['children']));
    }


    private function convertChildren(array $data): array
    {
        $result = [];
        foreach ($data as $value => $name) {
            if ($name !== null) {
                $result[] = [
                    'name' => $name,
                    'content' => $value
                ];
            }
        }
        return $result;
    }

    public function getList()
    {
        return [
            [
                'name' => '菜单类型',
                'code' => 'menu_type',
                'children' => [null, '后台菜单', '前台菜单']
            ],
            [
                'name' => '角色类型',
                'code' => 'role_type',
                'children' => [null, '后台角色', '前台角色']
            ]
        ];
    }
}
