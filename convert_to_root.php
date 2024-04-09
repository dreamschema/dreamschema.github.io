<?php

//Преобраование в главу номер 0, которая содержит проверенные связи

 
// Читаем JSON-файл
$jsonString = str_replace('window.connections = ', '', file_get_contents('RESULT.js'));
$data = json_decode($jsonString, true);
 
$characters = [];
 
 
// Функция для преобразования значения связи в массив
function toArray($value) {
    return is_array($value) ? $value : [$value];
}

// Обходим каждую главу
foreach ($data as $chapter) {
    // Обходим каждого персонажа в главе
    foreach ($chapter['characters'] as $character) {
        $name = $character['name'];

        // Если связи отсутствуют, создаем пустой массив
        if (!isset($character['relations'])) {
            $character['relations'] = [];
        }

        // Если персонаж уже существует в массиве, объединяем связи
        if (isset($characters[$name])) {
            foreach ($character['relations'] as $relation => $related) {
                if (!isset($characters[$name]['relations'][$relation])) {
                    $characters[$name]['relations'][$relation] = [];
                }

                $characters[$name]['relations'][$relation] = array_unique(array_merge(
                    $characters[$name]['relations'][$relation],
                    toArray($related)
                ));
            }
        } else {
            // Иначе добавляем нового персонажа без полей "actions" и "description"
            $characters[$name] = [
                'name' => $character['name'],
                'type' => $character['type'],
                'relations' => []
            ];

            foreach ($character['relations'] as $relation => $related) {
                $characters[$name]['relations'][$relation] = toArray($related);
            }
        }
    }
}

// Преобразуем массив персонажей в нужный формат
$result = [
    'characters' => array_values($characters)
];
// Выводим результат в формате JSON
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);