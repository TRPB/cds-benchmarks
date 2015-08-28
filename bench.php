<?php
require_once 'vendor/autoload.php';

//Not sure why composer cannot include this via classmap so include it anyway (TODO: Add an issue for Maphper)
require_once 'vendor/tombzombie/maphper/maphper/datasource/mysqladapter.php';


//Firstly create some dummy data and a database table using Maphper
//This is in a function so it can be reused. Maphper has an interal cache which I want to avoid
function getTopics() {
	$pdo = new \PDO('mysql:dbname=maphpertest;host=127.0.0.1', 'u', '');
	$topics = new \Maphper\Maphper(new \Maphper\DataSource\Database($pdo, 'topics', 'id', ['editmode' => true]));
	return $topics;
}

//Add 50 records to represent 50 forum posts

$topics = getTopics();
//Delete existing records
foreach ($topics as $topic) {
	unset($topics[$topic->id]);
}

//Create some new records
for ($i = 0; $i < 50; $i++) {
	$topic = new \stdclass;
	$topic->title = 'Topic ' . $i;
	$topic->author = 'Author ' . $i;
	$topics[] = $topic;
}


//Check the data has been stored correctly
if (count($topics) < 50) {
	echo 'Could not save data';
	die;
}
unset($topics);


function benchmark($closure, $times = 100) {
	$result = [];

	for ($i = 0; $i < $times; $i++) {
		$t1 = microtime(true);
		$closure();
		$t2 = microtime(true);	
		$result[] = $t2 - $t1;
	} 
	
	return array_sum($result) / count($result);

}




echo 'Benchmarking CDS:';
echo benchmark(function() {
	//Get a new instance of $topics each time to avoid maphper caching
	$topics = getTopics();
	$template = new \CDS\Builder(file_get_contents('template.xml'), 'topics.cds', $topics);
	$output = $template->output();
});

echo "\n\n";


echo 'Benchmarking tpl:';
echo benchmark(function() {
	//Get a new instance of $topics each time to avoid maphper caching
	$topics = getTopics();
	$data = ['topics' => $topics];
	extract($data);
	ob_start();
	require_once 'template.php';
	$output = ob_get_clean();
});

echo "\n\n";

echo 'Bechmkaring cached output:';
echo benchmark(function() {
	//Get a new instance of $topics each time to avoid maphper caching
	if (is_file('tmp/template.cache')) $output = file_get_contents('tmp/template.cache');
	else {
		$topics = getTopics();
		$template = new \CDS\Builder(file_get_contents('template.xml'), 'topics.cds', $topics);
		$output = $template->output();
		file_put_contents('tmp/template.cache', $output);
	}
});

echo "\n\n";
