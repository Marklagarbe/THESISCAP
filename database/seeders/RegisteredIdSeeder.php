<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\RegisteredId;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegisteredIdSeeder extends Seeder
{
    /** Run alone with: php artisan db:seed --class=RegisteredIdSeeder */
    public function run(): void
    {
        // Make sure the departments exist (safe to run many times).
        foreach (['BSIT' => 'BS Information Technology', 'BSHM' => 'BS Hospitality Management', 'BSBA' => 'BS Business Administration', 'BSED' => 'BS Education', 'BSCRIM' => 'BS Criminology'] as $code => $name) {
            Department::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $dept = Department::pluck('id', 'code');

        // IDs already belonging to the demo accounts (marked as used).
        $demo = [
            'instructor@thesiscap.test' => ['TE-3346776', 'instructor'],
            'instructor.hm@thesiscap.test' => ['TE-3346777', 'instructor'],
            'adviser@thesiscap.test' => ['AD-5500123', 'adviser'],
            'adviser.hm@thesiscap.test' => ['AD-5500124', 'adviser'],
            'coordinator@thesiscap.test' => ['CO-7700001', 'coordinator'],
            'leader@thesiscap.test' => ['ST-1000001', 'student'],
            'member@thesiscap.test' => ['ST-1000002', 'student'],
            'member2@thesiscap.test' => ['ST-1000003', 'student'],
            'leader.hm@thesiscap.test' => ['ST-1000004', 'student'],
            'member.hm@thesiscap.test' => ['ST-1000005', 'student'],
            'leader.ba@thesiscap.test' => ['ST-1000006', 'student'],
        ];

        foreach ($demo as $email => [$idNumber, $type]) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }
            RegisteredId::updateOrCreate(['id_number' => $idNumber], [
                'type' => $type, 'full_name' => $user->name, 'department_id' => $user->department_id,
                'used_by' => $user->id, 'used_at' => now(),
            ]);
            $user->update(['student_no' => $idNumber]);
        }

        // Unused IDs you can use to test the register page.
        foreach ([
            ['ST-1000101', 'student', 'Maria Clara Santos', 'BSIT'],
            ['ST-1000102', 'student', 'Juan Dela Cruz', 'BSHM'],
            ['ST-1000103', 'student', 'Ana Reyes', 'BSBA'],
            ['TE-3346800', 'instructor', 'Prof. Antonio Lopez', 'BSIT'],
            ['TE-3346801', 'instructor', null, null],
            ['AD-5500200', 'adviser', 'Dr. Liza Mercado', 'BSBA'],
            ['CO-7700100', 'coordinator', null, null],
        ] as [$idNumber, $type, $name, $code]) {
            $row = RegisteredId::firstOrNew(['id_number' => $idNumber]);
            if (! $row->used_by) { // never touch IDs that already belong to an account
                $row->fill([
                    'type' => $type, 'full_name' => $name, 'department_id' => $code ? ($dept[$code] ?? null) : null,
                ])->save();
            }
        }
    }
}
