<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@schoolorp.com',
            'password' => bcrypt('password'),
            'user_type' => 'super_admin',
            'status' => 'active',
        ]);

        // 2. Create Plans
        Plan::create(['name' => 'Starter', 'slug' => 'starter', 'monthly_price' => 1000, 'yearly_price' => 10000, 'max_students' => 300, 'max_branches' => 1]);
        Plan::create(['name' => 'Growth', 'slug' => 'growth', 'monthly_price' => 2500, 'yearly_price' => 25000, 'max_students' => 1000, 'max_branches' => 3]);
        Plan::create(['name' => 'Premium', 'slug' => 'premium', 'monthly_price' => 6000, 'yearly_price' => 60000, 'max_students' => 3000, 'max_branches' => 10]);

        // 3. Create Demo School
        $school = School::create([
            'name' => 'Demo Public School',
            'code' => 'SCH001',
            'email' => 'demo@school.com',
            'phone' => '9876543210',
            'address' => '123 Main Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
            'board' => 'CBSE',
            'status' => 'active',
        ]);

        // 4. Create Branch
        $branch = Branch::create([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'BR001',
            'is_main_branch' => true,
        ]);

        // 5. Academic Year
        $ay = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-04-01',
            'end_date' => '2026-03-31',
            'is_current' => true,
        ]);

        // 6. Classes
        $classes = ['Nursery', 'LKG', 'UKG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'];
        foreach ($classes as $i => $name) {
            SchoolClass::create([
                'school_id' => $school->id,
                'name' => $name,
                'sort_order' => $i + 1,
            ]);
        }

        $this->command->info('✅ Core data seeded successfully!');
    }
}
