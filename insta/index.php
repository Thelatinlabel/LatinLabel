<?php

print_r($_REQUEST);
$output = print_r($_REQUEST, true);
file_put_contents('insta.txt', $output);  
?>
