<?PHP
ini_set('memory_limit', 134217728);
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 0);

class Queries
{
    private static $logger;
    private static $dB;
    function __construct($logger, $dB)
    {
        SELF::$logger = $logger;
        SELF::$dB = $dB;
    }

    public function getStudents($schoolID)
    {
        try {
            $queryStart = 0;
            $queryEnd = 0;
            $queryStart = microtime(true);
            $sql = "SELECT * FROM alumno WHERE colegio = :schoolID LIMIT 10;";
            SELF::$logger->log('INICIA QUERY :: ' . $sql);

            $query = SELF::$dB->prepare($sql);

            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$dB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$dB->errorInfo());
            }
            $query->bindParam(':schoolID', $schoolID, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: EN LA CONSULTA A LA BASE DE DATOS DE ORIGEN: ' . $p, 1);
            exit(1);
        } finally {
            $queryEnd = microtime(true);
            SELF::$logger->log('TERMINA QUERY :: ' . $sql);
            $queryDuration = $queryEnd - $queryStart;
            SELF::$logger->log('DURACIÓN : ' . SELF::$logger->conversorSegundosHoras($queryDuration));
        }
    }
}
