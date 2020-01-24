<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = DB::table('user_type')->where('code','SDA')->first();
        if (!DB::table('users')->where('user_name','Couponbook')->first()) {
            DB::table('users')->insert([
                'user_name' => 'Couponbook',
                'id_company' => 1,
                'id_user_type' => $role->id,
                'email' => 'couponbook@gmail.com',
                'password' => bcrypt('admin'),
            ]);
        }
    	    
    }
}
