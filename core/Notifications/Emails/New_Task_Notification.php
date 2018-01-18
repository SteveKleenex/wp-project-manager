<?php 

namespace WeDevs\PM\Core\Notifications\Emails;

/**
* Email Notification When a new project created
*/
use WeDevs\PM\Core\Notifications\Email;

class New_Task_Notification extends Email {
    
    function __construct() {

        add_action('pm_after_create_task_notification', array($this, 'trigger'), 10, 2 );
    }

    public function trigger( $task, $data ) {

        $task->load('assignees.assigned_user', 'projects', 'creator');
        $users = array();
        foreach ($task->assignees as $assignee ) {
            if( $this->is_enable_user_notification( $assignee->assignee_to ) ){
                $users[] = $assignee->assigned_user->user_email;
            }
        }

        if( !$users ){
            return ; 
        }

        $template_name = apply_filters( 'pm_new_task_email_template_path', $this->get_template_path( '/html/new-task.php' ) );
        $subject = sprintf( __( '[%s][%s] New Task Assigned: %s', 'pm' ), $this->get_blogname(), $task->projects->title, $task->title );


        $message = $this->get_content_html( $template_name, [
            'id'    => $task->id,
            'title' => $task->title,
            'project_id'    => $task->project_id,
            'creator' => $task->creator->display_name,
            'due_date' => $task->due_date
        ] );

        $this->send( $users, $subject, $message );

    }

}