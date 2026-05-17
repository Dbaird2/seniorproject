<?php
function checkForm($id, $tag, $app_id, $form)
{
    echo $id . ' ' . $tag . ' ' . $app_id . ' ';
    echo '<pre>';
    echo $form . '<br>';
    echo '</pre>';
    global $dept_id, $audit_id, $query_repo, $kuali;

    $decode_true = $kuali->dataworksCheckForms($app_id, $tag);

    $edges = $decode_true['data']['app']['documentConnection']['edges'];
    $tag_regex = '/\b(' . $tag . ')\b/i';
    $form_status = '';
    $select = "SELECT unnest(check_forms) as form_id FROM audit_history WHERE dept_id = :dept_id AND audit_id = :id";
    foreach ($edges as $index => $edge) {
        $form_id = $edge['node']['id'];
        if ($id === $form_id) {
            $status = $edge['node']['meta']['workflowStatus'];
            if ($status === 'In Progress') {
                return false;
            }
            if ($status === 'Denied') {
                $form_status = 'denied';
            }
            if ($status === 'Complete') {
                $form_status = 'complete';
            }
            if ($status === 'Withdrawn') {
                $form_status = 'withdrawn';
            }

            $form = trim($form, '{}"');
            if (preg_match($tag_regex, $form)) {
                $new_form = str_replace('in-progress', $form_status, $form);
                $update = 'UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, ?) WHERE audit_id = ? AND dept_id = ?';
                $query_repo->execute($update, $new_form, $audit_id, $dept_id);
            }

            $update = 'UPDATE audit_history SET check_forms = ARRAY_REMOVE(check_forms, ?) WHERE audit_id = ? AND dept_id = ?';
            $query_repo->execute($update, $form, $audit_id, $dept_id);
            return $form_status;
        }
    }
    return false;
}
