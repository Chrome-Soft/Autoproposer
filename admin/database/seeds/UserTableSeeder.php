<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = \App\User::where('email', 'admin@admin.hu')->first();
        if (!$admin) {
            factory(\App\User::class)->create([
                'name'      => 'Admin Admin',
                'email'     => env('ADMIN_EMAIL', 'admin@admin.hu'),
                'password'  => bcrypt('admin')
            ]);
        }
    }
}
