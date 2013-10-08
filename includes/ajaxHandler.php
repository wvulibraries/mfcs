<?php

if(isset($engine->cleanGet['MYSQL']['ajax'])){
    $result = array();
    if (isset($engine->cleanPost['MYSQL']['action'])) {
        switch($engine->cleanPost['MYSQL']['action']){
            case 'updateUserProjects':
                $result = users::updateUserProjects();
                break;
            default:
                break;
        }
    }
    else if (isset($engine->cleanGet['MYSQL']['action'])) {
        switch($engine->cleanGet['MYSQL']['action']){
            case 'selectChoices':
                $field        = forms::getField($engine->cleanGet["MYSQL"]['formID'],$engine->cleanGet["MYSQL"]['fieldName']);
                $fieldChoices = forms::getFieldChoices($field);
                $result       = forms::drawFieldChoices($field,$fieldChoices);
                die($result);
                break;
            case 'searchFormFields':
                die(mfcsSearch::formFieldOptions($engine->cleanGet["MYSQL"]['formID']));
                break;
            case 'paginationPerPage':
                $result = users::setField('pagination',$engine->cleanGet["MYSQL"]['perPage']);
                die(json_encode((($result)?"TRUE":"FALSE")));
                break;
        }
    }
    header('Content-type: application/json');
    die(json_encode($result));
}

?>