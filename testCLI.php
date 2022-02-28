<?PHP
ini_set('memory_limit', 128000000);
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 0);

include_once __DIR__ . '/Logger.php';

$config = parse_ini_file(__DIR__ . "/config.ini", true);

define('_IDCOLEGIO_', intval($argv[1]));
define("_ANO_", 2021);

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

$start = $end = 0;

