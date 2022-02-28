<?PHP
ini_set('memory_limit', 128000000);
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 0);

include_once __DIR__ . '/Clases/Logger.php';
include_once __DIR__ . '/Clases/DBConn.php';
include_once __DIR__ . '/Clases/Queries.php';

$config = parse_ini_file(__DIR__ . "/config.ini", true);

define('_IDCOLEGIO_', intval($argv[1]));

$start = microtime(true);

$logger = new Logger();
$logger->enableLogFile($config['LOG']['ENABLE']);
$logger->enableErrorLogFile($config['LOG']['ERRORLOG']);
$logger->enableQueryLogFile($config['LOG']['QUERYLOG']);
$logger->enableOnScreenOutput($config['LOG']['SCREEN']);
$logger->setDirname($config['LOG']['PATH']);
$logger->setFilename(_IDCOLEGIO_ . '-' . $config['LOG']['FILENAME']);
$logger->setErrorFilename(_IDCOLEGIO_ . '-' . $config['LOG']['ERRORFILENAME']);
$logger->setQueryFilename(_IDCOLEGIO_ . '-' . $config['LOG']['QUERYFILENAME']);
$logger->enableDateOnFileName($config['LOG']['DATEONFILENAME']);


$paramsDB =  array(
    'host' => $config['DB']['HOST'],
    'port' => $config['DB']['PORT'],
    'dbname' => $config['DB']['DATABASE'],
    'username' => $config['DB']['USERNAME'],
    'password' => $config['DB']['PASSWORD']
);

$dbConn = new DBconn();
$dB = $dbConn->conectMySQL($paramsDB);

$q = new Queries($logger, $dB);

print_r(PHP_EOL . json_encode($q->getStudents(_IDCOLEGIO_)) . PHP_EOL);

$end = microtime(true);
$total = $end - $start;
$logger->log('DURACIÓN TOTAL : ' . $logger->conversorSegundosHoras($total));
