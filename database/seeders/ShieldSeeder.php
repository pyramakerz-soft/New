<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_beginning","view_any_beginning","create_beginning","update_beginning","restore_beginning","restore_any_beginning","replicate_beginning","reorder_beginning","delete_beginning","delete_any_beginning","force_delete_beginning","force_delete_any_beginning","view_benchmark","view_any_benchmark","create_benchmark","update_benchmark","restore_benchmark","restore_any_benchmark","replicate_benchmark","reorder_benchmark","delete_benchmark","delete_any_benchmark","force_delete_benchmark","force_delete_any_benchmark","view_course","view_any_course","create_course","update_course","restore_course","restore_any_course","replicate_course","reorder_course","delete_course","delete_any_course","force_delete_course","force_delete_any_course","view_ending","view_any_ending","create_ending","update_ending","restore_ending","restore_any_ending","replicate_ending","reorder_ending","delete_ending","delete_any_ending","force_delete_ending","force_delete_any_ending","view_game","view_any_game","create_game","update_game","restore_game","restore_any_game","replicate_game","reorder_game","delete_game","delete_any_game","force_delete_game","force_delete_any_game","view_lesson","view_any_lesson","create_lesson","update_lesson","restore_lesson","restore_any_lesson","replicate_lesson","reorder_lesson","delete_lesson","delete_any_lesson","force_delete_lesson","force_delete_any_lesson","view_patient","view_any_patient","create_patient","update_patient","restore_patient","restore_any_patient","replicate_patient","reorder_patient","delete_patient","delete_any_patient","force_delete_patient","force_delete_any_patient","view_presentation","view_any_presentation","create_presentation","update_presentation","restore_presentation","restore_any_presentation","replicate_presentation","reorder_presentation","delete_presentation","delete_any_presentation","force_delete_presentation","force_delete_any_presentation","view_program","view_any_program","create_program","update_program","restore_program","restore_any_program","replicate_program","reorder_program","delete_program","delete_any_program","force_delete_program","force_delete_any_program","view_question","view_any_question","create_question","update_question","restore_question","restore_any_question","replicate_question","reorder_question","delete_question","delete_any_question","force_delete_question","force_delete_any_question","view_role","view_any_role","create_role","update_role","view_school","view_any_school","create_school","update_school","restore_school","restore_any_school","replicate_school","reorder_school","delete_school","delete_any_school","force_delete_school","force_delete_any_school","view_stage","view_any_stage","create_stage","update_stage","restore_stage","restore_any_stage","replicate_stage","reorder_stage","delete_stage","delete_any_stage","force_delete_stage","force_delete_any_stage","view_test","view_any_test","create_test","update_test","restore_test","restore_any_test","replicate_test","reorder_test","delete_test","delete_any_test","force_delete_test","force_delete_any_test","view_unit","view_any_unit","create_unit","update_unit","restore_unit","restore_any_unit","replicate_unit","reorder_unit","delete_unit","delete_any_unit","force_delete_unit","force_delete_any_unit","view_warmup","view_any_warmup","create_warmup","update_warmup","restore_warmup","restore_any_warmup","replicate_warmup","reorder_warmup","delete_warmup","delete_any_warmup","force_delete_warmup","force_delete_any_warmup","page_Dashbaord","widget_PatientTypeOverview","widget_TreatmentsChart","delete_role","delete_any_role"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
