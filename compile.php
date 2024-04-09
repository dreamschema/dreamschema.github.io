<?php

$data = json_decode(file_get_contents('data.json'), true);
$truth = json_decode(file_get_contents('truth.json'), true);

$newData = [];


$approvedRelations = [];
 
foreach($truth['characters']  as $character){
    if($character['relations']){
        foreach ($character['relations'] as $relationType => $relation){
            if (!is_array($relation)){
                $relation = [ $relation ];
            }
            if(substr($relationType,-1)=="+"){
                $relationType = substr($relationType,0,-1);
                $approvedRelations[$character["name"]][$relationType]=$relation;
            }
        }
    }
}

foreach($truth['characters']  as $key=>$character){
    if($character['relations']){
        foreach ($character['relations'] as $relationType => $relation){
            if (!is_array($relation)){
                $relation = [ $relation ];
            }
            if(substr($relationType,-1)=="+"){
                $relationType = substr($relationType,0,-1);
                $truth['characters'][$key]['relations'][$relationType] = $relation;
                unset($truth['characters'][$key]['relations'][$relationType . '+']);
            }
        }
    }
}

 
/////////////////////////////
//Добавляем Истину


$newChapter = [];
$newChapter['chapter'] = 0;
$newChapter['chapter_summary'] = 'Данные о связях, добавленные вручную';
$newChapter['characters'] = [];


foreach($truth['characters']  as $character){
    $newCharacter = [];
    $newCharacter["name"]=$character["name"];
    $newCharacter["type"]=$character["type"]??'';
    $newCharacter["description"]=$character["description"]??'';
    $newCharacter["actions"]=$character["actions"]??'';
    $newCharacter["relations"]=[];
    if(isset($character['relations'])){

        foreach ($character['relations'] as $relationType => $relation){
            if (!is_array($relation)){
                $relation = [ $relation ];
            }
            $newCharacter["relations"][$relationType] = $relation;
        }
        
    }
    $newChapter['characters'][] = $newCharacter;

}
$newData[]= $newChapter;

////
foreach($data as $chapter){

    $newChapter = [];
    $newChapter['chapter'] = $chapter['chapter'];
    $newChapter['chapter_summary'] = $chapter['chapter_summary'];
    $newChapter['characters'] = [];


    foreach($chapter['characters']  as $character){
        $newCharacter = [];
        $newCharacter["name"]=$character["name"];
        $newCharacter["type"]=$character["type"];
        $newCharacter["description"]=$character["description"];
        $newCharacter["actions"]=$character["actions"]??'';
        $newCharacter["relations"]=[];
        if(isset($character['relations'])){

            foreach ($character['relations'] as $relationType => $relation){
                if (!is_array($relation)){
                    $relation = [ $relation ];
                }
                if(substr($relationType,-1)=="+"){
                    $relationType = substr($relationType,0,-1);
                }

                if(isset( $approvedRelations[$newCharacter["name"]][$relationType])){
                    $relation =  $approvedRelations[$newCharacter["name"]][$relationType];
                }
              
                $newCharacter["relations"][$relationType] = $relation;
            }
            
        }
        $newChapter['characters'][] = $newCharacter;

    }
    $newData[]= $newChapter;
}


file_put_contents('compiled.js', 
    'window.connections = ' .  json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n".
    'window.truth_connections = ' .  json_encode($truth, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n"
);