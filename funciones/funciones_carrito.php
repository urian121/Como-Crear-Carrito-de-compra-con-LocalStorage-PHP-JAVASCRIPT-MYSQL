<?php
session_start();
include('../config/config.php');
if (isset($_POST["aumentarCantida"])) {
    $idProd               = $_POST['idProd'];
    $precio               = $_POST['precio'];
    $tokenCliente         = $_POST['tokenCliente'];
    $cantidaProducto      = $_POST['aumentarCantida'];

    $UpdateCant = "UPDATE pedidostemporales 
              SET cantidad ='$cantidaProducto'
              WHERE tokenCliente='$tokenCliente'
              AND id='$idProd'";
    $result = mysqli_query($con, $UpdateCant);


    /**
     * Actualizando el total a pagar
     */
    totalAccionAumentarDisminuir($con, $tokenCliente);
}



/**
 * Agregar a carrito de compra el producto
 */
if (isset($_POST["accion"]) && $_POST["accion"] == "addCar") {
    $_SESSION['tokenStoragel']  = $_POST['tokenCliente'];
    $idProduct                  = $_POST['idProduct'];
    $precio                     = $_POST['precio'];
    $tokenCliente               = $_POST['tokenCliente'];

    //Verifico si ya existe el producto almacenado en la tabla temporal de acuerdo al Token Unico del Cliente
    $ConsultarProduct = ("SELECT * FROM pedidostemporales WHERE tokenCliente='" . $tokenCliente . "' AND producto_id='" . $idProduct . "' ");
    $jqueryProduct    = mysqli_query($con, $ConsultarProduct);
    //Caso 1; si ya existe dicho producto agregado con respecto al token que tiene asignado el Cliente.
    if (mysqli_num_rows($jqueryProduct) > 0) {
        $DataProducto     = mysqli_fetch_array($jqueryProduct);
        $newCantidad   = ($DataProducto['cantidad'] + 1);

        $UdateCantidad = ("UPDATE pedidostemporales SET cantidad='" . $newCantidad . "' WHERE producto_id='" . $idProduct . "' AND tokenCliente='" . $tokenCliente . "' ");
        $resultUpdat = mysqli_query($con, $UdateCantidad);
    } else {
        //Caso 2; No existe producto agregado en la tabla de pedidos
        $InsertProduct = ("INSERT INTO pedidostemporales (producto_id, cantidad, tokenCliente) VALUES ('$idProduct','1','$tokenCliente')");
        $result = mysqli_query($con, $InsertProduct);
    }

    //Total carrito en el icono de compra
    $SqlTotalProduct       = ("SELECT SUM(cantidad) AS totalProd FROM pedidostemporales WHERE tokenCliente='" . $_SESSION['tokenStoragel'] . "' GROUP BY tokenCliente");
    $jqueryTotalProduct    = mysqli_query($con, $SqlTotalProduct);
    $DataTotalProducto     = mysqli_fetch_array($jqueryTotalProduct);
    echo $DataTotalProducto['totalProd'];
}

/**
 * Disminuir cantidad de mi carrito de compra
 */
if (isset($_POST["accion"]) && $_POST["accion"] == "disminuirCantida") {
    $_SESSION['tokenStoragel']  = $_POST['tokenCliente'];
    $idProd                     = $_POST['idProd'];
    $precio                     = $_POST['precio'];
    $tokenCliente               = $_POST['tokenCliente'];
    $disminuirCantida           = $_POST['disminuirCantida'];

    if ($disminuirCantida == 0) {
        $DeleteRegistro = ("DELETE FROM pedidostemporales WHERE tokenCliente='{$tokenCliente}' AND id='" . $idProd . "' ");
        mysqli_query($con, $DeleteRegistro);
    } else {
        $UpdateCant = ("UPDATE pedidostemporales 
        SET cantidad ='$disminuirCantida'
	WHERE tokenCliente='" . $tokenCliente . "' 
        AND id='" . $idProd . "' ");
        $result = mysqli_query($con, $UpdateCant);
    }

    //Total deuda
    totalAccionAumentarDisminuir($con, $tokenCliente);
}

function totalAccionAumentarDisminuir($con, $tokenCliente)
{
    $SqlDeudaTotal = "
        SELECT SUM(p.precio * pt.cantidad) AS totalPagar 
        FROM products AS p
        INNER JOIN pedidostemporales AS pt
        ON p.id = pt.producto_id
        WHERE pt.tokenCliente = '" .  $tokenCliente . "'";
    $jqueryDeuda = mysqli_query($con, $SqlDeudaTotal);
    $dataDeuda = mysqli_fetch_array($jqueryDeuda);
    echo $dataDeuda['totalPagar'];
}

/**
 * Funcion que esta al pendiente de verificar si hay pedidos activos por el usuario en cuestión
 */
if (isset($_POST["accion"]) && $_POST["accion"] == "verificarResumenPedido") {
    $tokenCliente               = $_POST['tokenCliente'];
    $ConsultarProduct = ("SELECT * FROM pedidostemporales WHERE tokenCliente='" . $tokenCliente . "' ");
    $jqueryProduct    = mysqli_query($con, $ConsultarProduct);
    if (mysqli_num_rows($jqueryProduct) > 0) {
        totalAccionAumentarDisminuir($con, $tokenCliente);
    } else {
        echo 0;
    }
}
