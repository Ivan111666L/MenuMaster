# Instrucciones para Implementar el Sistema de Análisis de Ventas

Este documento contiene las instrucciones para implementar el sistema de análisis de ventas en MenuMaster.

## Pasos para la Implementación

1. **Ejecutar el script de instalación**

   ```
   php instalar_sistema_analisis.php
   ```

   Este script realizará las siguientes acciones:
   - Instalar la librería FPDF para generar PDFs
   - Crear las tablas de historial de pedidos
   - Modificar la tabla de pedidos para corregir el campo total
   - Crear triggers para actualizar automáticamente el total de los pedidos
   - Recalcular los totales para pedidos existentes

2. **Verificar la instalación**

   Después de ejecutar el script, verifica que:
   - Las tablas `historial_pedidos` y `historial_detalles_pedido` existan en la base de datos
   - La columna `total` en la tabla `pedidos` ya no sea VIRTUAL
   - Los triggers `actualizar_total_pedido` y `actualizar_total_pedido_delete` estén creados

3. **Probar el sistema**

   Para probar el sistema, puedes acceder a las siguientes rutas:
   - `/api/analisis/ventas` - Estadísticas generales de ventas
   - `/api/analisis/meseros` - Estadísticas de meseros
   - `/api/analisis/productos` - Estadísticas de productos
   - `/api/analisis/pdf` - Generar informe PDF

## Cambios Realizados

1. **Corrección del flujo de pedidos**
   - Se modificó la columna `total` en la tabla `pedidos` para que sea un campo real en lugar de virtual
   - Se crearon triggers para actualizar automáticamente el total cuando se agregan o eliminan detalles

2. **Almacenamiento de datos históricos**
   - Se crearon tablas para almacenar el historial de pedidos y sus detalles
   - Se modificó el método `eliminarPedido` para guardar los datos en el historial antes de eliminar

3. **Sistema de análisis de datos**
   - Se implementó un nuevo controlador `AnalisisController` para obtener estadísticas
   - Se agregó un método `getEstadisticasVentas` al modelo de pedidos para consultar datos históricos

4. **Generación de informes PDF**
   - Se integró la librería FPDF para generar informes
   - Se creó una clase personalizada `ReportePDF` para formatear los informes

## Funcionalidades Implementadas

1. **Análisis de Meseros**
   - Identificación de meseros con más ventas
   - Estadísticas de pedidos por mesero
   - Promedio de venta por pedido para cada mesero

2. **Análisis de Productos**
   - Identificación de productos más vendidos
   - Estadísticas de ventas por producto
   - Análisis de precios y cantidades

3. **Informes PDF**
   - Generación de informes completos con gráficos
   - Filtrado por rango de fechas
   - Exportación para análisis posterior

## Solución de Problemas

Si encuentras algún problema durante la implementación:

1. **Error en la instalación de FPDF**
   - Ejecuta manualmente: `composer require setasign/fpdf:^1.8`

2. **Error en la creación de tablas**
   - Verifica los permisos de la base de datos
   - Ejecuta manualmente el SQL en `historial_pedidos.sql`

3. **Error en la generación de PDF**
   - Verifica que la carpeta `public/assets/img/` contenga el archivo `logo.png`
   - Asegúrate de que PHP tenga permisos de escritura en el directorio temporal