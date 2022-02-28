#   **EVALUACIÓN DESARROLLO PHP / CLI**

### El archivo testCLI.php debe contener solamente la ejecución del programa, funciones y extras deben realizarse en las clases correspondientes en la carpeta Clases

## Definiciones
* Las consultas a base de datos deben realizarse por **PDO**, utilizando prepare y bindParam definiendo tipo de parametro
* La respuesta de las consultas deben ser devueltas en formato **OBJETO**

##  Tareas:
Ejecutar el script y capturar el JSON extraído: 
>$ php testCLI.php 23

***Definiciones de tablas a utilizar en TablasEvaluacionDesarrollo.xlsx***

1.   Generar codigo para guardar salida en formato JSON (validable en https://jsonlint.com/).
2.   Crear clase Alumno con los siguiente atributos: id, nombre completo, RUT, fecha de nacimiento, correo, teléfono ####(la data ya se encuentra en las tablas provistas en la DB, revisar xlsx adjunto en el repositorio).

3.   Crear una función que en base al 2° parametro de la ejecución genere un JSON con los alumnos habilitados o los inhabilitados.
>   $ php testCLI.php 23 1

4.   Crear una función que en base al 3° parametro de la ejecución genere un JSON con los alumnos del id del curso indicado.
>   $ php testCLI.php 23 1 23423


