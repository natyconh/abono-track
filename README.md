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
---

## Estructura de Archivos del Proyecto

```
_legacy/abono-track/
│
├── public/                         ← Document root del servidor web
│   ├── index.php                   ← Punto de entrada único (front controller)
│   └── .htaccess                   ← Rewrite rules para Apache shared hosting
│
├── app/
│   ├── config/
│   │   └── config.php              ← DB host, user, pass, name + constante BASE_URL
│   │
│   ├── core/
│   │   ├── App.php                 ← Router: parsea URL → Controller/Method/Params
│   │   ├── Controller.php          ← Clase base: carga modelos y vistas
│   │   ├── Database.php            ← Singleton PDO (sin empresa_id)
│   │   ├── SessionHelper.php       ← Login/logout helpers de sesión PHP
│   │   ├── FertilizacionService.php← Lógica de negocio NPK (cálculos, balances)
│   │   ├── RiegoService.php        ← Lógica de configuración de riego y caudales
│   │   └── StorageService.php      ← Upload simple de archivos (simplificado)
│   │
│   ├── controllers/
│   │   ├── HomeController.php      ← Dashboard principal con KPIs
│   │   ├── PublicoController.php   ← Login / Logout (vistas sin sesión)
│   │   ├── UsersController.php     ← CRUD usuarios + gestión de roles
│   │   ├── PrediosController.php   ← CRUD predios (campos agrícolas)
│   │   ├── SectoresController.php  ← CRUD sectores dentro de cada predio
│   │   ├── CultivosController.php  ← CRUD catálogo de cultivos (dato maestro FK)
│   │   ├── FertilizanteController.php ← CRUD fertilizantes con NPK y densidades
│   │   ├── FertilizacionController.php← Programas de temporada + ejecución
│   │   ├── RiegoController.php     ← Configuración de riego por sector
│   │   └── ReporteController.php   ← Reportes NPK: desviación, acumulado, PDF
│   │
│   ├── models/
│   │   ├── UserModel.php           ← Usuarios y autenticación
│   │   ├── PredioModel.php         ← Predios (sin empresa_id)
│   │   ├── SectorModel.php         ← Sectores vinculados a predio
│   │   ├── CultivoModel.php        ← Catálogo cultivos (vid, manzano, etc.)
│   │   ├── FertilizanteModel.php   ← Catálogo fertilizantes NPK
│   │   ├── RiegoModel.php          ← Configuración y registros de riego
│   │   ├── ConfiguracionRiegoModel.php ← Parámetros de riego por temporada
│   │   └── KpiModel.php            ← Consultas agregadas para dashboard
│   │   ← [ELIMINADOS: Cosecha*, Labor*, Instalacion*, Solicitud*,
│   │      Trabajador*, Clima*, EntidadLegal*, UsuarioWhatsappLink*]
│   │
│   └── views/
│       ├── layout/
│       │   ├── header.php          ← <head>, CSS, navbar superior
│       │   ├── sidebar.php         ← Menú lateral (solo módulos Abono Track)
│       │   └── footer.php          ← Cierre HTML + scripts JS
│       ├── publico/
│       │   └── login.php           ← Formulario de login
│       ├── home/
│       │   └── index.php           ← Dashboard: KPIs NPK, últimas aplicaciones
│       ├── predios/
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── sectores/
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── cultivos/
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── fertilizantes/
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── fertilizacion/
│       │   ├── index.php           ← Lista de programas de temporada
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── ejecucion.php       ← Registro de aplicación real en terreno
│       ├── riego/
│       │   ├── index.php
│       │   ├── create.php
│       │   └── edit.php
│       ├── reporte/
│       │   ├── index.php           ← Selector de rango y predio/sector
│       │   ├── npk_acumulado.php   ← Tabla NPK acumulado vs programado
│       │   └── desviacion.php      ← Gráfico de desviación por semana
│       └── users/
│           ├── index.php
│           ├── create.php
│           └── edit.php
│       ← [ELIMINADAS: avance_labores/, clima/, cosecha/, cosechas_destinos/,
│          entidades_legales/, instalaciones/, puntos/, tipos_puntos/,
│          solicitudes/, solicitudes_categorias/, reporte_cosecha/, trabajadores/]
│
├── database/
│   └── abono_track_demo.sql        ← Seed: 1 predio, 3 sectores, 5 fertilizantes,
│                                      2 cultivos, programas y ejecuciones de demo
│
└── composer.json                   ← Solo dependencias mínimas (sin composer.phar)
```
---

## Menú Sidebar Final (Abono Track)

```
[🏠] Dashboard
[🗺️] Configuración
    ↳ Predios
    ↳ Sectores
    ↳ Cultivos
[🌱] Catálogo de Fertilizantes
[📅] Programas de Temporada
[✅] Registro de Aplicación
[📊] Reportes NPK
```

---
