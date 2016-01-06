<?php

$byte_array = unpack('C*', 'The quick fox jumped over the lazy brown dog');

print_r($byte_array);
