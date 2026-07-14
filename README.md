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

- PHP 8.x
- MySQL 8
- HTML5 y CSS3
- Bootstrap 5
- JavaScript Vanilla
- Git y GitHub
- Arquitectura MVC

## Estructura real de archivos del proyecto

```text
app/
├── config/
│   └── config.php
├── controllers/
│   ├── CultivosController.php
│   ├── FertilizanteController.php
│   ├── FertilizacionController.php
│   ├── HomeController.php
│   ├── PrediosController.php
│   ├── ProgramaController.php
│   └── UsersController.php
├── core/
│   ├── App.php
│   ├── Controller.php
│   ├── Database.php
│   ├── FertilizacionService.php
│   ├── RiegoService.php
│   ├── SessionHelper.php
│   └── StorageService.php
├── models/
│   ├── ConfiguracionRiegoModel.php
│   ├── CultivoModel.php
│   ├── FertilizanteModel.php
│   ├── KpiModel.php
│   ├── PredioModel.php
│   ├── RiegoModel.php
│   ├── SectorModel.php
│   ├── UserModel.php
│   └── ProgramaFertilizacionModel.php
└── views/
    ├── fertilizacion/
    │   ├── configuracion.php
    │   └── registro.php
    ├── fertilizantes/
    │   ├── create.php
    │   ├── edit.php
    │   └── index.php
    ├── home/
    │   └── index.php
    ├── layout/
    │   ├── footer.php
    │   ├── header.php
    │   └── sidebar.php
    ├── predios/
    │   ├── form.php
    │   └── index.php
    ├── programa/
    │   ├── comparar.php
    │   ├── create.php
    │   ├── edit.php
    │   └── index.php
    └── users/
        ├── login.php
        ├── admin.php
        ├── create.php
        └── edit.php
```

## Sidebar real del sistema

El sidebar real del archivo `app/views/layout/sidebar.php` está organizado así:

- Dashboard
- Catálogos
  - Fertilizantes
  - Cultivos
  - Predios / Cuarteles
- Planificación
  - Programas
  - Config. Hidráulica
- Operación
  - Registro Aplicación
  - Historial Aplicaciones
- Reportes
  - Reporte NPK

## Menú Sidebar final

```text
[🏠] Dashboard
[📚] Catálogos
    ↳ Fertilizantes
    ↳ Cultivos
    ↳ Predios / Cuarteles
[📅] Planificación
    ↳ Programas
    ↳ Config. Hidráulica
[✅] Operación
    ↳ Registro Aplicación
    ↳ Historial Aplicaciones
[📊] Reportes
    ↳ Reporte NPK
```
