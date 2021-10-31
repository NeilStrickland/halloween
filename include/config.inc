<?php

global $halloween;

$top_data_dir = getenv('DATADIR');

if ($top_data_dir) {
 $halloween->data_dir = $top_data_dir . '/halloween';
} else {
 $halloween->data_dir = '/var/halloween';
}

$halloween->mapbox_token = "pk.eyJ1IjoibmVpbHN0cmlja2xhbmQiLCJhIjoiY2t2Y3dsNG9kNG9yZTJwcXd4NTlxZmVkdiJ9.K-4AglQjb6DSsq2ezkREHw";

