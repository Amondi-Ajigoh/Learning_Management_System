<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view users',
            'manage users',

            'view courses',
            'create courses',
            'update courses',
            'delete courses',
            'publish courses',

            'view course content',
            'create course content',
            'update course content',
            'delete course content',

            'manage instructors',

            'view enrollments',
            'create enrollments',
            'update enrollments',
            'cancel enrollments',

            'view progress',
            'update progress',

            'view quizzes',
            'create quizzes',
            'update quizzes',
            'delete quizzes',
            'attempt quizzes',
            'grade quizzes',

            'view assessments',
            'create assessments',
            'update assessments',
            'delete assessments',
            'submit assessments',
            'grade assessments',

            'view certificates',
            'issue certificates',
            'verify certificates',

            'view files',
            'upload files',
            'delete files',

            'view notifications',
            'manage notifications',

            'view analytics',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('admin', 'web');
        $instructor = Role::findOrCreate('instructor', 'web');
        $student = Role::findOrCreate('student', 'web');

        $admin->syncPermissions(Permission::all());

        $instructor->syncPermissions([
            'view courses',
            'create courses',
            'update courses',
            'delete courses',
            'publish courses',

            'view course content',
            'create course content',
            'update course content',
            'delete course content',

            'manage instructors',

            'view enrollments',
            'view progress',
            'update progress',

            'view quizzes',
            'create quizzes',
            'update quizzes',
            'delete quizzes',
            'grade quizzes',

            'view assessments',
            'create assessments',
            'update assessments',
            'delete assessments',
            'grade assessments',

            'view certificates',
            'issue certificates',

            'view files',
            'upload files',
            'delete files',

            'view notifications',
            'manage notifications',

            'view analytics',
        ]);

        $student->syncPermissions([
            'view courses',
            'view course content',

            'view enrollments',
            'create enrollments',
            'cancel enrollments',

            'view progress',
            'update progress',

            'view quizzes',
            'attempt quizzes',

            'view assessments',
            'submit assessments',

            'view certificates',
            'verify certificates',

            'view files',
            'upload files',

            'view notifications',
        ]);
    }
}
