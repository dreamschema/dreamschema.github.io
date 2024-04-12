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


$levels = [];
$levels["Цзя Юань (Жунго-Гун)"] = 0;
$levels["Цзя Янь (Нинго-Гун)"] = 0;

$levelRules = [

    "brother"=>0,
    "sister"=>0,
    "son"=>1,
    "daughter"=>1,
    "mother"=>-1,
    "father"=>-1,
    "wife"=>0,
    "husband"=>0,
];

$anotherTry = true;
while($anotherTry){
    $anotherTry = false;
    foreach($newData as $key=>$chapter){
        foreach($chapter["characters"] as $character){
            $name = $character["name"];
            foreach($character["relations"] as $relationType => $relation){
                if(!is_array($relation)){
                    $relation = [$relation];
                }
                foreach ($relation as $targetName){

                    //Добавляем основного персонажа в базу
                    if(!isset($levels[$name])){
                        if(isset($levels[$targetName])) {
                            if(isset($levelRules[$relationType])){
                                $levels[$name] = $levels[$targetName] - $levelRules[$relationType];
                                $anotherTry = true;
                            }
                        }
                    }
                    
                    //Добавляем второстепенного персонажа в базу
                    if(!isset($levels[$targetName])){
                        if(isset($levels[$name])) {
                            if(isset($levelRules[$relationType])){
                                $levels[$targetName] = $levels[$name] + $levelRules[$relationType];
                                $anotherTry = true;
                            }
                        }
                    }             
                }
                
            }
        }
    }
}
file_put_contents('compiled.js', 
    'window.connections = ' .  json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n".
    'window.truth_connections = ' .  json_encode($truth, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n".
    'window.levels = ' .  json_encode($levels, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n"
);