<!doctype html>
<?php

function get_subjects_from_file($path) {
    return array_map('trim', array_filter(file($path), function ($line) {
        return trim($line) !== '' && $line{0} === '.';
    }));
}

$allowedMatches = get_subjects_from_file(__DIR__ . '/test.pass');
$unMatches = get_subjects_from_file(__DIR__ . '/test.fail');

$subjects = implode("\n", array_merge($allowedMatches, $unMatches));

$regexs = file(__DIR__ . '/BEM.regex');
$regexs = array_filter($regexs, function ($pattern) {
    return trim($pattern) !== '' && $pattern{0} === '^';
});

$result = '';
array_walk($regexs, function ($regex) use (&$result, $allowedMatches, $subjects) {
    $regex = trim($regex);

    $pattern = vsprintf("/%s/m", [$regex]);
    $encodedPattern = htmlentities($regex);
    $match = preg_match_all($pattern, $subjects, $matches);

    // @TODO: Mark test as error if $match === false

    $shouldMatch = array_diff($allowedMatches, $matches[0]);
    $shouldNotMatch = array_diff($matches[0], $allowedMatches);

    if ($shouldMatch === [] && $shouldNotMatch === []) {
        $result .=  '<p><span class="pass">Success.</span> <code>' . $encodedPattern .'</code></p>';
    } else {
        $result .=  '<p><span class="fail">Failure.</span> <code>' . $encodedPattern .'</code></p>';
        $glue = '</code></li><li><code>';

        if ($shouldMatch !== []) {
            $result .= '<p>The following items SHOULD have been matched but were not:</p>'
                . '<ul><li><code>'.join($glue, $shouldMatch).'</code></li></ul>'
            ;
        }

        if ($shouldNotMatch !== []) {
            $result .= '<p>The following items SHOULD NOT have been matched but were:</p>'
                . '<ul><li><code>'.join($glue, $shouldNotMatch).'</code></li></ul>'
            ;
        }
    }
});

?>
<html lang="en">
<head>
    <title></title>
    <style>
        summary {
            border: 1px solid #CCC;
            cursor: pointer;
            display: inline;
            padding: 0.2em;
        }
        .fail {
            background-color: red;
            color: white;
            display: inline;
            padding: 0.2em;
        }

        .pass {
            background-color: limegreen;
            color: white;
            display: inline;
            padding: 0.2em;
        }
    </style>
</head>
<body>
<h1>Regex for BEM</h1>
<h2>The regular expressions</h2>

<?= $result; ?>

<h2>The text matched against</h2>
<details>
    <summary>Click to open</summary>
    <pre><code><?= $subjects; ?></code></pre>
</details>
</body>
</html>
