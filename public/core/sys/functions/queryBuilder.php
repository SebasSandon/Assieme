<?php
function queryBuilder($elements){
    $query = '';
    foreach ($elements as $element){
        if($element[0]){
            $query .= $element[1];
        }
    }
    return $query;
}