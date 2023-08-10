<?php
session_start();
include('config/config.php');

/**
 * Funcion para obtener todos los productos
 * de mi tienda
 */
function getProductData($con)
{
    $sqlProducts = ("
        SELECT 
            prod . * ,
            prod.id AS prodId,
            fot . * ,
            fot.id AS fotId 
        FROM 
            products AS prod,
            fotoproducts AS fot
        WHERE 
            prod.id = fot.products_id
    ");
    $queryProducts = mysqli_query($con, $sqlProducts);

    if (!$queryProducts) {
        return false;
    }
    // Si todo está bien, devuelves el resultado del query
    return $queryProducts;
}

/**
 * Detalles del producto seleccionado
 */
function detalles_producto_seleccionado($con, $idProd)
{
    $sqlDetalleProducto = ("
	SELECT 
		prod . * ,
		prod.id AS prodId,
		fot . * ,
		fot.id AS fotId 
	FROM 
		products AS prod,
		fotoproducts AS fot
	WHERE 
		prod.id = fot.products_id
		AND prod.id ='" . $idProd . "'
		LIMIT 1
	");
    $queryProductoSeleccionado = mysqli_query($con, $sqlDetalleProducto);
    if (!$queryProductoSeleccionado) {
        return false;
    }
    return $queryProductoSeleccionado;
}

/**
 * Funciona para validar si el carrito tiene algun producto
 */
function validando_carrito()
{
    if (isset($_SESSION['tokenStoragel']) == "") {
        return '
            <div class="row align-items-center">
                <div class="col-lg-12 text-center mt-5">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Ops.!</strong> Tu carrito está vacío.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="col-lg-12 text-center mt-5 mb-5">
                    <a href="./" class="red_button btn_raza">Volver a la Tienda</a>
                </div>
            </div>';
    }
}


/**
 * Retornando productos del carrito de compra
 */
function mi_carrito_de_compra($con)
{
    if (isset($_SESSION['tokenStoragel']) != "") {
        $sqlCarritoCompra = ("
            SELECT 
                prod . * ,
                prod.id AS prodId,
                fot . *,
                pedtemp .* ,
                pedtemp.id AS tempId
            FROM 
                products AS prod,
                fotoproducts AS fot,
                pedidostemporales AS pedtemp
            WHERE 
                prod.id = fot.products_id 
                AND prod.id=pedtemp.producto_id
                AND pedtemp.tokenCliente='" . $_SESSION['tokenStoragel'] . "'");
        $queryCarrito   = mysqli_query($con, $sqlCarritoCompra);
        if (!$queryCarrito) {
            return false;
        }
        return $queryCarrito;
    } else {
        return 0;
    }
}


/**
 * Mostrar la cantidad de productos seleccionados en el icono de carrito
 */
function iconoCarrito($con)
{
    if (isset($_SESSION['tokenStoragel']) && $_SESSION['tokenStoragel'] !== "") {
        $sqlTotalProduct = "SELECT SUM(cantidad) AS totalProd FROM pedidostemporales WHERE tokenCliente='" . $_SESSION['tokenStoragel'] . "' GROUP BY tokenCliente";
        $jqueryTotalProduct = mysqli_query($con, $sqlTotalProduct);

        if ($jqueryTotalProduct) {
            // La consulta se ejecutó correctamente
            $dataTotalProducto = mysqli_fetch_array($jqueryTotalProduct);
            return '<span id="checkout_items" class="checkout_items">' . $dataTotalProducto["totalProd"] . '</span>';
        } else {
            return '<span id="checkout_items" class="checkout_items">0</span>';
        }
    } else {
        return '<span id="checkout_items" class="checkout_items">0</span>';
    }
}


/**
 * Borrar producto del carrito
 */
function borrar_producto_carrito($con, $idRegistros)
{
    $DeleteRegistro = ("DELETE FROM pedidostemporales WHERE id= '{$idRegistros}' ");
    mysqli_query($con, $DeleteRegistro);
}



function totalAcumuladoDeuda($con)
{
    if (isset($_SESSION['tokenStoragel']) != "") {
        $SqlDeudaTotal = "
        SELECT SUM(p.precio * pt.cantidad) AS totalPagar 
        FROM products AS p
        INNER JOIN pedidostemporales AS pt
        ON p.id = pt.producto_id
        WHERE pt.tokenCliente = '" . $_SESSION["tokenStoragel"] . "'
        ";
        $jqueryDeuda = mysqli_query($con, $SqlDeudaTotal);
        $dataDeuda = mysqli_fetch_array($jqueryDeuda);
        return  number_format($dataDeuda['totalPagar'], 0, '', '.');
    }
}