<?PHP
ini_set('memory_limit', 134217728);
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 0);
class DBConn
{
    public static function conectMySQL($params)
    {
        try {
            $connection = new PDO(
                'mysql:host=' . $params['host'] . ';'
                    . 'port=' . $params['port'] . ';'
                    . 'dbname=' . $params['dbname'],
                $params['username'],
                $params['password']
            );
            $connection->exec("set names utf8mb4");
            $connection->exec("SET session wait_timeout=14400");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $connection;
        } catch (PDOException $p) {
            return $p;
        }
    }


}
