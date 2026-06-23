# Manual de Usuario - Compra Saludable & Vitamin OS

## 1. Introducción
Bienvenido al manual de usuario de la plataforma de comercio electrónico **Compra Saludable** y su sistema de administración **Vitamin OS**. Este documento está diseñado para guiarte en el uso diario de la tienda virtual y la gestión administrativa.

---

## 2. Tienda Virtual (Frontend)

La tienda virtual es la cara visible para tus clientes. Ha sido optimizada para ofrecer una experiencia de compra rápida y segura.

### 2.1. Navegación y Catálogo
- **Página Principal:** Muestra los productos destacados y categorías principales.
- **Buscador:** Los clientes pueden usar la barra superior para buscar productos específicos por nombre.
- **Detalle de Producto:** Al hacer clic en un producto, el cliente puede ver la descripción, precio y añadir la cantidad deseada al carrito.

### 2.2. Proceso de Compra (Checkout)
1. **Carrito de Compras:** El cliente revisa los productos añadidos y hace clic en "Proceder al Pago".
2. **Datos de Envío:** El cliente ingresa su información personal (Nombre, Email, Teléfono, Dirección). Puede comprar como invitado o crear una cuenta.
3. **Métodos de Pago:**
   - **Pago con Tarjeta (Izipay):** El cliente ingresa los datos de su tarjeta en una pasarela 100% segura. El sistema procesa el pago instantáneamente.
   - **Pago en Efectivo / Transferencia:** El cliente completa el pedido y recibe instrucciones para depositar. El pedido queda "Pendiente de Pago".

### 2.3. Mi Cuenta
Los clientes registrados pueden acceder a "Mi Cuenta" para:
- Ver su historial de pedidos.
- Consultar el estado en tiempo real de sus envíos.
- Actualizar su información personal.

---

## 3. Panel Administrativo (Vitamin OS)

El panel administrativo es tu centro de control (accesible desde `/admin`).

### 3.1. Dashboard Principal
Al ingresar, verás un resumen de las ventas, pedidos recientes y estadísticas clave del negocio.

### 3.2. Gestión de Pedidos (Orders)
Aquí administrarás todas las compras realizadas en la web.
- **Listado de Pedidos:** Ordenado de más reciente a más antiguo. Muestra el Nº de Pedido, Cliente, Estado y Total.
- **Estados del Pedido:**
  - *Pendiente:* El pedido acaba de ingresar.
  - *Procesando:* El pago fue confirmado (Izipay lo hace automáticamente).
  - *Enviado:* Has despachado el producto.
  - *Entregado:* El cliente recibió el producto.
- **Alertas Antifraude (Izipay):** Si un cliente paga con una tarjeta emitida fuera del país (ej. Estados Unidos, Europa), aparecerá un **triángulo rojo de advertencia** en la lista. Al entrar al pedido, verás un bloque rojo indicando "Posible Fraude", el país emisor, la marca (Visa/Mastercard) y el BIN (primeros 6 dígitos).

### 3.3. Catálogo de Productos y Cupones
- **Productos:** Puedes crear, editar, subir imágenes, establecer precios y controlar el stock.
- **Cupones:** Puedes crear códigos de descuento promocionales, establecer un porcentaje o monto fijo de rebaja, y limitar su número de usos.

### 3.4. Terminal POS (Punto de Venta)
Para ventas presenciales o por WhatsApp:
- Permite crear pedidos rápidamente desde el panel, seleccionar productos, aplicar descuentos y registrar el pago en efectivo o tarjeta sin pasar por la web pública.

### 3.5. Inventario (Kardex)
Lleva un registro detallado de todas las entradas y salidas de stock de tus productos, asegurando que nunca vendas productos agotados.
