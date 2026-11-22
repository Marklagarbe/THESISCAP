<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\Milestone;
use App\Models\Notice;
use App\Models\Phase;
use App\Models\ProgressUpdate;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $ago = fn (int $days, int $hours = 0) => now()->subDays($days)->subHours($hours);

        /* ---------- Departments, permissions, settings ---------- */
        $depts = [];
        foreach ([
            'BSIT' => 'BS Information Technology',
            'BSHM' => 'BS Hospitality Management',
            'BSBA' => 'BS Business Administration',
            'BSED' => 'BS Education',
            'BSCRIM' => 'BS Criminology',
        ] as $code => $name) {
            $depts[$code] = Department::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        foreach (config('thesiscap.default_permissions') as $role => $perms) {
            foreach ($perms as $perm) {
                RolePermission::firstOrCreate(['role' => $role, 'permission' => $perm]);
            }
        }
        foreach (array_keys(config('thesiscap.permissions')) as $perm) {
            RolePermission::firstOrCreate(['role' => 'admin', 'permission' => $perm]);
        }

        foreach ([
            'site_name' => 'ThesisCap', 'school_name' => 'Your University', 'academic_year' => '2026-2027',
            'allow_registration' => '1', 'max_upload_mb' => '20',
        ] as $k => $v) {
            if (Setting::where('key', $k)->doesntExist()) {
                Setting::put($k, $v);
            }
        }

        /* ---------- Phases and milestones (due dates are relative to today) ---------- */
        $base = now()->startOfDay();
        $plan = [
            ['Proposal', 'Topic and title approval', [['Topic approval', -50], ['Title defense', -38]]],
            ['Chapters 1 to 3', 'Introduction, literature and methodology', [['Chapter 1: Introduction', -26], ['Chapter 2: Review of Related Literature', -12], ['Chapter 3: Methodology', 5]]],
            ['Pre-Oral Defense', 'Panel review of the proposal', [['Pre-oral defense', 20]]],
            ['Development and Results', 'Build, gather data and analyze', [['Development / data gathering', 45], ['Chapter 4: Results and Discussion', 65], ['Chapter 5: Summary and Conclusion', 80]]],
            ['Final Defense', 'Final manuscript and defense', [['Final manuscript', 95], ['Final defense', 110], ['Hardbound submission', 125]]],
        ];
        $pos = 1;
        foreach ($plan as $i => [$name, $desc, $items]) {
            $phase = Phase::create(['name' => $name, 'description' => $desc, 'position' => $i + 1]);
            foreach ($items as [$title, $offset]) {
                Milestone::create(['phase_id' => $phase->id, 'title' => $title, 'position' => $pos++, 'due_date' => $base->copy()->addDays($offset)]);
            }
        }
        $milestones = Milestone::orderBy('position')->get();

        /* ---------- Users (password for all: "password") ---------- */
        $mk = fn ($name, $email, $role, $dept = null, $sn = null) => User::create([
            'name' => $name, 'email' => $email, 'password' => 'password', 'role' => $role,
            'department_id' => $dept ? $depts[$dept]->id : null, 'student_no' => $sn, 'created_at' => $ago(70), 'updated_at' => $ago(70),
        ]);

        $mk('System Administrator', 'admin@thesiscap.test', 'admin');
        $coord = $mk('Carmela Villanueva', 'coordinator@thesiscap.test', 'coordinator');
        $instructor = $mk('Prof. Maria Santos', 'instructor@thesiscap.test', 'instructor', 'BSIT');
        $adviser = $mk('Dr. Jose Ramirez', 'adviser@thesiscap.test', 'adviser', 'BSIT');
        $leader = $mk('Laira Mendoza', 'leader@thesiscap.test', 'leader', 'BSIT', '2022-0101');
        $member = $mk('Paolo Reyes', 'member@thesiscap.test', 'student', 'BSIT', '2022-0102');
        $member2 = $mk('Angela Cruz', 'member2@thesiscap.test', 'student', 'BSIT', '2022-0103');

        $instructorHm = $mk('Prof. Elena Garcia', 'instructor.hm@thesiscap.test', 'instructor', 'BSHM');
        $adviserHm = $mk('Dr. Ramon Tan', 'adviser.hm@thesiscap.test', 'adviser', 'BSHM');
        $leaderHm = $mk('Kristine Lim', 'leader.hm@thesiscap.test', 'leader', 'BSHM', '2022-0201');
        $memberHm = $mk('Marco Dizon', 'member.hm@thesiscap.test', 'student', 'BSHM', '2022-0202');

        $leaderBa = $mk('Jericho Bautista', 'leader.ba@thesiscap.test', 'leader', 'BSBA', '2022-0301');

        /* ---------- Helpers ---------- */
        // Creates a project with its milestone rows. $states: index => [status, approvedDaysAgo|null, approver|null]
        $makeProject = function (array $data, array $memberIds, array $states) use ($milestones) {
            $project = Project::create($data + ['status' => 'ongoing']);
            $project->members()->sync($memberIds);
            $rows = [];
            foreach ($milestones as $i => $m) {
                [$status, $daysAgo, $approver] = $states[$i] ?? ['pending', null, null];
                $rows[$i] = ProjectMilestone::create([
                    'project_id' => $project->id, 'milestone_id' => $m->id, 'due_date' => $m->due_date, 'status' => $status,
                    'approved_by' => $status === 'approved' ? $approver : null,
                    'approved_at' => $status === 'approved' ? now()->subDays($daysAgo) : null,
                ]);
            }

            return [$project, $rows];
        };

        // A submission version plus its feedback trail. $trail: [[user, kind, body, daysAgo], ...]
        $submit = function (Project $project, $author, array $data, int $daysAgo, array $trail = []) {
            $s = Submission::create($data + [
                'project_id' => $project->id, 'user_id' => $author->id, 'created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo),
            ]);
            foreach ($trail as [$by, $kind, $body, $d]) {
                Feedback::create(['submission_id' => $s->id, 'user_id' => $by->id, 'kind' => $kind, 'body' => $body, 'created_at' => now()->subDays($d), 'updated_at' => now()->subDays($d)]);
            }

            return $s;
        };

        $makeTask = function (Project $project, array $data, $createdBy, int $createdDaysAgo, array $history) {
            $t = Task::create($data + ['project_id' => $project->id, 'created_by' => $createdBy->id, 'created_at' => now()->subDays($createdDaysAgo), 'updated_at' => now()]);
            foreach ($history as [$by, $action, $detail, $d]) {
                TaskActivity::create(['task_id' => $t->id, 'user_id' => $by->id, 'action' => $action, 'detail' => $detail, 'created_at' => now()->subDays($d), 'updated_at' => now()->subDays($d)]);
            }

            return $t;
        };

        /* =====================================================================
           SAMPLE PROCESS: group SagipAlert (BSIT), registered 62 days ago.
           Shows registration, assignment, revisions, approvals, tasks,
           progress updates, a delayed milestone and notifications.
           ===================================================================== */
        [$p1, $pm1] = $makeProject([
            'title' => 'SagipAlert: Emergency Incident Reporting and Dispatch System',
            'abstract' => 'A web-based system for reporting and dispatching emergency incidents to responders in Minglanilla, Cebu.',
            'type' => 'capstone', 'department_id' => $depts['BSIT']->id, 'leader_id' => $leader->id,
            'instructor_id' => $instructor->id, 'adviser_id' => $adviser->id, 'due_date' => now()->addDays(125),
            'start_date' => $ago(62), 'created_at' => $ago(62), 'updated_at' => $ago(62),
        ], [$leader->id, $member->id, $member2->id], [
            0 => ['approved', 52, $adviser->id],
            1 => ['approved', 40, $adviser->id],
            2 => ['approved', 27, $adviser->id],
            3 => ['submitted', null, null],   // Chapter 2 is past its due date: shows as delayed
            4 => ['in_progress', null, null],
        ]);

        // Topic approval: two versions, revision requested then approved
        $submit($p1, $leader, ['type' => 'requirement', 'title' => 'Topic proposal form', 'version' => 1, 'status' => 'revision_requested', 'original_name' => 'topic-proposal-v1.pdf', 'project_milestone_id' => $pm1[0]->id, 'notes' => 'First draft of the topic.'], 58, [
            [$instructor, 'revision_request', 'Narrow the scope to Minglanilla only and name the target responders.', 57],
        ]);
        $submit($p1, $leader, ['type' => 'requirement', 'title' => 'Topic proposal form', 'version' => 2, 'status' => 'approved', 'original_name' => 'topic-proposal-v2.pdf', 'project_milestone_id' => $pm1[0]->id, 'notes' => 'Scope now limited to Minglanilla.'], 55, [
            [$adviser, 'approval', 'Approved. Proceed to the title defense.', 52],
        ]);
        $submit($p1, $leader, ['type' => 'requirement', 'title' => 'Title defense slides', 'version' => 1, 'status' => 'approved', 'original_name' => 'title-defense.pptx', 'project_milestone_id' => $pm1[1]->id], 41, [
            [$instructor, 'comment', 'Good defense. The panel liked the dispatch map idea.', 40],
            [$adviser, 'approval', 'Title approved by the panel.', 40],
        ]);

        // Chapter 1: revised once, then approved
        $submit($p1, $leader, ['type' => 'chapter', 'chapter_no' => 1, 'title' => 'Chapter 1: Introduction', 'version' => 1, 'status' => 'revision_requested', 'original_name' => 'chapter1-v1.docx', 'project_milestone_id' => $pm1[2]->id], 34, [
            [$instructor, 'revision_request', 'Add the statement of the problem and the scope and limitations.', 33],
            [$adviser, 'recommendation', 'Cite the 2023 local disaster report to support the problem.', 33],
        ]);
        $submit($p1, $leader, ['type' => 'chapter', 'chapter_no' => 1, 'title' => 'Chapter 1: Introduction', 'version' => 2, 'status' => 'approved', 'original_name' => 'chapter1-v2.docx', 'project_milestone_id' => $pm1[2]->id, 'notes' => 'Revised based on feedback.'], 29, [
            [$adviser, 'approval', 'Well written. Approved.', 27],
        ]);

        // Chapter 2: first version needed revisions, second version is waiting for review
        $submit($p1, $leader, ['type' => 'chapter', 'chapter_no' => 2, 'title' => 'Chapter 2: Review of Related Literature', 'version' => 1, 'status' => 'revision_requested', 'original_name' => 'chapter2-v1.docx', 'project_milestone_id' => $pm1[3]->id], 16, [
            [$instructor, 'revision_request', 'Add at least five more local studies and fix the citation format.', 14],
            [$adviser, 'recommendation', 'Group the literature by theme instead of by author.', 14],
        ]);
        $ch2 = $submit($p1, $leader, ['type' => 'chapter', 'chapter_no' => 2, 'title' => 'Chapter 2: Review of Related Literature', 'version' => 2, 'status' => 'submitted', 'original_name' => 'chapter2-v2.docx', 'project_milestone_id' => $pm1[3]->id, 'notes' => 'Added 7 studies and regrouped by theme.'], 3);
        $pm1[3]->update(['remarks' => 'Revised version submitted, waiting for the adviser.']);

        $submit($p1, $member, ['type' => 'requirement', 'title' => 'Adviser conforme form', 'version' => 1, 'status' => 'submitted', 'original_name' => 'conforme.pdf'], 2);
        $submit($p1, $leader, ['type' => 'manuscript', 'title' => 'Manuscript draft', 'version' => 1, 'status' => 'under_review', 'original_name' => 'manuscript-draft.pdf'], 1, [
            [$instructor, 'under_review', null, 1],
        ]);

        // Tasks, each with its own activity history
        $makeTask($p1, ['title' => 'Interview barangay responders', 'description' => 'Interview three responders about how reports reach them today.', 'assigned_to' => $member->id, 'due_date' => $ago(36)->toDateString(), 'status' => 'done', 'project_milestone_id' => $pm1[1]->id], $leader, 45, [
            [$leader, 'created', 'Assigned to Paolo Reyes', 45],
            [$member, 'status', 'To do to In progress', 41],
            [$member, 'update', 'Interviewed 2 of 3 responders. The third is available on Friday.', 38],
            [$member, 'status', 'In progress to Done', 35],
        ]);
        $makeTask($p1, ['title' => 'Write Chapter 1 background', 'assigned_to' => $leader->id, 'due_date' => $ago(31)->toDateString(), 'status' => 'done', 'project_milestone_id' => $pm1[2]->id], $leader, 40, [
            [$leader, 'created', 'Assigned to Laira Mendoza', 40],
            [$leader, 'status', 'To do to In progress', 38],
            [$leader, 'status', 'In progress to Done', 30],
        ]);
        $makeTask($p1, ['title' => 'Collect related literature (10 studies)', 'assigned_to' => $member2->id, 'due_date' => $ago(18)->toDateString(), 'status' => 'done', 'project_milestone_id' => $pm1[3]->id], $leader, 28, [
            [$leader, 'created', 'Assigned to Angela Cruz', 28],
            [$member2, 'status', 'To do to In progress', 26],
            [$member2, 'update', 'Found 6 studies so far, shared the links in the group chat.', 22],
            [$member2, 'status', 'In progress to Done', 19],
        ]);
        $t4 = $makeTask($p1, ['title' => 'Revise Chapter 2 citations', 'description' => 'Apply adviser comments and fix the APA references.', 'assigned_to' => $member->id, 'due_date' => now()->addDays(3)->toDateString(), 'status' => 'in_progress', 'project_milestone_id' => $pm1[3]->id], $leader, 13, [
            [$leader, 'created', 'Assigned to Paolo Reyes', 13],
            [$member, 'status', 'To do to In progress', 12],
            [$member, 'update', 'Fixed the APA format on 12 of 20 references.', 4],
        ]);
        $t5 = $makeTask($p1, ['title' => 'Draft survey questionnaire', 'assigned_to' => $member2->id, 'due_date' => now()->subDays(2)->toDateString(), 'status' => 'todo', 'project_milestone_id' => $pm1[4]->id], $leader, 6, [
            [$leader, 'created', 'Assigned to Angela Cruz', 6],
        ]);
        $makeTask($p1, ['title' => 'Prepare Chapter 3 methodology outline', 'assigned_to' => $leader->id, 'due_date' => now()->addDays(6)->toDateString(), 'status' => 'in_progress', 'project_milestone_id' => $pm1[4]->id], $leader, 5, [
            [$leader, 'created', 'Assigned to Laira Mendoza', 5],
            [$leader, 'status', 'To do to In progress', 3],
        ]);

        // Progress updates posted by the group
        foreach ([
            [$leader, null, 'We registered the project and invited Paolo and Angela. Meeting with the adviser on Monday.', 61],
            [$member, null, 'Interview questions were approved by Prof. Santos.', 42],
            [$member2, null, 'Collected 6 related studies. Need 4 more for Chapter 2.', 22],
            [$member, $t4, 'Fixed the APA format on 12 of 20 references.', 4],
            [$leader, null, 'Chapter 2 version 2 is submitted. Starting the Chapter 3 outline.', 3],
        ] as [$by, $task, $body, $d]) {
            ProgressUpdate::create(['project_id' => $p1->id, 'user_id' => $by->id, 'task_id' => $task?->id, 'body' => $body, 'created_at' => $ago($d), 'updated_at' => $ago($d)]);
        }

        // Notifications the group has received along the way
        foreach ([
            [$leader, 'Project assignment updated', $p1->title, 61, true],
            [$leader, 'Revision requested', 'Chapter 2: Review of Related Literature', 14, true],
            [$leader, 'New submission to review', 'Manuscript draft is under review', 1, false],
            [$leader, 'Milestone overdue', 'Chapter 2: Review of Related Literature', 0, false],
            [$member, 'New task assigned', 'Revise Chapter 2 citations', 13, true],
            [$member2, 'New task assigned', 'Draft survey questionnaire', 6, false],
            [$instructor, 'New submission to review', 'Chapter 2: Review of Related Literature (version 2)', 3, false],
            [$adviser, 'Milestone ready for approval', 'Chapter 2: Review of Related Literature', 3, false],
        ] as [$to, $title, $body, $d, $read]) {
            Notice::create(['user_id' => $to->id, 'title' => $title, 'body' => $body, 'url' => route('projects.show', $p1), 'read_at' => $read ? $ago($d) : null, 'created_at' => $ago($d), 'updated_at' => $ago($d)]);
        }

        /* ---------- Second group: BSHM thesis, earlier in the process ---------- */
        [$p2, $pm2] = $makeProject([
            'title' => 'Guest Experience Mapping in Boutique Hotels',
            'abstract' => 'A study on guest touchpoints and satisfaction in boutique hotels.',
            'type' => 'thesis', 'department_id' => $depts['BSHM']->id, 'leader_id' => $leaderHm->id,
            'instructor_id' => $instructorHm->id, 'adviser_id' => $adviserHm->id, 'due_date' => now()->addDays(125),
            'start_date' => $ago(55), 'created_at' => $ago(55), 'updated_at' => $ago(55),
        ], [$leaderHm->id, $memberHm->id], [
            0 => ['approved', 44, $adviserHm->id],
            1 => ['approved', 33, $adviserHm->id],
            2 => ['submitted', null, null],
        ]);
        $submit($p2, $leaderHm, ['type' => 'requirement', 'title' => 'Topic proposal form', 'version' => 1, 'status' => 'approved', 'original_name' => 'topic.pdf', 'project_milestone_id' => $pm2[0]->id], 48, [[$adviserHm, 'approval', 'Approved.', 44]]);
        $submit($p2, $leaderHm, ['type' => 'chapter', 'chapter_no' => 1, 'title' => 'Chapter 1: Introduction', 'version' => 1, 'status' => 'submitted', 'original_name' => 'chapter1.docx', 'project_milestone_id' => $pm2[2]->id], 5, [
            [$instructorHm, 'comment', 'Received. I will send comments by Friday.', 4],
        ]);
        $makeTask($p2, ['title' => 'Interview three boutique hotel managers', 'assigned_to' => $memberHm->id, 'due_date' => now()->addDays(9)->toDateString(), 'status' => 'todo'], $leaderHm, 4, [
            [$leaderHm, 'created', 'Assigned to Marco Dizon', 4],
        ]);
        ProgressUpdate::create(['project_id' => $p2->id, 'user_id' => $leaderHm->id, 'body' => 'Chapter 1 submitted. Preparing the interview guide.', 'created_at' => $ago(5), 'updated_at' => $ago(5)]);

        /* ---------- Third group: BSBA thesis, just registered, no staff yet ---------- */
        [$p3] = $makeProject([
            'title' => 'Digital Marketing Adoption Among Local Sari-Sari Stores',
            'abstract' => 'Examines how micro retailers adopt online marketing.',
            'type' => 'thesis', 'department_id' => $depts['BSBA']->id, 'leader_id' => $leaderBa->id,
            'due_date' => now()->addDays(125), 'start_date' => $ago(4), 'created_at' => $ago(4), 'updated_at' => $ago(4),
        ], [$leaderBa->id], [0 => ['in_progress', null, null]]);
        Notice::create(['user_id' => $coord->id, 'title' => 'New project registered', 'body' => $p3->title, 'url' => route('projects.show', $p3), 'created_at' => $ago(4), 'updated_at' => $ago(4)]);

        /* ---------- Activity log history ---------- */
        foreach ([
            [$leader, 'register', 'Created account as Student Project Manager', 63],
            [$leader, 'project.create', 'Registered project: '.$p1->title, 62],
            [$coord, 'project.assign', 'Updated instructor/adviser for: '.$p1->title, 61],
            [$leader, 'project.member_add', 'Paolo Reyes added to '.$p1->title, 61],
            [$leader, 'submission.create', 'Submitted: Topic proposal form', 58],
            [$instructor, 'submission.revision_request', 'Revision requested: Topic proposal form', 57],
            [$adviser, 'submission.approval', 'Submission approved: Topic proposal form', 52],
            [$leader, 'submission.create', 'Submitted: Chapter 1: Introduction', 34],
            [$adviser, 'submission.approval', 'Submission approved: Chapter 1: Introduction', 27],
            [$leader, 'submission.create', 'Submitted: Chapter 2: Review of Related Literature', 3],
            [$member, 'progress.update', 'Posted a progress update on '.$p1->title, 4],
            [$leaderBa, 'project.create', 'Registered project: '.$p3->title, 4],
        ] as [$who, $action, $desc, $d]) {
            ActivityLog::create(['user_id' => $who->id, 'action' => $action, 'description' => $desc, 'ip' => '127.0.0.1', 'created_at' => $ago($d), 'updated_at' => $ago($d)]);
        }

        Model::reguard();

        $this->call(RegisteredIdSeeder::class);
    }
}
