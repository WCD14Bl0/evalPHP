#   **EVALUACIÓN DESARROLLO PHP / CLI**

**El archivo testCLI.php debe contener solamente la ejecución del programa, funciones y extras deben realizarse en las clases correspondientes en la carpeta Clases**

## Definiciones
* Las consultas a base de datos deben realizarse por **PDO**, utilizando prepare y bindParam definiendo tipo de parametro
* La respuesta de las consultas deben ser devueltas en formato **OBJETO**
* Se requiere control de excepciones.
* Cada item tiene un valor de 6 puntos que se descomponen en:
  - 2 ptos. funcionamiento
  - 2 ptos. manejo de excepciones
  - 1 pto. orden
  - 1 pto. complejidad

_Puede levantar un entorno de desarrollo rápido con [Laragon](https://laragon.org/)_

[Descargar Laragon Portable](https://github.com/leokhoa/laragon/releases/download/5.0.0/laragon-portable.zip) 


**La respuesta se debe entregar en el fork generado a este repositorio.**

##  Tareas:
1.  Dar Fork a este repositorio, clonar y ejecutar el script, capturando el JSON extraído: 
>$ php testCLI.php 23

***Definiciones de tablas a utilizar en TablasEvaluacionDesarrollo.xlsx***


***LAS TABLAS CON LA DATA NECESARIA SE ENCUENTRAN EN EL ARCHIVO DB.zip, CADA TABLA VA EN UN ARCHIVO SQL POR SEPARADO***

2.   Levantar una base de datos MySQL y en ella montar las tablas contenidas en los sql de **DE.zip**
3.   Generar código para guardar salida en formato JSON (validable en https://jsonlint.com/).
4.   Crear clase Alumno con los siguiente atributos (la data ya se encuentra en las tablas provistas en la DB): 
     - id
     - nombre completo
     - RUT
     - fecha de nacimiento
     - correo
     - teléfono
5.   Crear una función que en base al 2° parametro de la ejecución genere un JSON con los alumnos habilitados o los inhabilitados.
>   $ php testCLI.php 23 **1**

6.   Crear una función que en base al 3° parametro de la ejecución genere un JSON con los alumnos del id del curso indicado.
>   $ php testCLI.php 23 1 **23423**

