<?PHP
ini_set('memory_limit', 134217728);
ini_set('mysql.connect_timeout', 14400);
ini_set('default_socket_timeout', 14400);
ini_set('max_execution_time', 0);
// BD en blanco solo con parámetros definidos por Enlaces-Mineduc
class Funciones
{
    private static $logger;
    private static $originDB;
    private static $destinationDB;
    function __construct($logger, $originDB, $destinationDB=false)
    {
        SELF::$logger = $logger;
        SELF::$originDB = $originDB;
        if ($destinationDB) {
            SELF::$destinationDB = $destinationDB;
        }
    }
    /**
     * Función para validar si el valor de una variable es string o numerico.
     * Retorna TRUE al ser numerico
     * Retorna FALSE al ser string
     */
    private function is_stringy($val)
    {
        return (is_string($val) && is_numeric($val));
    }

    public static function removeNonNumeric($input)
    {
        $output = preg_replace("/[^0-9]/", "", $input);
        return $output;
    }

    public static function findByName($text, $where)
    {
        $output = preg_grep('/^' . $text . '\s.*/', $where);
        print_r($output);
        return $output;
    }

    public function query($sql, $donde="origen")
    {
        try {
            $queryStart = 0;
            $queryEnd = 0;
            $queryStart = microtime(true);
            SELF::$logger->log('INICIA QUERY :: ' . $sql);
            if ($donde=="destino") {
                $query = SELF::$destinationDB->prepare($sql);
            } else {
                $query = SELF::$originDB->prepare($sql);
            }
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$originDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$originDB->errorInfo());
            }
            $query->execute();
            if ($query->rowCount()> 0) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                return true;
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

    public function select($sql, $table, $idColegio)
    {
        if (!$idColegio) {
            SELF::$logger->log('WARNING: NO SE INDICÓ EL COLEGIO A CONSULTAR, SE INTERRUMPE EL PROCESO.');
            exit(1);
        }
        try {
            $queryStart = 0;
            $queryEnd = 0;
            $selects = 0;
            $queryStart = microtime(true);
            SELF::$logger->log('INICIA SELECTS :: ' . $table);
            $query = SELF::$originDB->prepare($sql);
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$originDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$originDB->errorInfo());
            }
            $query->bindParam(':idColegio', $idColegio, PDO::PARAM_INT);
            $query->execute();
            if ($selects = $query->rowCount()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                SELF::$logger->log('WARNING: NO SE ENCONTRÓ DATA PARA ITERAR.');
                // exit(1);
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: EN LA CONSULTA A LA BASE DE DATOS DE ORIGEN: ' . $p, 1);
            exit(1);
        } finally {
            $queryEnd = microtime(true);
            SELF::$logger->log('TERMINA SELECTS :: ' . $table);
            $queryDuration = $queryEnd - $queryStart;
            SELF::$logger->log($table . ' LEIDOS (' . $selects . ') REGISTROS.');
            SELF::$logger->log('DURACIÓN : ' . SELF::$logger->conversorSegundosHoras($queryDuration));
        }
    }

    public function selectInsert($sql, $table, $idColegio, $arr)
    {
        $contador = 0;
        if (!$idColegio) {
            SELF::$logger->log('WARNING: NO SE INDICÓ EL COLEGIO A CONSULTAR, SE INTERRUMPE EL PROCESO.');
            exit(1);
        }
        $this->truncate($table . "_aux");
        if ($table == "Organization") {
            // $this->query(" ALTER TABLE Organization_aux AUTO_INCREMENT = 1;", "destino");
        }
        if ($table == "OrganizationCalendarDay") {
            $this->query(" ALTER TABLE OrganizationCalendarDay_aux AUTO_INCREMENT = 2;", "destino");
        }
        try {
            $queryStart = 0;
            $queryEnd = 0;
            $selects = 0;
            $queryStart = microtime(true);
            // SELF::$logger->log('INICIA SELECTS :: ' . $table);
            $query = SELF::$originDB->prepare($sql);
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$originDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$originDB->errorInfo());
            }
            $query->bindParam(':idColegio', $idColegio, PDO::PARAM_INT);
            $query->execute();
            if ($selects = $query->rowCount()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                // ******
                $inserts = 0;
                $_columns = array_keys($result[0]);
                foreach ($_columns as $_column) {
                    $columns[] = (@$arr[$_column]) ? :$_column;
                }
                $columns = implode(", ", $columns);
                foreach ($result as $row) {
                    $fixedValues = NULL;
                    $fixedValues = array();
                    foreach ($row as $key => $value) {
                        if ($this->is_stringy($value)) {
                            $fixedValues[$key] = $value;
                        } else {
                            if (is_null($value)) {
                                $fixedValues[$key] = 'NULL';
                            } else {
                                $fixedValues[$key] =  SELF::$destinationDB->quote($value);
                            }
                        }
                    }
                    $values = implode(", ", $fixedValues);
                    // SELF::$logger->log('Intento insertar: ' . $contador);
                    try {
                        $sql = "INSERT INTO " . $table . "_aux (" . $columns . ") VALUES (" . $values . ");";
                        SELF::$logger->qlog($sql);
                        $query = SELF::$destinationDB->prepare($sql);
                        $query->execute();
                        $inserts++;
                    } catch (PDOException $p) {
                        SELF::$logger->log("ERROR: AL INTENTAR INSERTAR EL REGISTRO EN LA BASE DE DATOS DE DESTINO ($contador): " . $p."\nQuery: $sql", 1);
                        exit(1);
                    }
                    $contador++;
                }
                // *********
            } else {
                SELF::$logger->log('WARNING: NO SE ENCONTRÓ DATA PARA ITERAR.');
                // exit(1);
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: EN LA CONSULTA A LA BASE DE DATOS DE ORIGEN: ' . $p."\n$sql", 1);
            exit(1);
        } finally {
            $queryEnd = microtime(true);
            SELF::$logger->log('TERMINA SELECTS :: ' . $table);
            $queryDuration = $queryEnd - $queryStart;
            SELF::$logger->log($table . ' LEIDOS (' . $selects . ') REGISTROS.');
            SELF::$logger->log('DURACIÓN : ' . SELF::$logger->conversorSegundosHoras($queryDuration));
        }
    }

    public function insert($dataset, $table, $replace=null)
    {
        try {
            $inserts=0;
            $contador = 1;
            $queryStart = 0;
            $queryEnd = 0;
            $queryStart = microtime(true);
            $res = $this->query("DESCRIBE $table", "destino");
            $je = json_encode($res);
            $auto = 0;
            if (strstr($je, "auto_increment")) {
                // echo "Con autoincremental\n";
                $auto = 1;
            }
            SELF::$logger->log('INICIA INSERTS :: ' . $table);

            if (@$dataset[0] === null) {
                SELF::$logger->log('Tabla vacia se ignora : ' . $table);
                return true;
            }

            $_columns = array_keys($dataset[0]);
            $columns = implode(", ", $_columns);
            SELF::$logger->log('COLUMNAS :: ' . $columns);
            SELF::$destinationDB->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $query = SELF::$destinationDB->prepare("SELECT * FROM $table");
            $query->execute();
            SELF::$logger->log('FILAS EN TABLA DE DESTINO: ' . $query->rowCount());
            SELF::$logger->log('FILAS EN dataset: ' . count($dataset));
            $inserts = 0;
            foreach ($dataset as $row) {
                $fixedValues = NULL;
                $fixedValues = array();
                foreach ($row as $key => $value) {
                    if ($this->is_stringy($value) && !strstr($value, "+") ) {
                        $fixedValues[$key] = $value;
                    } else {
                        if (is_null($value)) {
                            $fixedValues[$key] = 'NULL';
                        } else {
                            $fixedValues[$key] =  SELF::$destinationDB->quote($value);
                        }
                    }
                }
                if (@is_array($replace)) {

                    // SELF::$logger->log('** viene array de reemplazo : '.print_R($replace, true));
                    foreach ($replace as $rep) {
                        // SELF::$logger->log("** buscando {$rep["b"]} en ".print_r($_columns,true));
                        $kb = array_search($rep["b"], $_columns);
                        // SELF::$logger->log("** kb: [{$kb}]");
                        if ($kb !== false) {
                            // SELF::$logger->log("** encontrado en posicion: $kb");
                            $fixedValues[$_columns[$kb]] = $rep["r"]."(".$fixedValues[$_columns[$kb]].")";
                        } else {
                            SELF::$logger->log("** no encontrado");
                        }
                    }
                }
                $values = implode(", ", $fixedValues);
                // SELF::$logger->log('Intento insertar: ' . $contador);
                try {
                    $sql = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $values . ");";
                    // echo $sql;
                    // exit;
                    //SELF::$logger->log($sql);

                    $query = SELF::$destinationDB->prepare($sql);
                    $query->execute();
                    if ($auto) {
                        if (SELF::$destinationDB->lastInsertId()) {
                            $inserts++;
                        } else {
                            SELF::$logger->log("ERROR: CASO SIN AUTOINCREMENT ($contador): " . $query->errorCode()."\n$sql\n");
                        }
                    } else {
                        $inserts++;
                    }
                } catch (PDOException $p) {
                    SELF::$logger->log("ERROR: AL INTENTAR INSERTAR EL REGISTRO EN LA BASE DE DATOS DE DESTINO ($contador): " . $p."\nQuery: $sql", 1);
                    exit(1);
                }
                $contador++;
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: AL INTENTAR INSERTAR EL REGISTRO EN LA BASE DE DATOS DE DESTINO: ' . $p, 1);
            exit(1);
        } finally {
            $queryEnd = microtime(true);
            SELF::$logger->log('TERMINA INSERTS :: ' . $table);
            if(is_array($dataset)|| is_object($dataset)){

                SELF::$logger->log($table . ' :: ESCRITOS (' . $inserts . '/'.count($dataset).') REGISTROS.');
            }

            $query = SELF::$destinationDB->prepare("select * from $table");
            $query->execute();
            SELF::$logger->log('FILAS EN TABLA DE DESTINO POST INSERT: ' . $query->rowCount()) . PHP_EOL;
            $queryDuration = $queryEnd - $queryStart;
            SELF::$logger->log('DURACIÓN : ' . SELF::$logger->conversorSegundosHoras($queryDuration).PHP_EOL);
        }
    }

    public function dumpCSV($table, $destination)
    {
        try {
            $dumpStart = 0;
            $dumpEnd = 0;
            $registers = 0;
            $dumpStart = microtime(true);
            $extra = "";
            if ($table == "Organization") {
                $extra =  " WHERE OrganizationId > 1";
            }

            SELF::$logger->log('INICIA DUMP CSV :: ' . $table);
            if (strlen($destination)) {
                if (!is_dir($destination)) {
                    mkdir($destination, 0755, true);
                }
            }
            SELF::$logger->log('CARPETA DE DESTINO DE CSV :: ' . $destination);
            $destination = $destination . '/' . $table . '.csv';
            $query = SELF::$destinationDB->prepare('SELECT * FROM ' . $table . " $extra;");
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$destinationDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$destinationDB->errorInfo());
            }
            $query->execute();
            $registers = $query->rowCount();
            if ($registers > 0) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                $columns = SELF::getColumnsNames($table);

                $csv = fopen($destination, 'w') or die('No se pudo abrir el archivo: ' . $destination);
                fputcsv($csv, $columns, ';');
                foreach ($result as $r) {
                    fputcsv($csv, $r, ';');
                }
                fclose($csv);
            } else{
                SELF::$logger->log('ERROR: TABLA '.$table.' SIN DATOS ', 1);
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: EN LA CONSULTA A LA BASE DE DATOS DE ORIGEN: ' . $p, 1);
            exit(1);
        } finally {
            $dumpEnd = microtime(true);
            SELF::$logger->log('TERMINA DUMP CSV :: ' . $table);
            $dumpDuration = $dumpEnd - $dumpStart;
            SELF::$logger->log($table . ' LEIDOS (' . $registers . ') REGISTROS.');
            SELF::$logger->log('DURACIÓN : ' . SELF::$logger->conversorSegundosHoras($dumpDuration));
        }
    }

    private static function getColumnsNames($table)
    {
        $columns = array();
        try {
            //$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'librodigital' AND TABLE_NAME = '" . $table . "';";
            $sql= "describe $table ";
            $query = SELF::$destinationDB->prepare($sql);
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$destinationDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$destinationDB->errorInfo());
            }
            $query->execute();
            if ($query->rowCount()) {
                $result = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $r) {
                    array_push($columns, $r['Field']);
                }

                return $columns;
            } else {
                SELF::$logger->log('WARNING: NO SE ENCONTRÓ DATA PARA ITERAR');
                // exit(1);
            }
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: AL TRATAR DE LEER LAS COLUMNAS DE LA TABLA ' . $table . ': ' . $p, 1);
            exit(1);
        }
    }

    public function truncate($table)
    {
        try {
            $sql = "TRUNCATE TABLE " . $table . ";";
            SELF::$logger->log($sql);
            $query = SELF::$destinationDB->prepare($sql);
            if (!$query) {
                SELF::$logger->log('ERROR: ' . SELF::$destinationDB->errorInfo(), 1);
                throw new Exception('ERROR: ' . SELF::$destinationDB->errorInfo());
            }
            $query->execute();
            return true;
        } catch (PDOException $p) {
            SELF::$logger->log('ERROR: AL TRUNCAR TABLA ' . $table . ': ' . $p, 1);
            exit(1);
        }
    }
}
