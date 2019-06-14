<?php
function nest($items)
{  
    $tree = array();
    
    /**
     * find roots
     */
    foreach ($items as $key => $item) {
        if($item->parent === null){
            $tree[$item->id] = $item;
            unset($items[$key]);
        }
    }

    do {
        foreach($items as $key => $item){
            $parent = $item->parent;
            if(array_key_exists($parent, $tree)){
                $tree[$parent]->children[$item->id] = $item;
                $tree[$item->id] = $item;
                unset($items[$key]);
            }
        }
    } while(count($items) > 0);
    
    /**
     * keep roots
     */
    foreach($tree as $key => $item){
        if($item->parent !== null){
            unset($tree[$key]);
        }
    }
    
    return $tree;
}