<?php

use Illuminate\Database\Seeder;

class UserTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('user_type')->where('name','Super-admin')->first()) {
            DB::table('user_type')->insert([
                'code' => 'SAD',
                'name' => 'Super-admin',
                'description' => 'Administrador total del sistema.',
            ]);
        }
        if (!DB::table('user_type')->where('name','Admin')->first()) {
            DB::table('user_type')->insert([
                'code' => 'AD',
                'name' => 'Admin',
                'description' => 'Administrador parcial del sistema.',
            ]);
        }
        if (!DB::table('user_type')->where('name','Cajero')->first()) {
            DB::table('user_type')->insert([
                'code' => 'CA',
                'name' => 'Cajero',
                'description' => 'Usuario Cajero, tiene limitadas opciones.',
            ]);
        }
    }
}
