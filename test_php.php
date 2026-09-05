<?php
$lessonType = 'lessonpolling';
$canMarkCompleteManually = !in_array($lessonType, ['quiz', 'lessonassignment', 'lessonpolling', 'lessonwordcloud']);
var_dump($canMarkCompleteManually);
