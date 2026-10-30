# 📘 Documentación Técnica del Backend - E-commerce

Esta documentación profundiza en la implementación técnica, los patrones de diseño y la lógica de negocio del servidor.

## 🏗️ Arquitectura del Sistema

El sistema sigue el patrón **MVC (Model-View-Controller)**, donde la "Vista" es manejada por **Inertia.js**, permitiendo que el controlador devuelva respuestas JSON que Vue.js renderiza como páginas completas.

### 📂 Estructura de Controladores

| Namespace | Responsabilidad | Controladores Clave |
| :--- | :--- | :--- |
| `App\Http\Controllers\Admin` | Gestión administrativa y Operaciones Internas | `ProductController`, `CategoryController`, `OrderController`, `ShippingZoneController` |
| `App\Http\Controllers\Storefront` | Experiencia de compra del cliente | `ProductController`, `CartController`, `CheckoutController`, `OrderTrackingController` |
| `App\Http\Controllers\Account` | Gestión de datos del cliente | `OrderController` |
| `App\Http\Controllers\Settings` | Configuración de cuenta y seguridad | `ProfileController`, `SecurityController` |

---

## 🛠️ Implementaciones Detalladas

### 1. Gestión de Productos y Categorías
- **Sugerencia de SEO:** Se utiliza un sistema de `slugs` para las URLs de los productos (`producto/{product:slug}`), mejorando la indexación en buscadores.
- **Relaciones:** 
    - Un producto pertenece a una categoría.
    - Un producto tiene múltiples imágenes (gestionadas en `Admin\ProductController`).

### 2. Motor de Carrito y Checkout
- **Persistencia:** El carrito se gestiona mediante el `CartController`, permitiendo operaciones atómicas de actualización y eliminación de ítems.
- **Proceso de Pedido:** 
    1. Validación de stock y precios en el `CheckoutController`.
    2. Creación del registro de Pedido (`Order`).
    3. Asignación de un código de seguimiento único.
    4. Descuento de inventario.

### 3. Logística de Envíos
- **Zonas de Envío:** Se implementa un sistema de `ShippingZone` que permite definir costos basados en la ubicación geográfica del cliente, gestionado desde el panel administrativo.

---

## 🔐 Seguridad y Autenticación

### Flujo de Identidad
El sistema delega la autenticación a **Laravel Fortify**, lo que garantiza que la lógica de seguridad esté aislada de la capa de presentación.

- **WebAuthn (Passkeys):** Implementado para permitir el acceso biométrico (FaceID, TouchID) o mediante llaves físicas, reduciendo la dependencia de contraseñas.
- **Seguridad de Perfil:** 
    - `SecurityController` maneja el ciclo de vida de las contraseñas.
    - `ProfileController` gestiona la integridad de los datos del usuario.

---

## ⚡ Optimización y Herramientas de Desarrollo

### Tipado Dinámico con Wayfinder
Para evitar errores de "404 Not Found" o errores de escritura en las URLs del frontend, se utiliza **Laravel Wayfinder**. 
- **Funcionamiento:** Escanea las rutas de Laravel y genera funciones TypeScript.
- **Ejemplo:** En lugar de usar `axios.post('/carrito')`, el frontend utiliza una función tipada que garantiza que la ruta existe y recibe los parámetros correctos.

### Estrategia de Pruebas
Se utiliza **Pest PHP** para asegurar la calidad del código:
- **Feature Tests:** Prueban flujos completos (ej. "Un usuario puede agregar un producto al carrito y finalizar la compra").
- **Unit Tests:** Prueban lógica aislada (ej. "Cálculo de impuestos según zona de envío").

---

## 📊 Esquema de Datos (Conceptuall)

- **Users:** Datos de identidad y credenciales.
- **Products:** Nombre, descripción, precio, stock y slug.
- **Categories:** Nombre y jerarquía.
- **Orders:** Referencia al usuario, total, estado y código de seguimiento.
- **OrderItems:** Productos específicos dentro de un pedido y su precio en el momento de la compra.
- **ShippingZones:** Nombre de la zona y tarifa asociada.
