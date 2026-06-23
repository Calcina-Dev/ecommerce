# Casos de Uso y Escenarios de Prueba - Compra Saludable

Este documento detalla las pruebas funcionales que el cliente (dueño del negocio o administrador) debe realizar para validar que la plataforma opera correctamente antes de su lanzamiento o tras actualizaciones importantes.

---

## 1. Módulo: Compra y Checkout (Frontend)

### Caso de Prueba 1.1: Compra exitosa con Izipay (Tarjeta Local)
- **Objetivo:** Validar el flujo principal de compra con tarjeta peruana.
- **Pasos:**
  1. Ingresar a la tienda web.
  2. Agregar 1 o más productos al carrito.
  3. Ir al Checkout y llenar los datos de envío.
  4. Seleccionar método de pago "Izipay".
  5. Ingresar los datos de una tarjeta de prueba de Izipay (simulando una tarjeta emitida en Perú).
  6. Aprobar el pago.
- **Resultado Esperado:**
  - El cliente es redirigido a la página de "Pedido Confirmado".
  - En el panel administrativo (Vitamin OS), el pedido aparece con estado `Procesando` y estado de pago `Pagado`.
  - La campanita de notificaciones en el admin panel muestra "¡Pago Recibido!".
  - NO debe aparecer alerta de tarjeta extranjera.

### Caso de Prueba 1.2: Compra bloqueada o rechazada por Izipay
- **Objetivo:** Validar qué sucede cuando una tarjeta falla o no tiene fondos.
- **Pasos:**
  1. Repetir pasos 1 al 4 del caso anterior.
  2. Ingresar los datos de una tarjeta configurada para ser "Rechazada" (según el manual de pruebas de Izipay).
- **Resultado Esperado:**
  - El frontend muestra un mensaje de error rojo: "Transacción rechazada" o similar.
  - El cliente NO es redirigido a "Pedido Confirmado".
  - El pedido en el panel administrativo se cancela automáticamente o queda pendiente, y NO envía el producto.

### Caso de Prueba 1.3: Detección de Tarjeta Extranjera (Antifraude)
- **Objetivo:** Validar la seguridad y prevención de fraudes (ej. uso de BINs extranjeros para bypass 3DS2).
- **Pasos:**
  1. Realizar una compra usando una tarjeta de prueba de Izipay que simule ser de un país extranjero (ej. BIN de USA o Francia).
  2. Aprobar el pago.
- **Resultado Esperado:**
  - En el panel administrativo, la orden debe aparecer en la lista general con un **triángulo rojo** en la columna "Alerta".
  - Al editar la orden, la sección "Detalles de Pago Seguro (Izipay)" debe mostrar un recuadro rojo advirtiendo el país emisor y los primeros 6 dígitos de la tarjeta (BIN) para que el administrador decida si anula o despacha el pedido.

### Caso de Prueba 1.4: Compra con Efectivo / Transferencia
- **Objetivo:** Validar flujo manual.
- **Pasos:**
  1. Realizar una compra seleccionando "Efectivo / Transferencia".
- **Resultado Esperado:**
  - Cliente ve pantalla de confirmación con instrucciones de depósito.
  - En el panel administrativo, el pedido entra como `Pendiente de Pago`.
  - El administrador debe poder cambiar manualmente el estado a "Pagado" una vez que revise su cuenta bancaria.

---

## 2. Módulo: Administración (Backend)

### Caso de Prueba 2.1: Notificaciones en Tiempo Real (Campanita)
- **Objetivo:** Validar que los administradores son alertados rápidamente.
- **Pasos:**
  1. Mantener abierto el panel administrativo en una pestaña.
  2. Desde otra pestaña (o celular), realizar una compra como cliente.
- **Resultado Esperado:**
  - El panel administrativo debe mostrar un punto rojo en el ícono de la campana superior derecha en menos de 5 segundos.
  - Al abrir, debe haber una notificación de "¡Nueva Venta Web!" y (si se pagó con tarjeta) otra de "¡Pago Recibido!".

### Caso de Prueba 2.2: Creación y Uso de Cupones
- **Objetivo:** Validar el sistema de descuentos.
- **Pasos:**
  1. En el admin, ir a Coupons y crear un código `DESCUENTO10` (porcentaje 10%).
  2. En la tienda web, agregar un producto de S/ 100 y aplicar el código en el carrito.
- **Resultado Esperado:**
  - El carrito debe restar S/ 10.00 del total.
  - El pedido final debe procesarse por S/ 90.00.

### Caso de Prueba 2.3: Filtros de la Tabla de Órdenes
- **Objetivo:** Validar la usabilidad del administrador para encontrar fraudes rápidamente.
- **Pasos:**
  1. Ir a la lista de Orders en el admin.
  2. Usar el filtro de embudo superior derecho: "Alerta de Tarjeta" -> "Con tarjeta extranjera".
- **Resultado Esperado:**
  - La tabla debe reducirse y mostrar únicamente los pedidos sospechosos.
