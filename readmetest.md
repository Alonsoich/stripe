# Documentación del Sistema de Reservas de Pistas Deportivas

## Descripción General

Este proyecto es una aplicación web desarrollada en **Laravel 9** que implementa un sistema de gestión de reservas para pistas deportivas con integración de pagos mediante **Stripe**. La aplicación permite a los usuarios reservar slots de tiempo específicos en diferentes pistas deportivas y procesar los pagos de forma segura.

## Características Principales

### 🏸 Sistema de Reservas por Slots de Tiempo
- **Gestión de pistas deportivas**: Administración de múltiples pistas con configuración individual
- **Slots de tiempo configurables**: Define horarios disponibles con precios específicos para cada pista
- **Sistema de disponibilidad inteligente**: Evita reservas duplicadas y gestiona la disponibilidad en tiempo real
- **Restricciones de días**: Capacidad para bloquear reservas en fines de semana u otros días específicos

### 💳 Integración de Pagos con Stripe
- **Checkout seguro**: Procesamiento de pagos mediante Stripe Checkout
- **Gestión de sesiones**: Control completo del flujo de pago desde inicio hasta confirmación
- **Páginas de resultado**: Manejo de estados de éxito y cancelación de pagos
- **Soporte para múltiples monedas**: Configuración flexible de monedas (EUR, USD, etc.)

## Estructura del Proyecto

### Tecnologías Utilizadas
- **Backend**: Laravel 9.x + PHP 8.0+
- **Frontend**: Vite.js para compilación de assets
- **Base de datos**: Compatible con MySQL/PostgreSQL
- **Pagos**: Stripe PHP SDK v16.6
- **Autenticación**: Laravel Sanctum

### Arquitectura de la Base de Datos

El sistema se basa en dos tablas principales para gestionar las reservas:

#### `time_slots` - Plantillas de Horarios
Define los horarios potencialmente reservables para cada pista:
- Horarios de inicio y fin
- Precios base por slot
- Días de la semana aplicables
- Estado de disponibilidad general

#### `reservations` - Reservas Específicas  
Registra las reservas confirmadas o en proceso:
- Fecha específica de la reserva
- Hora de inicio del slot reservado
- Estado de la reserva (`confirmed`, `awaiting_payment`, `expired`)
- Información del cliente
- Detalles del pago

### Lógica de Disponibilidad

El sistema implementa una lógica inteligente para mostrar disponibilidad:

1. **Consulta de slots potenciales**: Obtiene todos los horarios configurados para una pista
2. **Verificación de ocupación**: Comprueba qué slots ya están reservados para el día específico
3. **Filtrado de disponibilidad**: Muestra solo los slots libres al usuario
4. **Restricciones de días**: Aplica reglas para bloquear ciertos días de la semana

## Componentes Principales

### Controladores

#### `StripeController`
Gestiona todo el flujo de pagos:
- `showProduct()`: Muestra la página del producto/reserva
- `checkout()`: Inicia la sesión de pago con Stripe
- `success()`: Procesa la confirmación de pago exitoso
- `cancel()`: Maneja la cancelación del pago
- `handleWebhook()`: Recibe notificaciones asíncronas de Stripe (comentado pero preparado)

### Rutas Principales

#### Rutas Web (`routes/web.php`)
- `/`: Página principal del producto
- `/checkout`: Procesamiento del pago
- `/success`: Confirmación de pago exitoso
- `/cancel`: Página de cancelación
- `/stripe/webhook`: Endpoint para webhooks de Stripe (preparado)

#### Rutas API (`routes/api.php`)
- Endpoint de autenticación con Sanctum
- Preparado para expansión con APIs de reservas

## Características Técnicas

### Gestión de Estados de Reserva
- **`confirmed`**: Reserva pagada y confirmada
- **`awaiting_payment`**: Reserva temporal durante el proceso de pago
- **`expired`**: Reservas que expiraron sin completar el pago

### Prevención de Conflictos
- Bloqueo temporal de slots durante el proceso de pago
- Limpieza automática de reservas expiradas
- Validación de disponibilidad en tiempo real

### Configuración de Stripe
El sistema requiere configuración de las claves de Stripe en el archivo de entorno:
```env
STRIPE_PUBLIC_KEY=pk_...
STRIPE_SECRET_KEY=sk_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Funcionalidades Futuras Contempladas

### Flexibilidad de Horarios
- Configuración de días específicos por pista
- Precios variables según día de la semana
- Promociones y descuentos especiales

### Webhooks de Stripe
El código incluye la estructura para implementar webhooks que permitan:
- Confirmación segura de pagos
- Manejo de fallos de pago
- Notificaciones automáticas
- Reconciliación de transacciones

## Instalación y Configuración

### Requisitos
- PHP 8.0 o superior
- Composer
- Node.js y npm
- Base de datos (MySQL/PostgreSQL)
- Cuenta de Stripe

### Dependencias Principales
- Laravel Framework 9.19+
- Stripe PHP SDK 16.6+
- Laravel Sanctum para autenticación API
- Vite para compilación de assets frontend

## Casos de Uso

### Para Administradores de Pistas
1. Configurar horarios disponibles y precios
2. Definir días operativos
3. Gestionar reservas y pagos
4. Monitorear la ocupación

### Para Clientes
1. Visualizar disponibilidad en calendario
2. Seleccionar slot de tiempo deseado
3. Procesar pago de forma segura
4. Recibir confirmación de reserva

## Documentación Técnica Adicional

El proyecto incluye documentación detallada sobre la interacción entre las tablas `time_slots` y `reservations` en el archivo `interaccion_time_slot_reservas.md`, que explica:
- Algoritmos de consulta de disponibilidad
- Estrategias para manejar restricciones de días
- Opciones de implementación flexibles
- Ejemplos de código PHP/Laravel

---

*Esta aplicación representa una solución completa para la gestión de reservas deportivas con un enfoque en la seguridad de pagos y la experiencia del usuario.*