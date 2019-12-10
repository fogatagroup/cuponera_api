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
        if (!DB::table('users')->where('user_name','Couponbook')->first()) {
            DB::table('users')->insert([
                'user_name' => 'Couponbook',
                'id_company' => 1,
                'id_user_type' => 1,
                'email' => 'couponbook@gmail.com',
                'password' => bcrypt('admin'),
            ]);
        }
    	    
    }
}
