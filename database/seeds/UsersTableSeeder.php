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
        for($i=1;$i<=10;$i++){
    		$users[] = ['name' => 'User'.$i,'email' => 'user'.$i.'@promolta.com','password' => bcrypt('password')];
    	}
        DB::table('users')->insert($users);
    }
}
