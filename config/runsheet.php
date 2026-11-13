<?php

return [

    /*
    | Feature 2: the delivery zones shown in the top-bar picker.
    | The picked zone is saved in the session (never in the database).
    */
    'zones' => ['Riverside', 'Hillcrest', 'Old Harbor', 'Airport Strip'],

    // Used until a zone is picked, and again after every sign-out.
    'default_zone' => 'Riverside',

];
