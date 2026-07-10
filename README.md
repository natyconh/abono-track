# 🌱 Abono Track

Abono Track es una plataforma web orientada a la gestión y trazabilidad de programas de fertilización agrícola. El sistema permite registrar fertilizantes, configurar distribuciones, calcular aportes nutricionales NPK y generar reportes para apoyar la toma de decisiones en predios agrícolas.

Este repositorio corresponde a una versión académica desarrollada para el proyecto final del ramo **Taller de Proyecto de Especialidad**.

## Integrantes

- Nathalia
- Cristian

## Contexto del proyecto

En la agricultura, la gestión de fertilización requiere controlar múltiples variables: productos utilizados, cantidades aplicadas, distribución hacia sectores o predios, y nutrientes aportados al cultivo. Cuando estos registros se realizan de forma manual o dispersa, se dificulta la trazabilidad, el análisis histórico y la generación de reportes confiables.

Abono Track busca centralizar esta información en una plataforma web que permita registrar y consultar datos de fertilización de manera ordenada, reduciendo errores y facilitando el seguimiento nutricional.

## Objetivo general

Desarrollar una plataforma web para la gestión y trazabilidad de programas de fertilización agrícola, incorporando registro de fertilizantes, cálculo nutricional NPK, historial de aplicaciones y generación de reportes.

## Objetivos específicos

- Registrar fertilizantes con sus respectivos aportes nutricionales.
- Gestionar aplicaciones de fertilización por fecha, predio o sector.
- Calcular automáticamente unidades nutricionales de nitrógeno, fósforo y potasio.
- Mantener historial de fertilizaciones realizadas.
- Generar reportes nutricionales para apoyar la toma de decisiones.
- Organizar la información de forma clara, trazable y consultable.

## Funcionalidades principales

### Gestión de fertilizantes

Permite registrar y administrar fertilizantes utilizados en el proceso agrícola, incluyendo información nutricional relevante como porcentaje de nitrógeno, fósforo y potasio.

### Registro de fertilización

Permite ingresar aplicaciones de fertilización, asociando productos, cantidades, fechas, predios y sectores correspondientes.

### Cálculo nutricional NPK

El sistema calcula automáticamente las unidades nutricionales aportadas según la composición del fertilizante y la cantidad aplicada.

### Configuración de distribución

Permite definir cómo se distribuye la fertilización entre distintos predios, sectores o instalaciones, facilitando el cálculo de aportes reales.

### Historial y trazabilidad

Permite consultar registros históricos de fertilización, revisar aplicaciones realizadas y mantener trazabilidad sobre los programas agrícolas.

### Reportes nutricionales

Permite generar reportes nutricionales para revisar los aportes realizados y apoyar el análisis técnico y la toma de decisiones.


## Tecnologías utilizadas

- PHP
- MySQL
- HTML5
- CSS / Bootstrap
- JavaScript
- Arquitectura MVC
- Patrón de servicios para lógica de negocio

## Tecnologías utilizadas

- **PHP 8.x:** desarrollo de la lógica del sistema, controladores, modelos y servicios.
- **MySQL 8:** almacenamiento de usuarios, predios, sectores, fertilizantes, registros y reportes.
- **HTML5 y CSS3:** estructura y diseño de las vistas del sistema.
- **Bootstrap 5:** construcción de una interfaz responsiva y componentes visuales.
- **JavaScript Vanilla:** apoyo a interacciones dinámicas dentro de la plataforma.
- **Git y GitHub:** control de versiones, respaldo del código y evidencia del avance del proyecto.
- **Arquitectura MVC:** separación entre modelos, vistas, controladores y lógica central del sistema.

## Estructura del repositorio

```text
abono-track/
├── README.md
├── .gitignore
├── composer.json
├── composer.lock
├── app/
│   ├── controllers/
│   │   ├── AdminController.php
│   │   ├── FertilizacionController.php
│   │   ├── FertilizanteController.php
│   │   ├── PrediosController.php
│   │   ├── SectoresController.php
│   │   ├── TrabajadoresController.php
│   │   └── UsersController.php
│   ├── core/
│   │   ├── App.php
│   │   ├── Controller.php
│   │   ├── FertilizacionService.php
│   │   ├── SessionHelper.php
│   │   └── StorageService.php
│   ├── models/
│   │   ├── FertilizanteModel.php
│   │   ├── PredioModel.php
│   │   ├── SectorModel.php
│   │   ├── TrabajadorModel.php
│   │   └── UserModel.php
│   └── views/
│       ├── admin/
│       ├── fertilizacion/
│       ├── fertilizantes/
│       ├── home/
│       ├── layout/
│       ├── predios/
│       ├── sectores/
│       ├── trabajadores/
│       └── users/
├── database/
│   └── schema_sanitized.sql
└── public/
    ├── css/
    │   └── style.css
    ├── img/
    ├── js/
    │   └── main.js
    └── index.php
```

La estructura del proyecto sigue una organización basada en arquitectura MVC, separando controladores, modelos, vistas y componentes centrales del sistema. Además, el directorio `public/` concentra los recursos accesibles desde el navegador, como estilos, imágenes, scripts y el punto de entrada principal de la aplicación.

## Modelo de datos

El proyecto utiliza una base de datos relacional en MySQL. El archivo `database/schema_sanitized.sql` contiene la estructura principal utilizada para representar empresas, predios, sectores, fertilizantes, configuraciones de distribución, registros de fertilización y reportes.

## Arquitectura

El sistema se organiza bajo una arquitectura MVC:

- **Models:** administran el acceso a datos.
- **Views:** presentan las interfaces del usuario.
- **Controllers:** reciben solicitudes y coordinan el flujo del sistema.
- **Services:** concentran la lógica de negocio, especialmente los cálculos de fertilización y distribución nutricional.

## Alcance académico

Esta versión tiene fines académicos y demostrativos. El proyecto fue adaptado como una propuesta coherente para la gestión de fertilización agrícola bajo el nombre **Abono Track**.

## Estado del proyecto

Proyecto en desarrollo para entrega académica final.

## Licencia

Uso académico.




