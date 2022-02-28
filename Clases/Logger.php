<?PHP

/**
 * @author D14Bl0
 * @email erodriguez@webclass.com
 * @create date 13-112020 09:24:25
 * @modify date 13-112020 09:24:25
 * @desc [Clase para generación de LOG]
 */
class Logger
{
    private static $logFile = true;
    private static $errorLogFile = false;
    private static $queryLogFile = false;
    private static $dateOnFileName = true;
    private static $onScreenOutput = false;
    private static $dirname = 'logs/';
    private static $filename = 'logFile.log';
    private static $errorFilename = 'error_logFile.log';
    private static $queryFilename = 'queriesLogFile.log';

    public static function enableLogFile($logFile)
    {
        self::$logFile = $logFile;
    }

    public static function islogFileEnabled()
    {
        return self::$logFile;
    }

    public static function enableErrorLogFile($errorLogFile)
    {
        self::$errorLogFile = $errorLogFile;
    }

    public static function isErrorLogFileEnabled()
    {
        return self::$errorLogFile;
    }
    public static function enableQueryLogFile($queryLogFile)
    {
        self::$queryLogFile = $queryLogFile;
    }

    public static function isQueryLogFileEnabled()
    {
        return self::$queryLogFile;
    }

    public static function enableDateOnFileName($dateOnFileName)
    {
        self::$dateOnFileName = $dateOnFileName;
    }

    public static function isDateOnFileNameEnabled()
    {
        return self::$dateOnFileName;
    }

    public static function enableOnScreenOutput($onScreenOutput)
    {
        self::$onScreenOutput = $onScreenOutput;
    }

    public static function isOnScreenOutputEnabled()
    {
        return self::$onScreenOutput;
    }

    public static function setDirname($dirname)
    {
        self::$dirname = $dirname;
    }

    public static function getDirname()
    {
        return self::$dirname;
    }

    public static function setFilename($filename)
    {
        self::$filename = $filename;
    }

    public static function getFilename()
    {
        return  self::$filename;
    }

    public static function setErrorFilename($errorFilename)
    {
        self::$errorFilename = $errorFilename;
    }

    public static function getErrorFilename()
    {
        return  self::$errorFilename;
    }

    public static function setQueryFilename($queryFilename)
    {
        self::$queryFilename = $queryFilename;
    }

    public static function getQueryFilename()
    {
        return  self::$queryFilename;
    }

    public static function conversorSegundosHoras($tiempoEnSegundos)
    {
        $horas = floor($tiempoEnSegundos / 3600);
        $minutos = floor(($tiempoEnSegundos - ($horas * 3600)) / 60);
        $segundos = $tiempoEnSegundos - ($horas * 3600) - ($minutos * 60);

        $hora_texto = "";
        if ($horas > 0) {
            $hora_texto .= $horas . " H ";
        }

        if ($minutos > 0) {
            $hora_texto .= $minutos . " m ";
        }

        if ($segundos > 0) {
            $hora_texto .= $segundos . " s";
        }

        return $hora_texto;
    }


    public static function log($string, $error = 0)
    {
        date_default_timezone_set('America/Santiago');

        // Escritura de log en archivo
        if (self::$logFile) {
            if (strlen(self::$dirname)) {
                if (!is_dir(self::$dirname)) {
                    mkdir(self::$dirname, 0755, true);
                }
            }
            if (self::isDateOnFileNameEnabled()) {
                $logFile = self::$dirname . date('Y-m-d_') . self::$filename;
                $errorLogFile = self::$dirname . date('Y-m-d_') . self::$errorFilename;
            } else {
                $logFile = self::$dirname . self::$filename;
                $errorLogFile = self::$dirname . self::$errorFilename;
            }
            $log = fopen($logFile, 'a') or die('No se pudo abrir el archivo: ' . $logFile);
            @chmod($logFile, 0777);
            $logLine = '[' . date('d-m-Y H:i:s') . '] :: ' . $string . PHP_EOL;
            if (self::isOnScreenOutputEnabled()) {
                echo $logLine;
            }
            fwrite($log, $logLine);
            fclose($log);
            if ($error && self::$errorLogFile) {
                $errorLog = fopen($errorLogFile, 'a') or die('No se pudo abrir el archivo: ' . $errorLogFile);
                @chmod($errorLogFile, 0777);
                $errorLogLine = '[' . date('d-m-Y H:i:s') . '] :: ' . $string . PHP_EOL;
                if (self::isOnScreenOutputEnabled()) {
                    echo $errorLogLine;
                }
                fwrite($errorLog, $errorLogLine);
                fclose($errorLog);
            }
        }
    }

    public static function qlog($string, $timestamp = 0)
    {
        date_default_timezone_set('America/Santiago');

        // Escritura de log en archivo
        if (self::$queryLogFile) {
            if (strlen(self::$dirname)) {
                if (!is_dir(self::$dirname)) {
                    mkdir(self::$dirname, 0755, true);
                }
            }
            if (self::isDateOnFileNameEnabled()) {
                $queryLogFile = self::$dirname . date('Y-m-d_') . self::$queryFilename;
            } else {
                $queryLogFile = self::$dirname . self::$queryFilename;
            }
            $log = fopen($queryLogFile, 'a') or die('No se pudo abrir el archivo: ' . $queryLogFile);
            chmod($queryLogFile, 0777);
            if ($timestamp) {
                $logLine = '[' . date('d-m-Y H:i:s') . '] :: ' . $string . PHP_EOL;
            } else {
                $logLine = $string . PHP_EOL;
            }
            fwrite($log, $logLine);
            fwrite($log, PHP_EOL);
            fclose($log);
        }
    }
}
