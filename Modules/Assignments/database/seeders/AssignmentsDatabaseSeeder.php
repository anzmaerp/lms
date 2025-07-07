<?php

namespace Modules\Assignments\database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\DB;

class AssignmentsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->setEmailTemplates();
        $this->seedAssignments();
    }

    private function seedAssignments()
    {
        $assignments = [
            //
        ];
    }

    private function setEmailTemplates()
    {

        $emailTemplates = $this->getEmailTemplates();
        $notificationTemplates = $this->getNotificationTemplates();
        foreach ($emailTemplates as $type => $template) {

            foreach (!empty($template['roles']) ? $template['roles'] : [] as $role => $data) {
                EmailTemplate::updateOrCreate([
                    'type' => $type,
                    'title' => $template['title'],
                    'role' => $role,
                    'content' => ['info' => $data['fields']['info']['desc'], 'subject' => $data['fields']['subject']['default'], 'greeting' => $data['fields']['greeting']['default'], 'content' => $data['fields']['content']['default']]
                ]);
            }
        }

        foreach ($notificationTemplates as $type => $template) {

            foreach (!empty($template['roles']) ? $template['roles'] : [] as $role => $data) {
                NotificationTemplate::updateOrCreate([
                    'type' => $type,
                    'title' => $template['title'],
                    'role' => $role,
                    'content' => ['info' => $data['fields']['info']['desc'], 'subject' => $data['fields']['subject']['default'], 'content' => $data['fields']['content']['default']]
                ]);
            }
        }
        
    }

    private function getEmailTemplates()
    {  
        EmailTemplate::whereIn('type', ['assignedAssignment', 'reviewedAssignment', 'generateAssignmentResult'])->forceDelete();
        return [
            'assignedAssignment' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.assigned_assignment_notify'),
                'roles' => [
                    'student' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.assignment_assigned_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.assignment_assigned_email_subject'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.assignment_assigned_email_greeting', ['studentName' => '{studentName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.assignment_assigned_email_content', [
                                    'studentName' => '{studentName}', 
                                    'tutorName' => '{tutorName}', 
                                    'quizName' => '{quizName}'
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            'reviewedAssignment' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.reviewed_assignment_notify'),
                'roles' => [
                    'tutor' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.assignment_in_review_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.assignment_in_review_email_subject'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.assignment_in_review_email_greeting', ['tutorName' => '{tutorName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.assignment_in_review_email_content', [
                                   'studentName' => '{studentName}',
                                    'tutorName' => '{tutorName}',
                                    'assignmentName' => '{assignmentName}',
                                    'reviewedAssignmentUrl' => '{reviewedAssignmentUrl}'
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            'generateAssignmentResult' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.assignment_result_notify'),
                'roles' => [
                    'student' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.assignment_result_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.assignment_result_email_subject'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.assignment_result_email_greeting', ['studentName' => '{studentName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.assignment_result_email_content', [
                                    // 'studentName' => '{studentName}',
                                    'tutorName' => '{tutorName}',
                                    'assignmentName' => '{assignmentName}',
                                    'assignmentResultUrl' => '{assignmentResultUrl}',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getNotificationTemplates()
    {  
        NotificationTemplate::whereIn('type', ['assignedAssignment', 'reviewedAssignment', 'generateAssignmentResult'])->forceDelete();
        return [
            'assignedAssignment' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.assigned_assignment_notify'),
                'roles' => [
                    'student' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.assignment_assigned_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.assigned_assignment_notify'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.assigned_email_greeting', ['studentName' => '{studentName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.assigned_assignment_link_text', [
                                    'studentName' => '{studentName}', 
                                    'tutorName' => '{tutorName}', 
                                    'assignmentName' => '{assignmentName}',
                                    'assignedAssignmentUrl' => '{assignedAssignmentUrl}'
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            'reviewedAssignment' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.reviewed_assignment_notify'),
                'roles' => [
                    'tutor' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.reviewed_assignment_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.reviewed_assignment_notify'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.reviewed_assignment_email_greeting', ['tutorName' => '{tutorName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.reviewed_assignment_link_text', [
                                   'studentName' => '{studentName}',
                                    'tutorName' => '{tutorName}',
                                    'assignmentName' => '{assignmentName}',
                                    'reviewedAssignmentUrl' => '{reviewedAssignmentUrl}'
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            'generateAssignmentResult' => [
                'version' => '1.0',
                'title' => __('assignments::assignments.assignment_result_notify'),
                'roles' => [
                    'student' => [
                        'fields' => [
                            'info' => [
                                'title' => __('assignments::assignments.variables_used'),
                                'icon' => 'icon-info',
                                'desc' => __('assignments::assignments.assignment_result_variables'),
                            ],
                            'subject' => [
                                'id' => 'subject',
                                'title' => __('assignments::assignments.subject'),
                                'default' => __('assignments::assignments.assignment_result_notify'),
                            ],
                            'greeting' => [
                                'id' => 'greeting',
                                'title' => __('assignments::assignments.greeting_text'),
                                'default' => __('assignments::assignments.assignment_result_email_greeting', ['studentName' => '{studentName}']),
                            ],
                            'content' => [
                                'id' => 'content',
                                'title' => __('assignments::assignments.email_content'),
                                'default' => __('assignments::assignments.assignment_result_link_text', [
                                    'studentName' => '{studentName}',
                                    'tutorName' => '{tutorName}',
                                    'assignmentName' => '{assignmentName}',
                                    'assignmentResultUrl' => '{assignmentResultUrl}'
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

}
