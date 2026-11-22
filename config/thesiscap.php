<?php

return [
    // Shows the demo accounts dropdown on the login page.
    'show_demo' => env('SHOW_DEMO_ACCOUNTS', true),
    'demo_password' => 'password',

    'demo_accounts' => [
        ['label' => 'Administrator', 'email' => 'admin@thesiscap.test'],
        ['label' => 'Project Coordinator', 'email' => 'coordinator@thesiscap.test'],
        ['label' => 'Instructor (BSIT)', 'email' => 'instructor@thesiscap.test'],
        ['label' => 'Adviser (BSIT)', 'email' => 'adviser@thesiscap.test'],
        ['label' => 'Student Project Manager (BSIT)', 'email' => 'leader@thesiscap.test'],
        ['label' => 'Student Member (BSIT)', 'email' => 'member@thesiscap.test'],
        ['label' => 'Student Project Manager (BSHM)', 'email' => 'leader.hm@thesiscap.test'],
    ],

    // Permission key => label, used by the Roles & Permissions page.
    'permissions' => [
        'projects.create' => 'Register a thesis / capstone project',
        'projects.view_all' => 'View every project',
        'tasks.manage' => 'Create, assign and delete group tasks',
        'tasks.update' => 'Update status of tasks',
        'submissions.upload' => 'Upload requirements, chapters and manuscripts',
        'submissions.review' => 'Review submissions, give feedback, request revisions',
        'milestones.approve' => 'Approve milestones',
        'phases.manage' => 'Manage project phases and milestones',
        'assignments.manage' => 'Assign instructors and advisers',
        'reports.view' => 'Generate accomplishment reports',
        'users.manage' => 'Manage user accounts',
        'roles.manage' => 'Manage roles and permissions',
        'settings.manage' => 'Manage system settings',
        'records.manage' => 'Manage system records',
        'reports.system' => 'View system-wide reports',
        'logs.view' => 'View activity logs',
    ],

    // Default permissions per role (admin always has everything).
    'default_permissions' => [
        'student' => ['tasks.update', 'submissions.upload'],
        'leader' => ['projects.create', 'tasks.manage', 'tasks.update', 'submissions.upload'],
        'instructor' => ['submissions.review'],
        'adviser' => ['submissions.review', 'milestones.approve'],
        'coordinator' => ['projects.view_all', 'phases.manage', 'assignments.manage', 'reports.view'],
    ],
];
