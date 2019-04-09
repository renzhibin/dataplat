<?php

try {
    exec('git log --pretty=format:"%H" -1', $gitversion, $giterror);
    if ($giterror == 0 && is_array($gitversion) && is_string($gitversion[0]))
        $confVersion = $gitversion[0];

    if(!empty($confVersion))
        file_put_contents('../config/version.txt',$confVersion);

} catch (Exception $a) {
    exit();
}
